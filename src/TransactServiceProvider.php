<?php

namespace AscentCreative\Transact;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Router;

class TransactServiceProvider extends ServiceProvider
{
  public function register()
  {
    //

    // Register the helpers php file which includes convenience functions:
    
    $this->mergeConfigFrom(
        __DIR__.'/../config/transact.php', 'transact'
    );

  }

  public function boot()
  {

    $this->loadViewsFrom(__DIR__.'/../resources/views', 'transact');

    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    $this->bootComponents();

    $this->bootPublishes();

    
  }

  

  // register the components
  public function bootComponents() {

        Blade::component('transact-stripe-ui', 'AscentCreative\Transact\Components\StripeUI');

  }




  

    public function bootPublishes() {

      $this->publishes([
        __DIR__.'/../assets' => public_path('vendor/ascentcreative/transact'),
    
      ], 'public');

      $this->publishes([
        __DIR__.'/config/usersettings.php' => config_path('transact.php'),
      ]);

    }



}