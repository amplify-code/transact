<?php

namespace AmplifyCode\Transact\Components;

use AmplifyCode\Transact\Contracts\iSubscribable;
use AmplifyCode\Transact\Contracts\iTransactable;
use AmplifyCode\Transact\Services\StripeIntentService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\View\Component;
use Stripe\PaymentIntent;

class StripeElements extends Component
{

    public PaymentIntent $intent;

    /**
     * @var array<string, mixed> $style
     * 
     * The style array as defined at https://stripe.com/docs/js/appendix/style
     * will be JSON encoded and passed to the javascript code when initialising the UI
     */
    public $style = [];

    /**
     * Create a new component instance.
     * 
     * @param Arrayable<string, mixed> $style
     *
     * @return void
     */
    public function __construct(public iSubscribable&iTransactable $transactable, public string $return = '', public string $id = "stripe-ui", public string $buttonText = "Pay Now", public ?string $cssSrc = null, ?Arrayable $style = null)
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

        $this->intent = (new StripeIntentService)->subscriptionIntent($transactable);
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
