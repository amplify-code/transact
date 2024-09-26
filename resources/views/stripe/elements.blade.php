<div id="payment-element" style="width: 100%">
    <!--Stripe.js injects the Payment Element-->
</div>

@php

    // $stripe = new \Stripe\StripeClient("sk_test_51IUCyCHZw0ztnS0JmrK5PZrebobxjxbsQ5nplzJD0hWvzh0dpxELT3Bpgctp6A130qQcifkwiRBBMq6iQAxBhfVa00qKcd0Yes");
    // $intent = $stripe->paymentIntents->create(
    //   [
    //     'amount' => 1099,
    //     'currency' => 'gbp',
    //   ]
    // );
    // echo json_encode(array('client_secret' => $intent->client_secret));


@endphp


@push('scripts')
    @scripttag('https://js.stripe.com/v3/')
    <script>

        var stripe = Stripe('{{ config('transact.stripe_public_key') }}');

        var elements = stripe.elements({
            {{-- // clientSecret: '{{ $intent->client_secret }}',--}}
            mode: 'payment',
            currency: 'gbp',
            amount: 1000,
        });
        
        var paymentElement = elements.create('payment');
        paymentElement.mount("#payment-element");
        

    </script>

@endpush