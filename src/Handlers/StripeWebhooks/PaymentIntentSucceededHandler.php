<?php

namespace AmplifyCode\Transact\Handlers\StripeWebhooks;

use AmplifyCode\Transact\Models\Transaction;
use Exception;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class PaymentIntentSucceededHandler
{

    public function __construct(public Event $event, public string $webhookPayload) {}

    // TODO: this currently only handles subscriptions - update to also handle single transactions
    public function handle(): void
    {
        $paymentIntent = $this->event->data->object;

        if (!is_a($paymentIntent, PaymentIntent::class)) {
            throw new Exception('Invalid event object type: '. get_class($paymentIntent), 1);
        }

        // Get fees for transaction
        $amount = 0;
        $fees = 0;
        $paidAt = null;

        $secret = config('transact.stripe_secret_key');
        $stripe = new StripeClient($secret);

        try {
            $charge = $stripe->charges->retrieve($paymentIntent->latest_charge);
            $balanceTransaction = $stripe->balanceTransactions->retrieve($charge->balance_transaction);
            $amount = $balanceTransaction->amount / 100;
            $fees = $balanceTransaction->fee / 100;
        } catch (ApiErrorException $e) {
            //
        }

        // Get invoice

        $invoicePayment = $stripe->invoicePayments->all(['payment' => ['payment_intent' => $paymentIntent->id, 'type' => 'payment_intent']])->first();

        if ($invoicePayment === null) {
            throw new Exception('Invoice payment not found', 1);
        }

        $invoice = $invoicePayment->invoice;

        if (is_string($invoice)) {
            $invoice = $stripe->invoices->retrieve($invoice);
        }

        $paidAt = $invoice->status_transitions->paid_at;

        // The invoice will have subscription details if it is part of a subscription

        if ($invoice->parent !== null && $invoice->parent->type == 'subscription_details') {
            $subscriptionDetails = $invoice->parent->subscription_details;
            if ($subscriptionDetails->metadata !== null && isset($subscriptionDetails->metadata->model) && isset($subscriptionDetails->metadata->model_id)) {
                $model = $subscriptionDetails->metadata->model;
                $modelID = $subscriptionDetails->metadata->model_id;
            }
        }

        if (!isset($model) || !isset($modelID)) {
            throw new Exception('Transactable model not found', 1);
        }

        $transaction = Transaction::query()->create([
            'transactable_type' => $model,
            'transactable_id' => $modelID,
        ]);

        $transaction->markPaid($amount, $fees, $paymentIntent->id, $this->webhookPayload, $paidAt);
    }
}
