<?php

namespace ClaudioDekker\LaravelAuthBlade;

use ClaudioDekker\LaravelAuthBlade\Console\GenerateCommand;
use Illuminate\Support\ServiceProvider;

class LaravelAuthBladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(GenerateCommand::class);
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //
    }
}
