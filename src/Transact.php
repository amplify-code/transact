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
            'amount' => floor($model->getTransactionAmount() * 100), // ensure no DP
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

        // create a customer with optional test clock
        $cust_payload = [
            'email' => $model->getCustomerEmail(),
            'name' => $model->getCustomerName(),
        ];

        if(config('transact.stripe_test_clocks')) {
            $clock = $stripe->testHelpers->testClocks->create(
                ['frozen_time' => Carbon::now()->timestamp, 'name' => 'Transact Test Clock']
            );
            $cust_payload['test_clock'] = $clock->id;
        }

        $customer = $stripe->customers->create(
            $cust_payload
        );


        // - register the subscription

        // The iSubscribable should return subscription items array
        // - could be a price identifier
        // - coud be a price_data array for flexible items like user-specified donations.
        $payload = [
            'customer' => $customer->id,
            'items' => [
                $model->getSubscriptionItems(),
            ],
            'metadata' => [
                'transaction_id' => $t->uuid
            ],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
            'proration_behavior' => 'none',
        ];

        // subscription schedules are problematic (must be set after payment)
        // so we'll just use a cancelation date to mimic the iterations
        // NB - this only works for simple subscriptions. 
        // - We're going to need to be cleverer for free trials etc.
        if($model->getIterations() > 0){
            $payload['cancel_at'] = Transact::calculateEndDate($model);
        }

        $subscription = $stripe->subscriptions->create($payload);


        $intent = $subscription->latest_invoice->payment_intent;

        $t->reference = $intent->id;
        $t->save();

        // return the intent
        return $intent;

    }

    static function calculateEndDate($model) {

        if($model->getIterations() > 0) {

            $date = Carbon::now();
            $date->add($model->getInterval(), $model->getIntervalCount() * $model->getIterations());
            return $date->timestamp;

        } else {
            return null;
        }

    }


}