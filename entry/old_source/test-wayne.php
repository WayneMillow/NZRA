<?php
include_once '../includes/db_connect.php';
$query = "UPDATE sequence SET entry_number = last_insert_id(entry_number + 1) WHERE event_id = 1";
if (! ($result = $mysqli->query ( $query ))) {
	printf ( "Errormessage: %s\n", $mysqli->error );
}
$team_number = mysqli_insert_id ( $mysqli );
echo "Team Number: ". $team_number;
?>