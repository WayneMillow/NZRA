<?php
include_once 'includes/functions.php';
sec_session_start ();
if (login_check($mysqli) == true) {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=labels.csv');
	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	// output the column headings
	fputcsv($output, array('Event Code', 'Grade', 'Team No', 'Team Name', 'Name'));
	// fetch the data
	$event_id = $_SESSION['eventId'];
	$query = "SELECT entry.sub_event_code, CONCAT(entry.gender_code, entry.age_group_code), entry.team_number, entry.team_name, team.names"
		. " FROM entry" 
		. " JOIN (SELECT entry_id, GROUP_CONCAT(CONCAT_WS(' ', member.first_name, member.last_name)) AS names"
		. " FROM entrant JOIN member ON member.member_id = entrant.member_id GROUP BY entry_id) AS team"
		. " ON team.entry_id = entry.entry_id"
		. " WHERE entry.status not in ('cancel', 'waitList')"
		. " AND entry.entry_id IN (SELECT entry_id FROM entrant WHERE entrant.event_id = " . $event_id . ")"				
		. " ORDER BY entry.sub_event_code DESC, entry.team_number";
	if (!($result = $mysqli->query($query))) {
		printf("Errormessage: %s\n", $mysqli->error);
	}
	// loop over the rows, outputting them
	while ($row = $result->fetch_assoc()) { 
		fputcsv($output, $row);
	}
}
?>