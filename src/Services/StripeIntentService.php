<?php

namespace AmplifyCode\Transact\Services;

use AmplifyCode\Transact\Contracts\iSubscribable;
use AmplifyCode\Transact\Contracts\iTransactable;
use Illuminate\Database\Eloquent\Model;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\StripeClient;

class StripeIntentService {

    public function subscriptionIntent(iSubscribable&iTransactable&Model $model): PaymentIntent|SetupIntent {
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
                'model_id' => $model->getKey(),
            ]
        ];

        if (($startDate = $model->getStartDate()) !== null) {
            $subscriptionDetails['trial_end'] = $startDate->timestamp;
            $subscriptionDetails['trial_settings'] = ['end_behavior' => ['missing_payment_method' => 'cancel']];
        }

        $subscription = $client->subscriptions->create($subscriptionDetails);

        $model->onSubscriptionCreated($subscription);

        if ($startDate === null) {
            $secret = $subscription->latest_invoice->confirmation_secret;
            $paymentIntentID = explode('_secret', $secret->client_secret)[0];
            $intent = $client->paymentIntents->retrieve($paymentIntentID);
            return $intent;
        } else {
            $secret = $subscription->pending_setup_intent;
            $intent = $client->setupIntents->retrieve($secret);
            return $intent;
        }
    }
}
