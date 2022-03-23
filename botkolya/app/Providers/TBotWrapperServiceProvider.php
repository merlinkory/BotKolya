<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TBotWrapper;

class TBotWrapperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TBotWrapper::class, function ($app) {
            return new TBotWrapper(config('constants.bot_token'), config('constants.telegram_bot_username'));
        });
    }
}