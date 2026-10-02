<?php
/**
 * These are the database login details
 */
/*
define ( "HOST", "localhost" ); // The host you want to connect to.
define ( "USER", "ynemg_user" ); // The database username.
define ( "PASSWORD", "dNe87q@5" ); // The database password.
define ( "DATABASE", "ynemgma32547com41354_nzra" ); // The database name.
*/
define ( "HOST", "localhost" ); // The host you want to connect to.
define ( "USER", "sec_user" ); // The database username.
define ( "PASSWORD", "eKcGZr59zAa2BEWU" ); // The database password.
define ( "DATABASE", "nzra" ); // The database name.

define ( "CAN_REGISTER", "any" );
define ( "DEFAULT_ROLE", "committee" );

define ( "SECURE", FALSE ); // FOR DEVELOPMENT ONLY!!!!

define ( "PAYSTATIONURL", "https://www.paystation.co.nz/direct/paystation.dll");
define ( "PAYSTATIONID", '615342'); //Paystation ID - Replace PAYSTATIONID with your Paystation ID.
define ( "RETURN_HOST", 'localhost'); //HOST name for return URL
define ( "GATEWAYID", 'PAYSTATION'); //Gateway ID - Replace GATEWAYID with your GATEWAY ID.
define ( "TESTMODE", 'true'); //change this to 'false' for production transactions.
define ( "HMACMODE", true); //change this to 'true' to use hmac and also change the next variable
define ( "HMAC_SECURITY_CODE", '8c2KnE9jyVWlmNM3'); // change this to the Paystation supplied security code
?>