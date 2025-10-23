<?php

namespace AmplifyCode\Transact\Providers;

use AmplifyCode\Transact\Events\PaymentIntentSucceeded;


use AmplifyCode\Transact\Listeners\PaymentIntentSucceededListener;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentIntentSucceeded::class => [
            PaymentIntentSucceededListener::class,
        ],
    ];
}
