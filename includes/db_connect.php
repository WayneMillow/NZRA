<?php
include_once 'psl-config.php'; // As functions.php is not included
$mysqli = new mysqli ( HOST, USER, PASSWORD, DATABASE );
/* check connection */
if ($mysqli->connect_errno) {
	printf("Connect failed: %s\n", $mysqli->connect_error);
	exit();
}
?>