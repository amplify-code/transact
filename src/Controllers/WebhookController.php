<?php

namespace AscentCreative\Transact\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

// use AscentCreative\Checkout\Models\Basket;
// use AscentCreative\Checkout\Models\Order;
use AscentCreative\Transact\Models\Transaction;

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
         

        if($event->type == 'payment_intent.succeeded') {

            
            // get the basket id from the posted data
            $meta = $event->data->object->metadata;

            $amount = 0;
            $fees = 0;
            $nett = 0;
            // callback to Stripe to get fees / amounts
            try {

                $secret = config('checkout.stripe_secret_key');
  
                $stripe = new \Stripe\StripeClient(
                    $secret
                     );
  
                $bt = $stripe->balanceTransactions->retrieve(
                  $event->data->object->charges->data[0]->balance_transaction,
                  []
                );
  
                $amount = $bt->amount / 100;
                $fees = $bt->fee / 100;
                $nett = $bt->net / 100;
  
  
              } catch (Exception $e) {
                throw new WebhookException('Error requesting Stripe data: ' . $e->getMessage());
                //   \Log::error($e->getMessage());
              }

            $reference = $event->data->object->id;
            
            // rather than getting the basket, get the Transaction record.
            $t = Transaction::getUnpaidForUUID($meta->transaction_id, $reference);
            
            $t->markPaid($amount, $fees, $reference, $webhookContent);


        }

    }
  

}
