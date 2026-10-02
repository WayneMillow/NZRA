<?php

require_once '../../stripe-php-master/init.php';
require_once '../secrets.php';

$stripe = new \Stripe\StripeClient([
  "api_key" => $stripeSecretKey,
  "stripe_version" => "2025-11-17.clover"
]);
header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost/entry/stripe/V3customCheckoutSessions';

$checkout_session = $stripe->checkout->sessions->create([
  'ui_mode' => 'custom',
    'line_items' => [[
        'price_data' => [
            'currency' => 'nzd',
            'product_data' => [
                'name' => 'Entry Fees',
            ],
            'unit_amount' => 12000,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
  'return_url' => $YOUR_DOMAIN . '/complete.html?session_id={CHECKOUT_SESSION_ID}',
]);

echo json_encode(array('clientSecret' => $checkout_session->client_secret));