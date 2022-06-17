<?php

namespace AscentCreative\Transact;

use AscentCreative\Transact\Models\Transaction;
use AscentCreative\Transact\Contracts\iTransactable;

class Transact {

    static function start(iTransactable $model) {

        // does a transaction already exist?

       $t = Transaction::firstOrCreate([
            'transactable_type' => get_class($model),
            'transactable_id' => $model->id
       ]);
       $t->transactable()->associate($model);
       $t->save();

       // create a stripe payment intent

       $secret = config('transact.stripe_secret_key');

       $stripe = new \Stripe\StripeClient(
           $secret
        );


        // dd($model->getTransactionAmount());

        $intent = $stripe->paymentIntents->create([
            'amount' => $model->getTransactionAmount() * 100,
            'currency' => 'gbp',
             'metadata' => [
                 'transaction_id' => $t->uuid
             ]
        ]);

        $t->reference = $intent->id;
        $t->save();


       return $intent;

    }


}