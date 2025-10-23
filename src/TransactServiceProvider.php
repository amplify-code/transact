<?php

namespace AmplifyCode\Transact;

use AmplifyCode\Transact\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TransactServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //

    // Register the helpers php file which includes convenience functions:

    $this->mergeConfigFrom(
      __DIR__ . '/../config/transact.php',
      'transact'
    );

    $this->app->register(EventServiceProvider::class);
  }

  public function boot(): void
  {

    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'transact');

    $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

    $this->bootComponents();

    $this->bootPublishes();
  }



  // register the components
  public function bootComponents(): void
  {

    Blade::component('transact-stripe-ui', 'AmplifyCode\Transact\Components\StripeUI');

    Blade::component('transact-stripe-elements', 'AmplifyCode\Transact\Components\StripeElements');
  }






  public function bootPublishes(): void
  {

    $this->publishes([
      __DIR__ . '/../assets' => public_path('vendor/ascent/transact'),

    ], 'public');

    $this->publishes([
      __DIR__ . '/../config/transact.php' => config_path('transact.php'),
    ]);
  }
}
