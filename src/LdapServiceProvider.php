<?php

namespace Finagin\LaravelLdap;

use App\LdapUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class LdapServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/ldap.php' => config_path('ldap.php'),
        ], 'config');

        Auth::provider('ldap', static function ($app, array $config) {
            return new LdapUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ldap.php', 'ldap'
        );
    }
}
