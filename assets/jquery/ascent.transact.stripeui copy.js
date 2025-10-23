$.ascent = $.ascent ? $.ascent : {};

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
        var thisID = this.element[0].id;
        var obj = this.element;

        // alert('Stripe Checkout: ' + this.options.key);

        this.stripe = Stripe(this.options.key);
        this.elements = this.stripe.elements({
            fonts: [{ cssSrc: this.options.cssSrc }]
        });

        this.card = this.elements.create("card", { style: this.options.style });
        this.card.mount("#card-element");

        // this.paymentElement = this.elements.create('card');

        this.card.on("change", ({ error }) => {
            const displayError = document.getElementById("card-errors");
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = "";
            }
        });

        let widget = this;
        $(this.element).on("click", "#stripe-submit", function (e) {
            e.preventDefault();
            $("#paymentspinner")
                .modal({
                    backdrop: "static",
                    keyboard: false
                })
                .modal("show");
        });

        window.addEventListener(
            "message",
            function (ev) {
                if (ev.data === "3DS-authentication-complete") {
                    self.on3DSComplete();
                }
            },
            false
        );

        $("#xpaymentspinner").on("shown.bs.modal", function (e) {
            let card = widget.card;
            widget.stripe
                .createPaymentMethod({
                    type: "card",
                    card: widget.card,
                    billing_details: {
                        name: $("#cardholder").val()
                    }
                })
                // Then, with the payment method, create a setup intent
                .then(function (result) {
                    return new Promise(function (resolve, reject) {
                        $.ajax({
                            type: "POST",
                            url: "/transact/setupintent",
                            data: {
                                payment_method: result.paymentMethod
                            },
                            headers: {
                                Accept: "application/json"
                            }
                        })
                            .done(function (data, xhr, request) {
                                resolve(data);
                            })
                            .fail(function (data) {});
                    });
                })
                // Then, confirm the setup intent (which will include 3DS checks etc)
                .then(function (result) {
                    return new Promise(function (resolve, reject) {
                        console.log(result);
                        widget.stripe
                            .confirmCardSetup(result.client_secret)
                            .then(function (si_result) {
                                console.log(si_result);
                                widget
                                    .startFunction(
                                        si_result.setupIntent.payment_method
                                    )
                                    .then(function (data) {
                                        console.log(data);
                                        console.log("END");
                                    });
                            });
                    });
                })
                // Now pass the payment method into the transact function to either make a payment or start a subscription

                // .then(function (result) {
                //     console.log(result);
                // })
                .then(function (result) {
                    console.log("in here", result);
                    console.log(result.setupIntent.payment_method);

                    if (result.error) {
                        // Show error to your customer (e.g., insufficient funds)

                        // This could issue an event for the hosting page/package to respond to
                        // (i.e. might need to clean up the transation? Maybe we also set a 'failed' flag?)
                        // widget.markFailed();
                        widget.failFunction(result.error.message);
                    } else {
                        // payment method was created ok.
                        // Run the transact start function.
                        // The result will either be a payment intent (with 'succeeded' status)
                        // or a subscription_schedule
                        // @TODO - is it possible to write a generic start function which auto detects the parent form and submits it?
                        widget
                            .startFunction(result.setupIntent.payment_method)
                            .then(
                                function (data) {
                                    console.log(data);

                                    if (data.status == "succeeded") {
                                        $(document).trigger("transact-success");
                                        // widget.pollStatus(data.id);
                                    }

                                    if (data.status == "requires_action") {
                                        widget.stripe
                                            .retrievePaymentIntent(
                                                data.client_secret
                                            )
                                            .then(function (result) {
                                                console.log(
                                                    "retrieved",
                                                    result
                                                );
                                                // Handle result.error or result.paymentIntent
                                            });
                                    }
                                    //     widget.intent = data;
                                    //     var iframe = document.createElement('iframe');
                                    //     iframe.src = data.next_action.redirect_to_url.url;
                                    //     iframe.width = 600;
                                    //     iframe.height = 400;
                                    //     $('#paymentspinner .modal-body').append(iframe);
                                    // }

                                    if (
                                        data.object == "subscription_schedule"
                                    ) {
                                        // $(document).trigger('transact-success');
                                    }
                                },
                                function (error) {
                                    widget.failFunction(error);
                                }
                            );
                    }
                });
        });
    },

    on3DSComplete: function () {
        alert("£DS DONE");
        console.log("intent", this.intent);

        this.stripe
            .retrievePaymentIntent(this.intent.client_secret)
            .then(function (result) {
                console.log(result);
                if (result.error) {
                    // PaymentIntent client secret was invalid
                } else {
                    if (result.paymentIntent.status === "succeeded") {
                        // Show your customer that the payment has succeeded
                        $(document).trigger("transact-success");
                    } else if (
                        result.paymentIntent.status ===
                        "requires_payment_method"
                    ) {
                        // Authentication failed, prompt the customer to enter another payment method
                    }
                }
            });
    },

    startFunction: function (resolve, reject) {
        reject(new Error("Start Promise has not been defined"));
    },

    setStartFunction: function (fn) {
        this.startFunction = fn;
    },

    failFunction: function (error) {
        // populate an error message on the UI.
        $("#card-errors").html(error);

        $("#paymentspinner").modal("hide");

        // post the failure to the transact table.
        $.ajax({
            type: "POST",
            url: "/transact/fail",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                reference: this.intent ? this.intent.id : "",
                message: error
            },
            headers: {
                Accept: "application/json"
            }
        })
            .done(function (data, xhr, request) {
                $("#paymentspinner").modal("hide");
            })
            .fail(function (data) {
                $("#paymentspinner").modal("hide");
            });
    },

    setFailFunction: function (fn) {
        this.failFunction = fn;
    },

    // This may not be needed in this new version as we pre-confirm the paymentIntent during the server-side code
    pollStatus: function (id) {
        console.log("polling...");

        let widget = this;

        $.ajax({
            type: "GET",
            url: "/transact/poll-reference/" + id,
            headers: {
                Accept: "application/json"
            }
        })
            .done(function (data, xhr, request) {
                // This could issue an event for the hosting page/package to respond to
                // (i.e. by navigating to a new page)
                // window.location = '/basket/complete';
                // $(document).trigger('transact-success');
                console.log(data);
                if (data == "paid") {
                    // $('#paymentspinner').modal('hide');
                    $(document).trigger("transact-success");
                } else {
                    widget.pollStatus(id);
                }
            })
            .fail(function (data) {
                alert(data);
            });
    }
};

$.widget("ascent.stripeui", StripeUI);
$.extend($.ascent.stripeui, {});
