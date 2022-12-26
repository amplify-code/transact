<?php

return [

    /** Payment Provider 
     * Currently supports only Stripe, but need to wire up for PayPal etc too.
    */
    'payment_provider' => 'stripe',
    'stripe_public_key' => env('STRIPE_PUBLIC', 'public_key'),
    'stripe_secret_key' => env('STRIPE_SECRET', 'secret_key'),

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
