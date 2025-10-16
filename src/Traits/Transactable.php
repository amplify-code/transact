<?php

namespace AmplifyCode\Transact\Traits;

use AmplifyCode\Transact\Models\Transaction;

trait Transactable {

    public function transaction() {
        return $this->morphMany(Transaction::class, 'transactable')->latest();
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transactable');
    }
   
}
