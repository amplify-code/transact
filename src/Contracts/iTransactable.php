<?php

namespace AmplifyCode\Transact\Contracts;

use Stripe\Customer;

interface iTransactable
{
    public function getCustomer(): string|Customer;

    public function getTransactionAmount(): float;

    public function getTransactionDescription(): ?string;

    public function onTransactionComplete(): void;
}
