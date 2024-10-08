
{{-- <form id="payment-form"> --}}
<div>
    <div id="payment-element" style="margin-bottom: 20px;">
      <!-- Elements will create form elements here -->
    </div>
  
    <div id="error-message" style="color: darkred; font-weight: bold">
      <!-- Display error message to your customers here -->
    </div>
  
    <div id="stripe-actions" style="margin: 20px 0; text-align: right">
        <button id="submit" class="btn btn-primary">{{ $buttonText }}</button>
    </div>
</div>
  
{{-- </form> --}}


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

            'mode': 'payment',
            'currency': 'gbp',
            'amount': 3600
            // for use with existing intent:
            // clientSecret: '{{-- $intent->client_secret --}}',

            // Fully customizable with appearance API.
            //  - Set via application.ini / Config, 
            //  - Rendered as global js variables in StripeController::appearancejsAction
            // fonts: stripe_fonts,
            // appearance: {/*...*/
                // variables: stripe_variables
            // },
        };

    // Set up Stripe.js and Elements to use in checkout form, passing the client secret obtained in step 5
    const elements = stripe.elements(options);

    // Create and mount the Payment Element
    const paymentElement = elements.create('payment', {
        // {{--
        // @if($intent->payment_method_types)
        //     paymentMethodOrder: {{ json_encode($intent->payment_method_types) }},
        // @endif
        // --}}

    });
    paymentElement.mount('#payment-element');

    paymentElement.on('ready', function(event) {
    // Handle ready event
    $('#stripe-actions').show();
    });

    const form = document.getElementById('payment-form');

    form.addEventListener('submitx', async (event) => {

        alert('submit');

        console.log(elements._commonOptions);

        event.preventDefault();

        // Trigger form validation and wallet collection
        const {error: submitError} = await elements.submit();
        if (submitError) {
            console.log(submitError);
            // handleError(submitError);
            return;
        }


        const {error, confirmationToken} = await stripe.createConfirmationToken({
                    elements,
                    params: {
                    shipping: {
                        name: 'Jenny Rosen',
                        address: {
                        line1: '1234 Main Street',
                        city: 'San Francisco',
                        state: 'CA',
                        country: 'US',
                        postal_code: '94111',
                        },
                    },
                    return_url: 'https://example.com/order/123/complete'
                    }
                });

            console.log('confirmationToken', confirmationToken);

        // const {error} = await stripe.confirmPayment({
        //     //`Elements` instance that was used to create the Payment Element
        //     elements,
        //     confirmParams: {
        //         return_url: '{{-- route('subscribe.return') --}}'
        //     }
        // });

        // if (error) {
        //     // This point will only be reached if there is an immediate error when
        //     // confirming the payment. Show error to your customer (for example, payment
        //     // details incomplete)
        //     const messageContainer = document.querySelector('#error-message');
        //     messageContainer.textContent = error.message;
        // } else {
        //     // Your customer will be redirected to your `return_url`. For some payment
        //     // methods like iDEAL, your customer will be redirected to an intermediate
        //     // site first to authorize the payment, then redirected to the `return_url`.
        // }
    });

    </script>

@endpush


