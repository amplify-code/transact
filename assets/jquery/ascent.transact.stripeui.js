$.ascent = $.ascent?$.ascent:{};

var StripeUI = {
        
    self: null,
    stripe: null,
    elements: null,
    card: null,
    intent: null,
    // style: null,
    
    _init: function () {
        
        var self = this;
        this.widget = this;
        var thisID = (this.element)[0].id;
        var obj = this.element;

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
        $(this.element).on('click', '#stripe-submit', function(e) {

            // show the spinner - this will trigger the payment process
            e.preventDefault();
            $('#paymentspinner')
                .modal({
                    backdrop: 'static',
                    keyboard: false
                })
                .modal('show');

        });
        

        $('#paymentspinner').on('shown.bs.modal', function (e) {

            let card = widget.card;
            // create the payment method from the card element
            widget.stripe
                .createPaymentMethod({
                    type: 'card',
                    card: widget.card,
                    billing_details: {
                        name: $('#cardholder').val(),
                    },
                })
                 // Then, with the payment method, send data to create an intent. 
                 // Should return either a PaymentIntent or SetupIntent
                 // - this is where we need a custom function to process the data which makes this decision
                 // - unless we just tell it to use the parent form data.
                 .then(function(result) {

                    if(result.error) {
                        // failed to create paymentMethod
                        widget.failFunction(result.error.message);

                    } else {

                        return new Promise(function(resolve, reject) {

                            $('.validation-error').remove();

                            let form = $(widget.element).parents('form')[0];
                            let url = $(form).attr('action');
                            let formData = new FormData(form);
                            formData.append('paymentMethod', result.paymentMethod.id);

                            $.ajax({       
                                type: 'POST',
                                url: $(form).attr('action'),
                                contentType: false,
                                processData: false,
                                data: formData,
                                headers: {
                                    'Accept' : "application/json"
                                }
                            }).done(function(data, xhr, request) {
                               
                                resolve(data);
                                // resolve(data);
                            }).fail(function(error) {
                                console.log(error);
                                if(error.status == 422) {
                                    // validation error - cancel the process
                                    // need code to process the validation errors
                                    processValidationErrors(error.responseJSON.errors);
                                    reject();    
                                } else {
                                    // otherwise, we have a declined card etc
                                    reject(error.responseJSON.message);
                                }
                            });

                        });

                    }
                
                })
                .then(function (result) {

                    /** Response is a payment intent. Attempt to confirm it */
                    if(result.object == 'payment_intent') {
                        widget.intent = result;
                        widget.stripe.confirmCardPayment(
                            result.client_secret, 
                            {
                                payment_method: result.payment_method,
                            }
                        ).then(function (result) {
                            if(result.error) {
                                widget.failFunction(result.error.message);
                            } else {                    
                                // success?? or should we poll waiting for the webhook? 
                                $(document).trigger('transact-success');
                            }
                        });
                    }

                    /** Response is a Setup Intent - Attempt to confirm it */
                    if(result.object == 'setup_intent') {
                        widget.intent = result;
                        widget.stripe.confirmCardSetup(
                            result.client_secret, 
                            {
                                payment_method: result.payment_method,
                            }
                        ).then(function (result) {
                            if(result.error) {
                                // alert('ERROR');
                                // console.log('si fail', result);
                                widget.failFunction(result.error.message);
                            } else {
                                // console.log('si confirmation result', result);
                                $(document).trigger('transact-success');
                            }
                        });
                    }


                }, function(error) {
                    widget.failFunction(error);
                });

        });



    },

   
    startFunction: function(resolve, reject) {
        reject(new Error('Start Promise has not been defined'));
    },
    
    setStartFunction: function(fn) {
        this.startFunction = fn;
    },

    failFunction: function(error) {

        console.log('fail fn', error);

        // populate an error message on the UI.
        $('#card-errors').html(error);

        $('#paymentspinner').modal('hide');

        // post the failure to the transact table.
        if(this.intent) {
            $.ajax({     
                type: 'POST',
                url: '/transact/fail',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    reference: this.intent ? this.intent.id : '',
                    message: error
                },
                headers: {
                    'Accept' : "application/json"
                }
            }).done(function(data, xhr, request) {      
                $('#paymentspinner').modal('hide');
            }).fail(function(data) {
                $('#paymentspinner').modal('hide');
            });
        } else {
            // $('#paymentspinner').modal('hide');
        }
    
    },

    setFailFunction: function(fn) {
        this.failFunction = fn;
    },

    // This may not be needed in this new version as we pre-confirm the paymentIntent during the server-side code
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
            // This could issue an event for the hosting page/package to respond to
            // (i.e. by navigating to a new page)
            // window.location = '/basket/complete';
            // $(document).trigger('transact-success');
            console.log(data);
            if(data == 'paid') {
                // $('#paymentspinner').modal('hide');
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


function processValidationErrors(data) {
    console.log(data);

    let errors = flattenObject(data);

    console.log('errors', errors);

    for(fldname in errors) { 

        let undotArray = fldname.split('.');
        for(i=1;i<undotArray.length;i++) {
            undotArray[i] = '[' + undotArray[i] + ']';
        }
        aryname = undotArray.join('');

        val = errors[fldname];

        if(typeof val == 'object') {
            val = Object.values(val).join('<BR/>');
        }

        $('.error-display[for="' + aryname + '"]').append('<small class="validation-error alert alert-danger form-text" role="alert">' +
            val + 
            '</small>');

    }
}



function flattenObject(ob) {
    var toReturn = {};

    for (var i in ob) {
        if (!ob.hasOwnProperty(i)) continue;

        if ((typeof ob[i]) == 'object' && ob[i] !== null) {
            var flatObject = this.flattenObject(ob[i]);
            for (var x in flatObject) {
                if (!flatObject.hasOwnProperty(x)) continue;

                toReturn[i + '.' + x] = flatObject[x];
            }
        } else {
            toReturn[i] = ob[i];
        }
    }
    return toReturn;
}