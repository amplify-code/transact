<?php

namespace AmplifyCode\Transact\Components;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class StripeUI extends Component
{
    /**
     * @var array<string, mixed> $style
     * 
     * The style array as defined at https://stripe.com/docs/js/appendix/style
     * will be JSON encoded and passed to the javascript code when initialising the UI
     */
    public array $style = [];
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
     * @param Arrayable<string, mixed> $style
     * 
     * @return void
     */
    public function __construct(public string $id = "stripe-ui", public string $buttonText = "Pay Now", public ?string $cssSrc = null, ?Arrayable $style = null)
    {

        if ($this->cssSrc === null) {
            $this->cssSrc = config('transact.cssSrc');
        }


        /** @var array<string, mixed> */
        $configStyle = config('transact.style');
        $styleCollection = collect($configStyle);

        if (!is_null($style)) {
            $styleCollection = $styleCollection->merge($style);
        }
        $this->style = $styleCollection->toArray();
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
