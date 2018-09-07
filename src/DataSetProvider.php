<?php

namespace Jw\DataSet;

use Illuminate\Support\ServiceProvider;
use Jw\DataSet\Command\DataSetInputCommand;
use Jw\DataSet\Command\DataSetOutputCommand;

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
                DataSetInputCommand::class,
                DataSetOutputCommand::class
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
