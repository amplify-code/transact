<?php

namespace AmplifyCode\Transact\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

use AmplifyCode\Transact\Events\PaymentIntentSucceeded;
use Exception;

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
    public function handle(PaymentIntentSucceeded $event): void {  

        throw new Exception('Just to see what data hits the logs...');

    }


  
}
