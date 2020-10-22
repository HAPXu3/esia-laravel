<?php

namespace hapxu3\Esia\Socialite;

use Esia\Config;
use Esia\Exceptions\AbstractEsiaException;
use Esia\Exceptions\InvalidConfigurationException;
use Esia\OpenId;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;

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
     * @param array $guzzle
     * @throws InvalidConfigurationException
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, array $certParams, $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

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
     */
    protected function getUserByToken($token)
    {
        // TODO: Implement getUserByToken() method.
    }

    /**
     * @inheritDoc
     */
    protected function mapUserToObject(array $user)
    {
        // TODO: Implement mapUserToObject() method.
    }

    /**
     * @inheritDoc
     */
    public function user()
    {
        // TODO: Implement user() method.
    }

    /**
     * @inheritDoc
     * @throws AbstractEsiaException
     */
    public function getAccessTokenResponse($code)
    {
        return ['access_token' => $this->esia->getToken($code)];
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
}
