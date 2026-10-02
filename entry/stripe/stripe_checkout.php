<?php

require_once './stripe-php-master/init.php';
require_once './stripe/secrets.php';

$stripe = new \Stripe\StripeClient($stripeSecretKey);
header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost/entry/stripe';

$checkout_session = $stripe->checkout->sessions->create([
    'line_items' => [[
        'price_data' => [
            'currency' => 'nzd',
            'product_data' => [
                'name' => 'Entry Fees',
            ],
            'unit_amount' => $amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'customer_email' => $to,
    'success_url' => $YOUR_DOMAIN . '/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $YOUR_DOMAIN . '/stripe_cancel.php',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);

?>