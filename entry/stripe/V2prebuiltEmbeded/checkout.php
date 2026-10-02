<?php

require_once '../../stripe-php-master/init.php';
require_once '../secrets.php';

$stripe = new \Stripe\StripeClient($stripeSecretKey);
header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost/entry/stripe/V2prebuiltEmbeded';

$checkout_session = $stripe->checkout->sessions->create([
  'ui_mode' => 'embedded',
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
  'return_url' => $YOUR_DOMAIN . '/return.html?session_id={CHECKOUT_SESSION_ID}',
]);

  echo json_encode(array('clientSecret' => $checkout_session->client_secret));