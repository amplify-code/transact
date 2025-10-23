<?php

namespace AmplifyCode\Transact\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Carbon;

/**
 * AmplifyCode\Transact\Models\Event
 * A model to represent a wehook event from Stripe
 * 
 * @property integer $id
 * @property string $event
 * @property string $data
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class Event extends Model
{
    
    /*
    * Uses a global scope to ensure we never include un-completed orders (baskets) when requesting orders
    */
    public $table = "transact_events"; 
   
    public $fillable = ['event', 'data'];


    public static function log(mixed $data): void {

        $event = Event::create([
            'event' => $data->type,
            'data' => json_encode($data)
        ]);

    }
    

}
