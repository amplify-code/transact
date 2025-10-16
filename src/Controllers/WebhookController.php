<?php

namespace AmplifyCode\Transact\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;

// use AmplifyCode\Checkout\Models\Basket;
// use AmplifyCode\Checkout\Models\Order;
use AmplifyCode\Transact\Models\Transaction;
use AmplifyCode\Transact\Models\Event;

/**
 * TODO: BIG ONE
 * 
 * Maybe this controller should just capture the data and store in the database
 *  - Fire off an event, rather than trying to process it in this code, just in case of error?
 * 
 * But, there might be problems where a user is waiting for a payment confirmation in real time. 
 *  - So do we save anything? If the event fails we still return an error...
 *  - But, we could (should!) trap the error and fire a WebHook failed message
 *  - If the event failed fully, we can at least re-run it in some way.
 * 
 * ----
 * Better option - some stuff happens immediately - simple confirmation etc
 *  - but then we fire off events to do what else is needed. 
 *  - Like all the fee stuff below... farm that off for an event running later. It's not needed immediately.
 * 
 */
class WebhookController extends Controller
{

    public function stripe() {

        echo 'STRIPE WEBHOOK CALLED';

        // read incoming webhook data 
        $webhookContent = "";
        
        $webhook = fopen('php://input' , 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }
        fclose($webhook);
         
        $event = json_decode($webhookContent);
        // process the event:

        Event::log($event);
        
         
        if($event->type == 'payment_intent.succeeded') {

            // get the basket id from the posted data
            $meta = $event->data->object->metadata;

            $amount = 0;
            $fees = 0;
            $nett = 0;
            $paid_at = null;
            
            // callback to Stripe to get fees / amounts
            try {

                $secret = config('transact.stripe_secret_key');
  
                $stripe = new \Stripe\StripeClient(
                    $secret
                     );

                // Change to get to fees - updated for latest API (but also bwd compat).
                $charge = $stripe->charges->retrieve(
                    $event->data->object->latest_charge
                );

                $bt = $stripe->balanceTransactions->retrieve(
                    $charge->balance_transaction
                );

  
                $amount = $bt->amount / 100;
                $fees = $bt->fee / 100;
                $nett = $bt->net / 100;


                if($event->data->object->invoice) {
                    $inv = $stripe->invoices->retrieve(
                        $event->data->object->invoice,
                        []
                    );
                   
                    // Log::debug($inv->lines->data[0]->metadata->transaction_id);
                    $meta = $inv->lines->data[0]->metadata;
                    $transaction_id = $inv->lines->data[0]->metadata->transaction_id;
                    $paid_at = $inv->status_transitions->paid_at;
                } else {
                    $meta = $meta;
                    $transaction_id = $meta->transaction_id;
                }
    
              } catch (Exception $e) {
                throw new WebhookException('Error requesting Stripe data: ' . $e->getMessage());
                //   \Log::error($e->getMessage());
              }

            $reference = $event->data->object->id;
            
            if($transaction_id = $meta->transaction_id) {

                // Legacy Code (transaction created ahead of time)
                $t = Transaction::getUnpaidForUUID($transaction_id, $reference);
               

            } else {

                // New Code
                // Create a transacrion record when the webhook comes in
                $t = Transaction::create([
                    'transactable_type' => $meta->model,
                    'transactable_id' => $meta->model_id,
                ]);
                
                $t->save();

            }

            $t->markPaid($amount, $fees, $reference, $webhookContent, $paid_at);
            

            
            // \AmplifyCode\Transact\Events\PaymentIntentSucceeded::dispatch($event);

        }

    }
  

}
