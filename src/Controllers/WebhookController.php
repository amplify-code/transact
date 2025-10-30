<?php

namespace AmplifyCode\Transact\Controllers;

use AmplifyCode\Transact\Handlers\StripeWebhooks\PaymentIntentSucceededHandler;
use Illuminate\Http\Response;
use Stripe\Event;

class WebhookController
{
    public function __invoke(): Response
    {
        $endpoint_secret = config('transact.stripe_webhook_secret');

        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = Event::constructFrom(
                json_decode($payload, true)
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        }

        if ($endpoint_secret === null) {
            return new Response('Webhook secret not set', 500);
        }

        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Webhook error while validating signature.', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                (new PaymentIntentSucceededHandler($event, $payload))->handle();
                break;
            default:
                return new Response('Received unknown event type ' . $event->type, 200);
        }

        return new Response('Webhook handled');
    }
}
