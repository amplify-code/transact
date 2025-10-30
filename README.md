# Transact

A Laravel package for handling payment transactions and subscriptions with Stripe.

## Overview

Transact is a Laravel package that simplifies the integration of payment processing into your Laravel applications. It provides a clean, consistent interface for handling both one-time payments and recurring subscriptions through Stripe, with the architecture designed to support additional payment providers in the future.

Transact was originally developed by Kieran Metcalfe of Ascent Creative until his death in July 2025. This fork is maintained by Colin Cameron of Amplify Code Ltd in order to further develop and maintain websites that rely on this package.

## Features

- **One-time Payments**: Process single payments with Stripe
- **Subscriptions**: Handle recurring payments and subscription management
- **Webhook Support**: Process Stripe webhooks for payment status updates
- **Blade Components**: Ready-to-use UI components for payment forms
- **Polymorphic Relationships**: Associate transactions with any model in your application
- **Transaction Tracking**: Built-in database storage for transaction history

## Installation

### 1. Require the package via Composer

```bash
composer require amplify-code/transact
```

### 2. Publish the configuration and assets

```bash
php artisan vendor:publish --provider="AmplifyCode\Transact\TransactServiceProvider"
```

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Configure your environment variables

Add the following to your `.env` file:

```
STRIPE_PUBLIC=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret
STRIPE_TEST_CLOCKS=false
```

## Updating assets

When updating this library, or installing your application, you will need to publish the latest js and css asset files to your application's public directory.

### 1. Update the published asset files

```bash
php artisan vendor:publish --tag=public --force
```

## Usage

### One-time Payments

1. Implement the `iTransactable` interface on your model:

```php
use AmplifyCode\Transact\Contracts\iTransactable;

class Order extends Model implements iTransactable
{
    public function getTransactionAmount(): float
    {
        return $this->total;
    }

    public function getTransactionDescription(): ?string
    {
        return "Payment for Order #{$this->id}";
    }

    public function onTransactionComplete()
    {
        // Handle successful payment logic
        $this->status = 'paid';
        $this->save();
    }
}
```

2. Process a payment:

```php
use AmplifyCode\Transact\Transact;

$order = Order::find(1);
$paymentIntent = Transact::pay($order, $paymentMethodId);
```

### Subscriptions

1. Implement the `iSubscribable` interface on your model:

```php
use AmplifyCode\Transact\Contracts\iSubscribable;
use Stripe\SubscriptionSchedule;

class Subscription extends Model implements iSubscribable
{
    public function getCustomerName(): string
    {
        return $this->user->name;
    }

    public function getCustomerEmail(): string
    {
        return $this->user->email;
    }

    public function getSubscriptionAmount(): float
    {
        return $this->plan_amount;
    }

    public function getSubscriptionDescription(): ?string
    {
        return "Subscription to {$this->plan_name}";
    }

    public function getStripeProductId(): string
    {
        return $this->stripe_product_id;
    }

    public function getInterval(): string
    {
        return 'month'; // or 'day', 'week', 'year'
    }

    public function getIntervalCount(): int
    {
        return 1; // e.g., 1 for monthly, 3 for quarterly
    }

    public function getIterations(): int
    {
        return 0; // 0 for unlimited, or specific number of billing cycles
    }

    public function getSubscriptionPhases(): array
    {
        return [
            [
                'items' => [
                    [
                        'price_data' => [
                            'currency' => 'gbp',
                            'product' => $this->getStripeProductId(),
                            'recurring' => [
                                'interval' => $this->getInterval(),
                                'interval_count' => $this->getIntervalCount(),
                            ],
                            'unit_amount' => $this->getSubscriptionAmount() * 100,
                        ],
                    ],
                ],
                'metadata' => [
                    'subscription_id' => $this->id,
                ],
            ],
        ];
    }

    public function onSubscriptionCreated(SubscriptionSchedule $sched)
    {
        // Handle subscription creation logic
        $this->stripe_subscription_id = $sched->id;
        $this->save();
    }

    public function onRecurringPayment()
    {
        // Handle recurring payment logic
    }
}
```

2. Set up a subscription:

```php
use AmplifyCode\Transact\Transact;

$subscription = Subscription::find(1);
$setupIntent = Transact::setup($subscription, $paymentMethodId);
```

3. Start the subscription (typically called from a frontend callback):

```php
$subscriptionSchedule = Transact::subscribe($setupIntentId);
```

### Using the Blade Components

#### Stripe Elements

```blade
<x-transact-stripe-elements
    :amount="$amount"
    :description="$description"
    :transaction-id="$transactionId"
    :return-url="$returnUrl"
/>
```

#### Stripe UI

```blade
<x-transact-stripe-ui
    :amount="$amount"
    :description="$description"
    :transaction-id="$transactionId"
    :return-url="$returnUrl"
/>
```

## Webhook Configuration

Configure your Stripe webhook to point to:

```
https://your-domain.com/transact/stripe
```

Ensure the following events are enabled:
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `invoice.payment_succeeded` (for subscriptions)
- `invoice.payment_failed` (for subscriptions)

## License

This package is developed by [Amplify Code](https://amplifycode.com) and [Ascent Creative](https://ascent-creative.co.uk).
