<?php

return [

    /** Payment Provider 
     * Currently supports only Stripe, but need to wire up for PayPal etc too.
     */
    'payment_provider' => 'stripe',
    'stripe_public_key' => env('STRIPE_PUBLIC'),
    'stripe_secret_key' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'stripe_test_clocks' => env('STRIPE_TEST_CLOCKS', false),

    /**
     * Styling defaults
     */
    'cssSrc' => '',
    'style' => [
        'base' =>
        [
            'backgroundColor' => "#ffffff",
            'padding' => '10px',
            // 'fontFamily' => 'Montserrat, sans-serif'
        ]
    ]

];
