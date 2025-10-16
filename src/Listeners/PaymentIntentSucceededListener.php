<?php

namespace AmplifyCode\Transact\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use AmplifyCode\Transact\Events\PaymentIntentSucceeded;

class PaymentIntentSucceededListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

     /**
     * Handle the event.
     *
     */
    public function handle(PaymentIntentSucceeded $event) {  

        throw new Exception('Just to see what data hits the logs...');

    }


  
}
