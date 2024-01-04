<?php

namespace AscentCreative\Transact\Providers;

use AscentCreative\Transact\Events\PaymentIntentSucceeded;


use AscentCreative\Transact\Listeners\PaymentIntentSucceededListener;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentIntentSucceeded::class => [
            PaymentIntentSucceededListener::class,
        ],
    ];

  
}
