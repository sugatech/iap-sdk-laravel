<?php

namespace IAP\SDK;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class IAPServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'iap');

        $this->app->singleton('iap.client', function ($app) {
            $options = $app['config']->get('iap');

            if (!isset($options['api_url'])) {
                throw new \InvalidArgumentException('Not found api_url config');
            }

            return new IAPClient($options['api_url']);
        });
    }

    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('iap.php')], 'iap');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('iap');
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/iap.php';
    }

}
