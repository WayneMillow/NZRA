<?php
include_once 'includes/db_connect.php';
$event_id = $_SESSION['eventId'];
?>
<div id="header" class="row">
  <div class="large-3 columns">
    <img class="center" src="img/Logo3_130x118.gif" alt="logo" width="130" height="118">
  </div>
  <div class="large-9 columns">
    <h1><?php echo get_description($event_id, $mysqli); ?></h1>
    <h2 class="subheader"><?php echo get_event_date($event_id, $mysqli);?></h2>
  </div>
<!--   
  <div class="row">
    <div class="small-12 columns text-center">
      <div class="error">This is a test site only. Completing this form does not enter any event. Nor does it make any obligation for payment of any value.</div>
    </div>
  </div>
 -->  
  <div class="row">
<?php
	$sql =
		"SELECT CONCAT(c1.first_name, ' ', c1.last_name) AS event_name, c1.email, c1.phone," .
		"       CONCAT(c2.first_name, ' ', c2.last_name) AS entry_name, c2.email, c2.phone" .
		" FROM event" .
		" JOIN contact AS c1 ON c1.contact_id = event.event_contact_id" .
		" JOIN contact AS c2 ON c2.contact_id = event.entry_contact_id" .
		" WHERE event.event_id = ?";
	if ($stmt = $mysqli->prepare ($sql)) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1) {
			$stmt->bind_result($event_name, $event_email, $event_phone, $entry_name, $entry_email, $entry_phone);
			$stmt->fetch();
		
			echo '<div class="large-4 medium-6 small-12 columns">';
			echo '<h3><small>Event Enquiries to</small></h3>';
			echo '<p>'.$event_name.'<br>';
			echo 'Email: <a href="mailto:'.$event_email.'">'.$event_email.'</a><br>';
			echo 'Phone: '.$event_phone.'</p></div>';
		
			echo '<div class="large-4 medium-6 small-12 columns">';
			echo '<h3><small>Entry Enquiries to</small></h3>';
			echo '<p>'.$entry_name.'<br>';
			echo 'Email: <a href="mailto:'.$entry_email.'">'.$entry_email.'</a><br>';
			echo 'Phone: '.$entry_phone.'</p></div>';
		}
		$stmt->close();
	}
	$sql = 
	  "SELECT sub_event.description, sub_event.senior_price, sub_event.junior_price" .
		" FROM event" .
		" JOIN sub_event ON sub_event.event_id = event.event_id" .
		" WHERE event.event_id = ?";
	if ($stmt = $mysqli->prepare ($sql)) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($description, $senior_price, $junior_price);
		echo '<div class="large-4 medium-6 small-12 columns">';
		echo '<h3><small>Entry Fees</small></h3>';
		echo '<p>';
		while ($stmt->fetch()){
				echo $description.' - $'. $senior_price.' per person (Junior-$'. $junior_price.')<br>';
		}
		echo '</p></div>';
		$stmt->close();
	}
?>
		<div class="large-12 columns">
			<strong>Entries close <?php echo get_entry_close($event_id, $mysqli);?></strong>.<br> 
			For current numbers see the <a target="_blank" href="entrants.php?eventId=<?php echo $event_id;?>">List of Entrants</a>.
			Late entries at the discretion of the organizer.
		</div>
	</div>
	<hr>
</div>