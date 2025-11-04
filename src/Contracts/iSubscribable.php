<?php

namespace AmplifyCode\Transact\Contracts;

use Illuminate\Support\Carbon;
use Stripe\Price;
use Stripe\Subscription;
use Stripe\SubscriptionSchedule;

/**
 * Methods for recurring subscriptions
 */
interface iSubscribable
{
    public function getPrice(): string|Price;

    public function getStartDate(): ?Carbon;

    public function onSubscriptionCreated(Subscription $subscription): void;

    public function onSubscriptionScheduleCreated(SubscriptionSchedule $schedule): void;
}
