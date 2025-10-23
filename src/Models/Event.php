<?php

namespace AmplifyCode\Transact\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Str;

use AmplifyCode\Transact\Exceptions\WebhookException;
use Illuminate\Support\Carbon;

/**
 * AmplifyCode\Transact\Models\Event
 * A model to represent a wehook event from Stripe
 * 
 * @property integer $id
 * @property string $event
 * @property string $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Event extends Model
{
    use HasFactory;
    
    /*
    * Uses a global scope to ensure we never include un-completed orders (baskets) when requesting orders
    */
    public $table = "transact_events"; 
   
    public $fillable = ['event', 'data'];


    public static function log($data) {

        $event = Event::create([
            'event' => $data->type,
            'data' => json_encode($data)
        ]);

    }
    

}
