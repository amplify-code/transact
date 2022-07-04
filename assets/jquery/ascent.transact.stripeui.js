$.ascent = $.ascent?$.ascent:{};

var StripeUI = {
        
    self: null,
    stripe: null,
    elements: null,
    card: null,
    // style: null,
    
    _init: function () {
        
        var self = this;
        this.widget = this;
        var thisID = (this.element)[0].id;
        var obj = this.element;

        // alert('Stripe Checkout: ' + this.options.key);

        this.stripe = Stripe(this.options.key);
        this.elements = this.stripe.elements({
            fonts: [
                { cssSrc: this.options.cssSrc }
            ]
        });

        this.card = this.elements.create("card", { style: this.options.style });
        this.card.mount("#card-element");

        this.card.on('change', ({error}) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });

        let widget = this;
        $(this.element).on('click', '#stripe-submit', function() {

            
            $('#paymentspinner').modal('show');

            let promise = new Promise(widget.startFunction);
            
            promise.then(function(result) {

                // try {
                    widget.stripe.confirmCardPayment(result.client_secret, {  
                        payment_method: {
                            card: widget.card,
                            billing_details: {
                                name: $('#cardholder').val(),
                            }
                    
                        }
                                
                    }).then(function(result) {
                        if (result.error) {
                        // Show error to your customer (e.g., insufficient funds)
                            $('#paymentspinner').modal('hide');
                            console.log(result.error.message);

                            // This could issue an event for the hosting page/package to respond to
                            // (i.e. might need to clean up the transation? Maybe we also set a 'failed' flag?)
                            

                        } else {
                        // The payment has been processed!
                        if (result.paymentIntent.status === 'succeeded') {

                            console.log(result);
                            console.log(widget);

                            // This could issue an event for the hosting page/package to respond to
                            // (i.e. by navigating to a new page)
                            // window.location = '/basket/complete';
                            // $(document).trigger('transact-success');

                            // as we're using a payment spinner modal,
                            // let's keep that in place, and poll the server to see if the 
                            // transaction has completed via the webhook.
                            widget.pollStatus(result.paymentIntent.id);
                            

                            // Show a success message to your customer
                            // There's a risk of the customer closing the window before callback
                            // execution. Set up a webhook or plugin to listen for the
                            // payment_intent.succeeded event that handles any business critical
                            // post-payment actions.
                        }
                        }
                    });

                // } catch(e) {
                // alert(e);
                // }
            }, 
            function(error) { 
                $('#paymentspinner').modal('hide');
                alert(error);
            }).then(
                function(result) {},
                function(error) {
                    $('#paymentspinner').modal('hide');
                    alert(error);
                }
            
            );;
            
        });
        
    },

    startFunction: function(resolve, reject) {
        reject(new Error('Start Promise has not been defined'));
    },
    

    setStartFunction: function(fn) {
        this.startFunction = fn;
    },

    pollStatus: function(id) {

        console.log('polling...');

        let widget = this;
        
        $.ajax({     
            type: 'GET',
            url: '/transact/poll-reference/' + id,
            headers: {
                'Accept' : "application/json"
            }
        }).done(function(data, xhr, request) {
            // resolve('force-fail');
            console.log(data);
            if(data == 'paid') {
                $(document).trigger('transact-success');
            } else {
               widget.pollStatus(id);
            }
        }).fail(function(data) {
            alert(data);
        });

      

    }


}

$.widget('ascent.stripeui', StripeUI);
$.extend($.ascent.stripeui, {
	 
}); 

