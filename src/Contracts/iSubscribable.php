<?php

namespace AmplifyCode\Transact\Contracts;

use Stripe\SubscriptionSchedule;


/**
 * Methods for recurring subscriptions
 */
interface iSubscribable
{

    public function getCustomerName(): string;

    public function getCustomerEmail(): string;

    public function getSubscriptionAmount(): float;

    public function getSubscriptionDescription(): ?string;

    public function getStripeProductId(): string;

    public function getInterval(): string;
    public function getIntervalCount(): int;

    public function getIterations(): int;

    public function onSubscriptionCreated(SubscriptionSchedule $sched): void;

    // public function onSetupComplete();

    public function onRecurringPayment(): void;

    // public function getSubscriptionItems():array;

    /**
     * @return array<int, mixed>
     */
    public function getSubscriptionPhases(): array;
}
