<?php

namespace AscentCreative\Transact\Components;

use Illuminate\View\Component;

class StripeUI extends Component
{


    public $id = '';
    public $buttonText;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id="stripe-ui", $buttonText="Pay Now")
    {
        //
        $this->id = $id;
        $this->buttonText = $buttonText;
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('transact::stripe.ui');
    }
    
}
