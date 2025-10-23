<?php

namespace AmplifyCode\Transact\Models;

use AmplifyCode\Transact\Contracts\iSubscribable;
use AmplifyCode\Transact\Contracts\iTransactable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use AmplifyCode\Transact\Exceptions\WebhookException;

use Illuminate\Support\Carbon;

/**
 * AmplifyCode\Transact\Models\Transaction
 * A model to represent a confirmed order.
 * 
 * @property integer $id
 * @property string $transactable_type
 * @property integer $transactable_id
 * @property string $uuid
 * @property string $provider
 * @property boolean $is_recurring
 * @property float $amount
 * @property float $fees
 * @property float $nett
 * @property Carbon|null $paid_at
 * @property boolean $failed
 * @property string|null $failure_reason
 * @property string $reference
 * @property string $data
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Model|iTransactable|iSubscribable $transactable
 */
class Transaction extends Model
{
    use HasFactory;
    
    /*
    * Uses a global scope to ensure we never include un-completed orders (baskets) when requesting orders
    */
    public $table = "transact_transactions"; 
   
    public $fillable = ['transactable_type', 'transactable_id', 'provider', 'reference', 'paid_at', 'is_recurring', 'uuid', 'amount', 'data', 'failed', 'failure_reason'];

    public $casts = [
        'paid_at' => 'datetime'
    ];

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
     */
    public function getIsPaidAttribute(): bool {
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
     */
    public function markPaid($amount, $fees, $reference, $data, $paid_at=null) {
        if (!$this->is_paid) {
            
            $this->amount = $amount;
            $this->fees = $fees;
            $this->nett = $amount - $fees;
            $this->reference = $reference;
            $this->data = $data;
            
            $this->paid_at = new Carbon($paid_at);

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
                throw new WebhookException('Transaction ' . $uuid . ' already marked paid');
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
