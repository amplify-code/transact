<div class="transact-stripe-elements">
    <div id="payment-element" style="margin-bottom: 20px;">
      <!-- Elements will create form elements here -->
    </div>
  
    <div id="error-message" style="color: darkred; font-weight: bold">
      <!-- Display error message to your customers here -->
    </div>
  
    <div id="stripe-actions" style="margin-top: 20px; text-align: right">
        <button id="stripe-elements-submit" class="btn btn-primary">{{ $buttonText }}</button>
    </div>
</div>

@push('styles')
    <style>
        #stripe-actions {
            display: none;
        }
    </style>
@endpush


@push('scripts')
    @scripttag('https://js.stripe.com/v3/')
    
<script>

    var stripe = Stripe('{{ config('transact.stripe_public_key') }}');

    const options = {
        clientSecret: '{{ $intent->client_secret }}',
    };

    const intentType = '{{ get_class($intent) }}';

    const elements = stripe.elements(options);

    const paymentElement = elements.create('payment', {});

    paymentElement.mount('#payment-element');

    paymentElement.on('ready', function(event) {
        $('#stripe-actions').show();
        console.log(intentType);
    });

    $(document).on('click', '#stripe-elements-submit', async (event) => {

        event.preventDefault();

        await elements.submit();

        if (intentType == 'StripeSetupIntent') {
            await stripe.confirmSetup({
                elements,
                confirmParams: {
                    return_url: '{{ $return }}',
                }
            });
        } else {
            await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: '{{ $return }}'
                }
            });
        }
    });

    </script>

@endpush


