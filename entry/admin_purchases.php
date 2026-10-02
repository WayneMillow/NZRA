<?php
include_once 'includes/functions.php';
sec_session_start ();
if (login_check($mysqli) == true) {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=purchases.csv');
	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	// output the column headings
	fputcsv($output, array('TeamId', 'Name', 'Extra', 'Number', 'Cost', 'Total'));
	// fetch the data
	$event_id = $_SESSION['eventId'];
	$query = "SELECT distinct team_number, team_name, extra.description, quantity, cost, quantity * cost"
		. " FROM event JOIN entrant ON entrant.event_id = event.event_id"
        . " JOIN entry ON entry.entry_id = entrant.entry_id" 		
		. " JOIN entry_extra ON entry_extra.entry_id = entrant.entry_id"
        . " JOIN extra ON extra.event_id = event.event_id AND extra.code = entry_extra.code"
		. " WHERE event.event_id = " . $event_id
        . " AND quantity > 0"
		. " ORDER BY entry.sub_event_code DESC, entry.team_number";
	if (!($result = $mysqli->query($query))) {
		printf("Errormessage: %s\n", $mysqli->error);
	}
	// loop over the rows, outputting them
	while ($row = $result->fetch_assoc()) fputcsv($output, $row);
}
?>