<?php

namespace hapxu3\Esia;

use Hapxu3\Esia\Socialite\EsiaSocialiteProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class EsiaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EsiaSocialiteProvider::class, function (Container $container) {
            $config = $container->make('config');

            return new EsiaSocialiteProvider(
                $container->make('request'),
                $config->get('esia.clientId'),
                '',
                $config->get('esia.redirectUrl'),
                [
                    'signer' => $config->get('esia.signer'),
                    'certPath' => $config->get('esia.certPath'),
                    'privateKeyPath' => $config->get('esia.privateKeyPath'),
                    'privateKeyPassword' => $config->get('esia.privateKeyPassword'),
                    'tmpPath' => $config->get('esia.tmpPath'),
                ],
                $config->get('esia.scope'),
                $config->get('esia.test')
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/esia.php' => config_path('esia.php'),
        ], 'esia');

        Socialite::extend('esia', function (Container $container) {
            return $container->make(EsiaSocialiteProvider::class);
        });
    }
}
