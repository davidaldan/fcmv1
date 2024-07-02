<?php

namespace Daldan26\Fcmv1;

use Illuminate\Support\ServiceProvider;

class FcmProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/fcm_config.php' => config_path('fcm_config.php'),
        ], 'fcmv1');
    }
}
