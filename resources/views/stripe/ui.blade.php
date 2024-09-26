@push('scripts')
    @scripttag('https://js.stripe.com/v3/')
    @scripttag('/vendor/ascent/transact/jquery/ascent.transact.stripeui.js')

    <SCRIPT>
        $(document).ready(function() {
            $('#{{ $id }}').stripeui({
                key: '{{ config('transact.stripe_public_key') }}',
                cssSrc: '{{ $cssSrc }}',
                style: {!! json_encode($style) !!}
            });
        });
    </SCRIPT>

@endpush


@push('styles')

    @style('/vendor/ascent/transact/jquery/ascent.transact.stripeui.css')

<style>

    /** spinner **/
    
    .spinner {
        
        height: 100px;
        width: 100px;
        margin: 50px auto 50px;
        -webkit-animation: rotation .6s infinite linear;
        -moz-animation: rotation .6s infinite linear;
        -o-animation: rotation .6s infinite linear;
        animation: rotation .6s infinite linear;
        border-left: 6px solid rgba(55, 55, 55, .15);
        border-right: 6px solid rgba(55, 55, 55, .15);
        border-bottom: 6px solid rgba(55, 55, 55, .15);
        border-top: 6px solid rgba(55, 55, 55, 1);
        border-radius: 100%;
      }
      
      @-webkit-keyframes rotation {
        from {
          -webkit-transform: rotate(0deg);
        }
        to {
          -webkit-transform: rotate(359deg);
        }
      }
      
      @-moz-keyframes rotation {
        from {
          -moz-transform: rotate(0deg);
        }
        to {
          -moz-transform: rotate(359deg);
        }
      }
      
      @-o-keyframes rotation {
        from {
          -o-transform: rotate(0deg);
        }
        to {
          -o-transform: rotate(359deg);
        }
      }
      
      @keyframes rotation {
        from {
          transform: rotate(0deg);
        }
        to {
          transform: rotate(359deg);
        }
      }
    
    </style>
    
@endpush

<div id="{{ $id }}">
   
 
    <x-cms-form-input type="text" name="cardholder" id="cardholder" label="Cardholder Name" value="{{old('cardholder', '')}}" wrapper="simple">
        The name exactly as it appears on the card
    </x-cms-form-input>

    <div id="card-element" class="stripe-card-wrap form-control sborder srounded sp-2 pt-2 mt-3 ">
    </div>
    
    <div id="card-errors" class="card-errors p-2"></div>

    <div class="text-center">
        <button id="stripe-submit" class="button btn btn-primary">{{ $buttonText }}</button>
    </div> 

    <div class="small pt-2 mt-3 text-center">
        <p>
            <a href="https://stripe.com" target="_blank"><img src="/vendor/ascent/transact/img/stripe.svg" height="20" width="auto" alt="Powered by STRIPE" border="0"/></a>
        </p>
        <p>
            Your card details will be processed by Stripe.
            {{-- <br/> --}}
            <span style="white-space: nowrap">{{ config('app.owner', config('app.name'))}}</span> will not have access to your full payment details.
        </p>
    </div>

    <x-cms-modal modalid="paymentspinner" centered="true" :showHeader="false" :showFooter="false" :closeButton="false" size="modal-sm">

        <x-slot name="title">Processing</x-slot>
    
        <div class="spinner"></div>
    
        <h4 class="text-center">Please wait...</h4>
    
        <p class="text-center">Processing your payment.</p>
    
    </x-cms-modal>
    

</div>