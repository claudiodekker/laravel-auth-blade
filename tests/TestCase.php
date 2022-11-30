<?php

namespace ClaudioDekker\LaravelAuthBlade\Tests;

use ClaudioDekker\LaravelAuthBlade\LaravelAuthBladeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelAuthBladeServiceProvider::class,
        ];
    }
}
