<?php

namespace Jw\DataSet;

use Illuminate\Support\ServiceProvider;
use Jw\DataSet\Command\DataSetInputCommand;

class DataSetProvider extends ServiceProvider
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
                DataSetInputCommand::class
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
