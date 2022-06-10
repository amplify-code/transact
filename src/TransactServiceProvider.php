<?php

namespace AscentCreative\Transact

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Router;

class TransactServiceProvider extends ServiceProvider
{
  public function register()
  {
    //

    // Register the helpers php file which includes convenience functions:
    require_once (__DIR__.'/helpers.php');
   
    $this->mergeConfigFrom(
        __DIR__.'/../config/transact.php', 'transact'
    );

  }

  public function boot()
  {

    $this->loadViewsFrom(__DIR__.'/../resources/views', 'transact');

    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    
  }

  

  // register the components
  public function bootComponents() {

  }




  

    public function bootPublishes() {

      $this->publishes([
        __DIR__.'/Assets' => public_path('vendor/ascentcreative/transact'),
    
      ], 'public');

      $this->publishes([
        __DIR__.'/config/usersettings.php' => config_path('transact.php'),
      ]);

    }



}