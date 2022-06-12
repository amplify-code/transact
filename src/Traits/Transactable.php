<?php

namespace AscentCreative\Transact\Traits;

use AscentCreative\Transact\Models\Transaction;

trait Transactable {

    public function transaction() {
        return $this->morphOne(Transaction::class, 'transactable');
    }
   
}