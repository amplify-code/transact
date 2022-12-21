<?php

namespace AscentCreative\Transact\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Str;

use AscentCreative\Transact\Exceptions\WebhookException;

use Carbon\Carbon;

/**
 * A model to represent a confirmed order.
 */
class Transaction extends Model
{
    use HasFactory;
    
    /*
    * Uses a global scope to ensure we never include un-completed orders (baskets) when requesting orders
    */
    public $table = "transact_transactions"; 
   
    public $fillable = ['transactable_type', 'transactable_id', 'provider', 'reference', 'paid_at', 'is_recurring', 'uuid', 'amount', 'data', 'failed', 'failure_reason'];


    protected static function booted() {

        static::saving(function($model) {
            if(!$model->uuid) {
                $model->uuid = Str::uuid();
            }
        });

    }


    public function transactable() {
        return $this->morphTo();
    }


    public function getLast4Attribute() {
       
        return json_decode($this->data)->data->object->charges->data[0]->payment_method_details->card->last4;
    
    }

    /**
     * Whether the transaction has been completed / paid
     * @return [type]
     */
    public function getIsPaidAttribute() {
        return !is_null($this->paid_at);
    }

    /**
     * 
     * Marks a transaction as paid.
     * 
     * @param mixed $amount
     * @param mixed $fees
     * @param mixed $reference
     * @param mixed $data
     * 
     * @return [type]
     */
    public function markPaid($amount, $fees, $reference, $data) {
        if (!$this->is_paid) {
            
            $this->amount = $amount;
            $this->fees = $fees;
            $this->nett = $amount - $fees;
            $this->reference = $reference;
            $this->data = $data;
            $this->paid_at = new \Carbon\Carbon();

            $this->save();

            $this->transactable->onTransactionComplete();

            return true;

            // fire off a call to the transactable model so they can perform the required logic.
            // - Is this a method call? or an event?

           

        } else {
            return false;
        }
    }


   /**
    * Returns an unpaid transaction, based on the uuid provided.
    *  - If the transaction is found, but already paid, this checks if a recurring payment is expected
    *  - If so, a new Txn is created, with a new UUID, based on the original Txn, and the new one is returned.
    * 
    * @param mixed $uuid
    * 
    * @return [type]
    */
    static function getUnpaidForUUID($uuid, $reference) {

        $t = Transaction::where('uuid', $uuid)->first();

        if(!$t) {
            throw new WebhookException('Transaction ' . $uuid . ' not found');
        }

        if($t->is_paid) {

            // if it's paid, check if a recurring payment is expected 
            // before throwing a wobb... um, exception.
            if($t->is_recurring && $t->reference != $reference) {

                // ok - expected to recur, and the incoming data has a new reference
                // We can safely assume it's a new payment.
                // Create a new copy of the transaction, and update it:
                $t = $t->replicate([
                    'paid_at',
                    'reference',
                    'uuid',
                    'amount',
                    'fees',
                    'nett',
                    'data',
                ]);
                $t->save();

            } else {
                throw new WebhookException('Transaction ' . $meta->transaction_id . ' already marked paid');
            }


        }

        return $t;
    }

    public function getStatusAttribute() {

        if (!is_null($this->paid_at)) {
            return 'paid';
        }
        
        return 'unpaid'; 
    }
   

}
