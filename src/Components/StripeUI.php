<?php

namespace AscentCreative\Transact\Components;

use Illuminate\View\Component;

class StripeUI extends Component
{


    public $id = '';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id="stripe-ui")
    {
        //
        $this->id = $id;
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
