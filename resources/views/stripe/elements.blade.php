<div id="payment-element">
    <!--Stripe.js injects the Payment Element-->
</div>


@push('scripts')
    @script('https://js.stripe.com/v3/')
    <script>

        var stripe = Stripe('{{ config('transact.stripe_public_key') }}');

        var elements = stripe.elements({
            mode: 'payment',
            currency: 'gbp',
            amount: 1000,
        });

        var paymentElement = elements.create('payment');

        paymentElement.mount("#payment-element");

    </script>

@endpush