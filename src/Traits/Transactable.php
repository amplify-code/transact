<?php

namespace AmplifyCode\Transact\Traits;

use AmplifyCode\Transact\Models\Transaction;

/**
 * AmplifyCode\Transact\Traits\Transactable
 * Trait for transactable classes that are attached to a
 * Transaction's transactable morph many relation
 * 
 * @phpstan-ignore trait.unused
 */
trait Transactable
{

    public function transaction()
    {
        return $this->morphMany(Transaction::class, 'transactable')->latest();
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }
}
