<?php

namespace hapxu3\Esia\Socialite;

use Esia\Config;
use Esia\Exceptions\AbstractEsiaException;
use Esia\Exceptions\InvalidConfigurationException;
use Esia\OpenId;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class EsiaSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    /** @var OpenId */
    protected $esia;

    /** @var bool */
    protected $isTest;

    /**
     * EsiaSocialiteProvider constructor.
     * @param Request $request
     * @param $clientId
     * @param $clientSecret
     * @param $redirectUrl
     * @param array $certParams
     * @param array $scope
     * @param array $guzzle
     * @param bool $isTest
     * @throws InvalidConfigurationException
     */
    public function __construct(
        Request $request,
        $clientId,
        $clientSecret,
        $redirectUrl,
        array $certParams,
        array $scope,
        bool $isTest = true,
        $guzzle = []
    ) {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);
        $this->isTest = $isTest;
        $this->scopes = $scope;

        $this->esia = new OpenId($this->makeConfig($certParams['privateKeyPath'], $certParams['certPath']));
        $this->esia->setSigner($this->makeSigner($certParams));
    }

    /**
     * @inheritDoc
     */
    protected function getAuthUrl($state)
    {
        return $this->isTest
            ? 'https://esia-portal1.test.gosuslugi.ru/'
            : 'https://esia.gosuslugi.ru/';
    }

    /**
     * @inheritDoc
     */
    protected function getTokenUrl()
    {
        return $this->isTest
            ? 'https://esia-portal1.test.gosuslugi.ru/aas/oauth2/te'
            : 'https://esia.gosuslugi.ru/aas/oauth2/te';
    }

    /**
     * @inheritDoc
     * @throws AbstractEsiaException
     */
    protected function getUserByToken($token)
    {
        return $this->esia->getPersonInfo() + ['oid' => $this->esia->getConfig()->getOid()];
    }

    /**
     * @inheritDoc
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['oid'],
            'name' => $user['lastName'] . ' ' . $user['firstName'] . ' ' . $user['middleName'],
        ]);
    }

    /**
     * @inheritDoc
     * @throws AbstractEsiaException
     */
    public function getAccessTokenResponse($code)
    {
        $token = $this->esia->getToken($code);
        $payload = json_decode($this->base64UrlSafeDecode(explode('.', $token)[1]), true);

        return [
            'access_token' => $token,
            'expires_in' => $payload['exp'],
            'refresh_token' => null,
        ];
    }

    /**
     * @param string $privateKeyPath
     * @param string $certPath
     * @return Config
     * @throws InvalidConfigurationException
     */
    protected function makeConfig(string $privateKeyPath, string $certPath)
    {
        return new Config([
            'clientId' => $this->clientId,
            'redirectUrl' => $this->redirectUrl,
            'privateKeyPath' => $privateKeyPath,
            'certPath' => $certPath,
            'portalUrl' => $this->getAuthUrl(null),
            'scope' => $this->scopes,
        ]);
    }

    protected function makeSigner(array $params)
    {
        $signer = $params['signer'];

        return new $signer(
            $params['certPath'],
            $params['privateKeyPath'],
            $params['privateKeyPassword'],
            $params['tmpPath']
        );
    }

    /**
     * Url safe for base64
     * @param string $string
     * @return string
     */
    private function base64UrlSafeDecode(string $string): string
    {
        $base64 = strtr($string, '-_', '+/');

        return base64_decode($base64);
    }
}
