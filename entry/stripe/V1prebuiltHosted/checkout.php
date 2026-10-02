<?php

require_once '../../stripe-php-master/init.php';
require_once '../secrets.php';

\Stripe\Stripe::setApiKey($stripeSecretKey);
header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost/entry/stripe/V1prebuiltHosted';

$checkout_session = \Stripe\Checkout\Session::create([
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
  'success_url' => $YOUR_DOMAIN . '/success.html',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);