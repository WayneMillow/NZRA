<?php
include_once 'db_connect.php';
include_once 'functions.php';
sec_session_start ();
if (isset ( $_POST ['email'], $_POST ['p'] )) {
	$email = $_POST ['email'];
	$password = $_POST ['p']; // The hashed password.
	$event_id = $_POST['eventId'];
	if (login ( $email, $password, $mysqli ) == true) {
		// Login success
	    $_SESSION['eventId'] = $event_id;
		header ( 'Location: ../admin_entrants.php?eventId='.$event_id );
	} else {
		// Login failed
		header ( 'Location: ../entrants.php?error=1' );
	}
} else {
	// The correct POST variables were not sent to this page.
	echo 'Invalid Request';
}
?> 