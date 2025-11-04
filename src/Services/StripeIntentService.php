<?php

namespace AmplifyCode\Transact\Services;

use AmplifyCode\Transact\Contracts\iSubscribable;
use AmplifyCode\Transact\Contracts\iTransactable;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeIntentService {

    public function subscriptionIntent(iSubscribable&iTransactable $model): PaymentIntent {
        $client = new StripeClient(config('transact.stripe_secret_key'));

        $price = $model->getPrice();

        if (!is_string($price)) {
            $price = $price->id;
        }

        $subscriptionDetails = [
            'customer' => $model->getCustomer()->id,
            'items' => [
                ['price' => $price]
            ],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                    'payment_method_types' => ['card']
                ],
            'expand' => ['latest_invoice.confirmation_secret'],
            'metadata' => [
                'model' => get_class($model),
                'model_id' => $model->id,
            ]
        ];

        if (($startDate = $model->getStartDate()) !== null) {
            $subscriptionDetails['trial_end'] = $startDate->timestamp;
        }

        $subscription = $client->subscriptions->create($subscriptionDetails);

        $model->onSubscriptionCreated($subscription);
    
        $secret = $subscription->latest_invoice->confirmation_secret;

        $paymentIntentID = explode('_secret', $secret->client_secret)[0];

        $intent = $client->paymentIntents->retrieve($paymentIntentID);

        return $intent;
    }
}
