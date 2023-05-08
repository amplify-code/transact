<?php

namespace AscentCreative\Transact\Components;

use Illuminate\View\Component;

class StripeElements extends Component
{


    public $id = '';
    public $buttonText;

    public $cssSrc = null;

    // the style array as defined at https://stripe.com/docs/js/appendix/style
    // will be JSON encoded and passed to the javascript code when initialising the UI
    public $style = [];
    // = [
    //    'base' => [
    //         'backgroundColor' => "#ffffff",
    //         'padding' => '10px',
    //         'fontFamily' => 'Montserrat, sans-serif'
    //    ]
    // ];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id="stripe-ui", $buttonText="Pay Now", $cssSrc=null, $style=null)
    {
        //
        $this->id = $id;
        $this->buttonText = $buttonText;

        if($cssSrc) {
            $this->cssSrc = $cssSrc;
        } else {
            $this->cssSrc = config('transact.cssSrc');
        }
        
        $styleCollection = collect(config('transact.style'));


        if(!is_null($style)) {
           $styleCollection = $styleCollection->merge($style);
        }
        $this->style = $styleCollection->toArray();


        // $intent = 
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('transact::stripe.elements');
    }
    
}
