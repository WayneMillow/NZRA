<?php
include_once 'includes/functions.php';
sec_session_start ();
if (login_check($mysqli) == true) {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=navlight.csv');
	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	// output the column headings
	fputcsv($output, array('Team No', ' ', 'Grade', 'Name', 'Team Name', 'Event Code'));
	// fetch the data
	$event_id = $_SESSION['eventId'];
	$query = "SELECT entry.team_number, ' ', CONCAT(entry.gender_code, entry.age_group_code)"
		. ", CONCAT_WS(' ', member.first_name, member.last_name"
		. ", CASE WHEN event.use_country = 'Y' THEN CONCAT('(', ifnull(country.country_code, 'xxx'), ')') ELSE '' END)"	
		. ", replace(entry.team_name, ',', ' '), entry.sub_event_code"
		. " FROM event" 
		. " JOIN entrant ON entrant.event_id = event.event_id" 
		. " JOIN entry ON entry.entry_id = entrant.entry_id AND entry.status not in ('cancel', 'waitList')"
		. " JOIN member ON member.member_id = entrant.member_id" 
		. " LEFT OUTER JOIN country ON country.country_id = ucase(replace(member.country,' ', ''))"	
		. " WHERE event.event_id = " . $event_id
		. " ORDER BY entry.sub_event_code DESC, entry.team_number";
	if (!($result = $mysqli->query($query))) {
		printf("Errormessage: %s\n", $mysqli->error);
	}
	// loop over the rows, outputting them
	while ($row = $result->fetch_assoc()) fputcsv($output, $row);
}
?>