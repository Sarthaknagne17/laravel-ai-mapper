<?php

namespace Araminco\AiMapper;

use Illuminate\Support\ServiceProvider;
// مطمئن شوید این خط use هم درست است
use Araminco\AiMapper\Console\GenerateMapCommand;

class AiMapperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateMapCommand::class,
            ]);
        }
    }
}