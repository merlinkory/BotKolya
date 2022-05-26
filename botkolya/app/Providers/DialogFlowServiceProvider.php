<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\DialogFlow;

class DialogFlowServiceProvider extends ServiceProvider {
        
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
        $this->app->singleton(DialogFlow::class, function ($app) {
            return new DialogFlow();
        });
    }
}
