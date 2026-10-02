<?php
include_once 'includes/db_connect.php';
$event_id = $_POST['eventId'];
$event = $_POST['event'];
if ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ?" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo '>$'.$junior_price.' Junior</option>';
	}	else {
		echo '<option value="">Please Select an Event</option>';
	}
}	else {
	echo '<option value="">Please Select an Event</option>';
}
?>