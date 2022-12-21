<?php

namespace AscentCreative\Transact;

use AscentCreative\Transact\Models\Transaction;
use AscentCreative\Transact\Contracts\iTransactable;
use AscentCreative\Transact\Contracts\iSubscribable;

use Carbon\Carbon;

class Transact {

    static function start(iTransactable $model) {

        // does a transaction already exist?
        
       $t = Transaction::create([
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


    // subscribe - starts a subscription, returning the intent for the first invoice.
    static function subscribe(iSubscribable $model) {

        // find or create Transaction
        $t = Transaction::firstOrCreate([
            'transactable_type' => get_class($model),
            'transactable_id' => $model->id
       ], [
           'is_recurring' => 1, 
       ]);
       $t->transactable()->associate($model);
       $t->save();

        // set up with Stripe:
        // - register the customer
        $secret = config('transact.stripe_secret_key');

        $stripe = new \Stripe\StripeClient(
            $secret
        );

        $clock = $stripe->testHelpers->testClocks->create(
            ['frozen_time' => Carbon::now()->timestamp, 'name' => 'Test Donation Clock']
        );

        $customer = $stripe->customers->create(
            [
              'email' => $model->getCustomerEmail(),
              'test_clock' => $clock->id,
              'name' => $model->getCustomerName(),
            ]
        );

        // dump($customer);

        // - register the subscription
        $subscription = $stripe->subscriptions->create([
            'customer' => $customer->id,
            'items' => [[
                'price_data' => [
                    'product'=> 'prod_ME2EYepbapRMXI',
                    'currency'=>'GBP',
                    'recurring'=>[
                        'interval'=>$model->getInterval(),
                        'interval_count'=>$model->getIntervalCount()
                    ],
                    'unit_amount'=>$model->getSubscriptionAmount() * 100
                    
                ],
                'metadata' => [
                    'transaction_id' => $t->uuid
                ]
            ]],
            'metadata' => [
                'transaction_id' => $t->uuid
            ],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        // dump($subscription);


        $intent = $subscription->latest_invoice->payment_intent;

        $t->reference = $intent->id;
        $t->save();

        // return the intent

        return $intent;

    }


}