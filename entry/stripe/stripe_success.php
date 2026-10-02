<?php

require_once '../stripe-php-master/init.php';
require_once '../stripe/secrets.php';

\Stripe\Stripe::setApiKey($stripeSecretKey);

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];
    $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
    
    // Access information from the $checkout_session object
    $payment_intent_id = $checkout_session->payment_intent;
    $customer_email = $checkout_session->customer_details->email;
    // ... and so on
    
    // Further processing, e.g., updating your database, sending confirmation emails
    echo "Payment successful! Session ID: " . $session_id;
    echo "<br>Payment Intent ID: " . $payment_intent_id;
    echo "<br>Customer Email: " . $customer_email;
    
} else {
    echo "Error: No session ID found.";
}