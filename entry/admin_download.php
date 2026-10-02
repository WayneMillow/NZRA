<?php
include_once 'includes/functions.php';
sec_session_start ();
if (login_check($mysqli) == true) {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=entrants.csv');
	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	// output the column headings
	fputcsv($output, array('TeamId', 'Status', 'Event', 'Grade', 'Name', 'Street', 'City', 'Country', 'Postcode', 'Phone', 'Email', 'Comments', 'TeamName', 'EntryDate', 'Payment', 'Received'));
	// fetch the data
	$event_id = $_SESSION['eventId'];
	$query = "SELECT entry.team_number, status, sub_event_code, CONCAT(gender_code, age_group_code), CONCAT_WS(' ', first_name, last_name), street, town, country, postcode, phone, email, comments, team_name, entry_date, payment_value, payment_received"
			. " FROM event JOIN entrant ON entrant.event_id = event.event_id JOIN entry ON entry.entry_id = entrant.entry_id JOIN member ON entrant.member_id = member.member_id"
			. " WHERE event.event_id = " . $event_id . " AND entry.status NOT IN ('cancel', 'waitList')"
			. " ORDER BY entry.sub_event_code DESC, entry.team_number";
	if (!($result = $mysqli->query($query))) {
		printf("Errormessage: %s\n", $mysqli->error);
	}
	// loop over the rows, outputting them
	while ($row = $result->fetch_assoc()) fputcsv($output, $row);
}
?>