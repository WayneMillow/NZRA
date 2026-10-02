<?php
include_once 'includes/functions.php';
sec_session_start ();
if (find_event($mysqli) === false){
	header('Location: no_event_error.php');
	exit();
}
// Set up variables to hold the form fields and errors
if (isset ( $_SESSION ['eventId'] )) {
	$event_id = $_SESSION ['eventId'];
}
if (isset ($_GET['id'])) {
	$entry_id = filter_input ( INPUT_GET, 'id', $filter = FILTER_SANITIZE_STRING );
	$_SESSION ['entry_id'] = $entry_id;
} else {
	$entry_id = $_SESSION ['entry_id'];	
}
unset ( $_SESSION ['member_id'] );
$numberOfExtra = getNumberOfExtra($event_id, $mysqli);
$team_number = 0;
$team_name = '';
$gender = '';
$age_group = '';
$event = '';
$payment_type = '';
$payment_value = 0;
$payment_received = 0;
$waiver = '';
$comments = '';
$status = '';
$entry_date = date("F j, Y, g:i a");
$entryFees = 0;
$totalFees = 0;
$extraCode = array_fill(0, $numberOfExtra, '');
$extraValue = array_fill(0, $numberOfExtra, 0);
$extraQuantity = array_fill(0, $numberOfExtra, 0);
$team_nameError = '';
$genderError = '';
$age_groupError = '';
$eventError = '';
$payment_typeError = '';
$payment_receivedError = '';
$statusError = '';
$extraError = array_fill(0, $numberOfExtra, '');
$commentsError = '';
$waiverError = '';
$pageError = false;
if (empty ( $_POST ['submitButton'] )) {
	if ($entry_id > 0) {
		if (! ($stmt = $mysqli->prepare ( "SELECT team_number, team_name, gender_code, age_group_code, sub_event_code, payment_type, payment_value, payment_received, waiver, comments, status, entry_date FROM entry where entry_id = ?" ))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param ( 'i', $entry_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute ())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->bind_result ( $team_number, $team_name, $gender, $age_group, $event, $payment_type, $payment_value, $payment_received, $waiver, $comments, $status, $entry_date ))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->fetch ())) {
			echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close ();
		if ($payment_received != $payment_value) {
			$payment_receivedError = 'Payment received does not equal payment owed.';
		}
		if (empty ( $status )) {
			$status = 'rcvd';
		}
		// Set up any extras purchased
		if (! ($stmt = $mysqli->prepare ( "SELECT extra.code, cost, IFNULL(quantity, 0) FROM extra LEFT OUTER JOIN entry_extra ON entry_id = ? AND entry_extra.code = extra.code WHERE event_id = ? ORDER BY event_id, extra.sequence, extra.code" ))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param ( 'ii', $entry_id, $event_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute ())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->store_result ())) {
			echo "Store result failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->bind_result ( $code, $cost, $quantity))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$index = 0;
		while ($stmt->fetch ()) {
			$extraCode[$index] = $code;
			$extraValue[$index] = $cost;
			$extraQuantity[$index] = $quantity;
			$index += 1;
			$totalFees += $quantity * $cost;
		}
		$stmt->close ();
	}
	// If this form submitted the data then validate it
} else {
	if (! empty ( $_POST ['entry_id'] )) {
		$entry_id = $_POST ['entry_id'];
	}
	if (! empty ( $_POST ['entry_date'] )) {
		$entry_date = $_POST ['entry_date'];
	}
	if (! empty ( $_POST ['payment_value'] )) {
		$payment_value = $_POST ['payment_value'];
	}
	$team_name = strip_tags ( trim ( $_POST ['team_name'] ) );
	if (empty($team_name) || strlen($team_name) < 1 || strlen($team_name) > 32){
		$team_nameError = 'Please choose a Team Name between 1 and 32 characters';
		$pageError = true;
	}
	if (empty ( $_POST ['gender'] )) {
		$genderError = 'Please select a Gender';
		$pageError = true;
	} else {
		$gender = $_POST ['gender'];
	}
	if (empty ( $_POST ['age_group'] )) {
		$age_groupError = 'Please select an Age Group';
		$pageError = true;
	} else {
		$age_group = $_POST ['age_group'];
	}
	if (empty ( $_POST ['event'] )) {
		$eventError = 'Please select an Event';
		$pageError = true;
	} else {
		$event = $_POST ['event'];
	}
	if (empty ( $_POST ['payment_type'] )) {
		$payment_typeError = 'Please select a Payment Type';
		$pageError = true;
	} else {
		$payment_type = $_POST ['payment_type'];
	}
	$payment_value = $_POST ['payment_value'];
	$payment_received = 0;
	if (empty ( $_POST ['payment_received'] ) and !empty ( $_POST ['status'] ) and $_POST ['status'] !== "paid") {
	} else if (empty ( $_POST ['payment_received'] )) {
		$payment_receivedError = 'Please key a payment value';
		$pageError = true;
	} else {
		$options = array ( 'options' => array ('min_range' => 0));
		if (filter_var ( $_POST ['payment_received'], FILTER_VALIDATE_INT, $options ) === FALSE) {
			$payment_receivedError = 'Please key a positive payment';
			$pageError = true;
		} else {
			$payment_received = $_POST ['payment_received'];
			if ($payment_received != $payment_value) {
				$payment_receivedError = 'Payment received does not equal payment owed.';
			}
		}
	}
	$extraCode = $_POST['extraCode'];
	$extraValue = $_POST['extraValue'];
	$extraQuantity = $_POST['extraQuantity'];
	for ($idx = 0; $idx < $numberOfExtra; $idx++) {
		if ($extraQuantity[$idx] < 0 || $extraQuantity[$idx] > 10) {
			$extraError[$idx] = 'Please key a number between 0 and 10';
			$pageError = TRUE;
			}
	}
	if (empty($_POST['waiver'])) {
	  $waiverError = 'You must accept the terms and conditions in order to enter this event';
	  $pageError = true;
	} else {
	  $waiver = $_POST['waiver'];
	}
	if (empty ( $_POST ['status'] )) {
		$statusError = 'A status must be seletced';
		$pageError = true;
	} else {
		$status = $_POST ['status'];
	}
	if (! empty ( $_POST ['comments'] )) {
		$comments = htmlentities ( trim ( $_POST ['comments'] ) );
	} else {
		$comments = "";
	}
	// If no update the database
	if ($pageError === FALSE) {
		if ($entry_id != 0) {
			$isNew = FALSE;
			if (! ($stmt = $mysqli->prepare ( "UPDATE entry set team_name = ?, gender_code = ?, age_group_code = ?, sub_event_code = ?, payment_type = ?, payment_value = ?, payment_received = ?, waiver = ?, comments = ?, status = ? WHERE entry_id = ?" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit ();
			}
			if (! ($stmt->bind_param ( 'sssssiisssi', $team_name, $gender, $age_group, $event, $payment_type, $payment_value, $payment_received, $waiver, $comments, $status, $entry_id ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
		} else {
			$isNew = TRUE;
			// Get the next team number for this event
			if (!($stmt = $mysqli->prepare("UPDATE sequence SET entry_number = last_insert_id(entry_number + 1) WHERE event_id = ? AND sub_event_code = ?" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (!($stmt->bind_param ( 'is', $event_id, $event))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (!($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			$team_number = mysqli_insert_id($mysqli);
			$stmt->close();
			// Add the entry
			if (!($stmt = $mysqli->prepare("INSERT INTO entry(team_number, team_name, gender_code, age_group_code, sub_event_code, payment_type, payment_value, payment_received, waiver, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (! ($stmt->bind_param ( 'isssssiiss', $team_number, $team_name, $gender, $age_group, $event, $payment_type, $payment_value, $payment_received, $waiver, $comments ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			$entry_id = mysqli_insert_id ( $mysqli );
			$stmt->close();
			// Add a dummy member
			if (!($stmt = $mysqli->prepare("INSERT INTO member (first_name, last_name, street, town, country, postcode, email, phone) VALUES ('Entrant', 'Dummy', '', '', '', '', '', '')" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (!($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			$member_id = mysqli_insert_id($mysqli);
			$stmt->close();
			// And add an entrant to link them all
			if (!($stmt = $mysqli->prepare("INSERT INTO entrant (event_id, entry_id, member_id, entry_fee) VALUES (?, ?, ?, 0)" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (!($stmt->bind_param ( 'iii', $event_id, $entry_id, $member_id ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			$stmt->close();
		}
		if (!($stmt = $mysqli->prepare("INSERT INTO entry_extra(entry_id, code, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = ?" ))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		// Loop for each extra
		for ($i = 0; $i < $numberOfExtra; $i++) {
			if (!($stmt->bind_param('isii', $entry_id, $extraCode[$i], $extraQuantity[$i], $extraQuantity[$i] ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (!($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
		}
		$stmt->close();
		if ($isNew) {
			header ( 'Location: admin_entry.php?id='. $entry_id );
		} else {
			header ( 'Location: admin_entrants.php' );
			}
		exit ();
	}
}
?>
<!doctype html>
<html class="no-js" lang="en">
<?php include 'head.php'?>
<body>
	<div id="header" class="row">
		<div class="small-12 large-2 columns">
			<img class="center" src="img/Logo3_130x118.gif" alt="logo"
				width="130" height="118">
		</div>
		<div class="small-12 large-6 columns">
			<h1>
			<?php
			$result=get_description($event_id, $mysqli);
			echo $result;
			?></h1>
			<h2 class="text-center subheader">Administration</h2>
		</div>
		<div class="small-12 large-4 columns">
			<div class="nav-bar right">
				<ul class="button-group">
					<li><a class="button small" href="admin_entrants.php">Entrants</a></li>
					<li><a class="button small" href="includes/logout.php?id=<?php echo $_SESSION['eventId'];?>">Logout</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="main" class="row">
		<div class="large-12 columns">
<?php if (login_check($mysqli) == true) : ?>
    <div class="row">
				<div class="small-12 columns">
					<fieldset>
						<legend>Entrants</legend>
						<table>
							<thead>
								<tr>
									<th><a href="admin_member.php?id=0">Add</a></th>
									<th>Name</th>
									<th>Address</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Fees</th>
								</tr>
							</thead>
							<tbody>
<?php
	if (! ($stmt = $mysqli->prepare ( "SELECT member.member_id, first_name, last_name, street, town, postcode, email, phone, entry_fee FROM entrant
		JOIN member ON member.member_id = entrant.member_id WHERE event_id = ? AND entry_id = ?" ))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if (! ($stmt->bind_param ( 'ii', $event_id, $entry_id ))) {
		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	if (! ($stmt->execute ())) {
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	if (! ($stmt->bind_result ( $member_id, $first_name, $last_name, $street, $town, $postcode, $email, $phone, $entry_fee ))) {
		echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	while ( $stmt->fetch () ) {
		echo '<tr>';
		echo '<td><a href="admin_member.php?id=' . $member_id . '">Edit</a></td>';
		echo '<td>' . $first_name . ' ' . $last_name;
		echo '</td>';
		echo '<td>' . $street . ', ' . $town . ' ' . $postcode . '</td>';
		echo '<td>' . $email . '</td>';
		echo '<td>' . $phone . '</td>';
		echo '<td>$' . $entry_fee . '</td>';
		echo '</tr>';
		$entryFees += $entry_fee;
	}
	$stmt->close ();
	$totalFees += $entryFees;
	?>
              </tbody>
						</table>
					</fieldset>
				</div>
			</div>
<!-- 			
			<div class="row">
				<div class="small-12 columns">
					This is a test button for a user to request further extra's.
php echo '<a class="button" href="event_payment.php?eventId=' . $event_id . '&entryId=' . $entry_id . '">Request Extras</a>';					
				</div>
			</div>
 -->			
			<form id="headerForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<input id="entry_id" type="hidden" name="entry_id" value="<?php echo $entry_id; ?>" />
				<input type="hidden" name="entry_date" value="<?php echo $entry_date; ?>" />
				<input id="numberOfExtra" type="hidden" value="<?php echo $numberOfExtra;?>" />
				<input id="entryFees" type="hidden" value="<?php echo $entryFees; ?>" />
				<div class="row">
					<div class="small-12 columns">
									<div id="formErrors" class="error">
<?php if ($pageError === TRUE) : ?>Errors exist! Please check the form for the details, correct them and press enter again.<?php endif; ?>
									</div>				
						<fieldset>
							<legend>Team Number&nbsp;<?php echo $team_number;?></legend>
							<div class="row">
								<div class="large-9 small-12 columns">
									<label>
										Team Name
										<input type="text" name="team_name" maxlength="32" value="<?php echo $team_name;?>">
									</label>
									<div class="error"><?php echo $team_nameError; ?></div>
								</div>
								<div class="large-3 small-12 columns">
								  <p><strong>Entry Date:</strong><br><?php echo date("F j, Y, g:i a", strtotime($entry_date)) ?></p>
								</div>
							</div>
							<div class="row">
								<div class="large-3 medium-6 small-12 columns">
									<h4>
										<small>Gender</small>
									</h4>
									<div class="error"><?php echo $genderError; ?></div>
<?php
$sql = "SELECT code, description FROM gender WHERE event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($code, $description);
	while ($stmt->fetch()) {
		echo '<label><input type="radio" name="gender" value="'.$code.'"';
		echo ($gender==$code) ? ' checked' : '';
		echo ' />&nbsp;' .$description.'</label>';
	}
	$stmt->close();
}
?>								
								</div>
								<div class="large-3 medium-6 small-12 columns">
									<h4>
										<small>Age Group</small>
									</h4>
									<div class="error"><?php echo $age_groupError; ?></div>
<?php 
$sql = "SELECT code, description FROM age_group WHERE event_id = ? ORDER BY lower_age";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($code, $description);
	while ($stmt->fetch()) {
	echo '<label><input type="radio" name="age_group" value="'.$code.'"';
		echo ($age_group==$code) ? ' checked' : '';
		echo ' />&nbsp;' .$description.'</label>';
	}
	$stmt->close();
}
?>									
								</div>
								<div class="large-3 medium-6 small-12 columns">
									<h4>
										<small>Event</small>
									</h4>
									<div class="error"><?php echo $eventError; ?></div>
<?php 
$sql = "SELECT code, description FROM sub_event WHERE event_id = ? ORDER BY code DESC";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($code, $description);
	while ($stmt->fetch()) {
	echo '<label><input type="radio" name="event" value="'.$code.'"';
		echo ($event==$code) ? ' checked' : '';
		echo ' />&nbsp;' .$description.'</label>';
	}
	$stmt->close();
}
?>									
								</div>
								<div class="large-3 medium-6 small-12 columns">
									<h4>
										<small>Payment</small>
									</h4>
									<label>Owed $<input type="text" id="payment_value" name="payment_value" maxlength="4" readonly="readonly" value="<?php echo $totalFees;?>" /></label>
									<div class="error"><?php echo $payment_receivedError; ?></div>
									<label>Received $<input type="text" name="payment_received" maxlength="4" value="<?php echo $payment_received;?>" /></label>
									<div class="error"><?php echo $payment_typeError; ?></div>
									<label><input type="radio" name="payment_type" value="credit card" <?php echo ($payment_type=='credit card')?' checked':''?> >&nbsp;Credit Card</label>
									<label><input type="radio" name="payment_type" value="direct credit" <?php echo ($payment_type=='direct credit')? 'checked':''; ?>>&nbsp;Direct credit</label>
									<label><input type="radio" name="payment_type" value="cheque" <?php echo ($payment_type=='cheque')? 'checked':''; ?>>&nbsp;Cheque</label>
								</div>
							</div>
<?php 
$sql = "SELECT code, description, cost FROM extra WHERE event_id = ? ORDER BY event_id, sequence, code";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($code, $description, $cost);
	if ($stmt->num_rows > 0) {
	  echo '<div class="row"><div class="small-12 columns"><h4><small>Optional Extras</small></h4>';
		echo '<p>Enter the number of each extra required. The cost will be added to amount owed.</p>';
	  echo '<div class="row">';
	  $index = 0;
	  while ($stmt->fetch()) {
			echo '<div class="large-3 small-12 columns">';
			echo '<input type="hidden" name="extraCode['.$index.']" value="'.$code.'" />';
			echo '<input type="hidden" name="extraValue['.$index.']" value="'.$cost.'" />';
			echo '<label>'.$description.' - <b>$'.$cost.'</b><input type="number" name="extraQuantity['.$index.']" min="0" max="10" value="'.$extraQuantity[$index].'" onchange="adminPaymentValue()" /></label>';
			echo '<div class="error">'.$extraError[$index].'</div>';
			echo '</div>';
			$index += 1;
		}
		echo '</div></div></div>';
	}
	$stmt->close();
}
?>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<h4>
										<small>Status</small>
									</h4>
									<div class="error"><?php echo $statusError; ?></div>
									<label>
										<input type="radio" name="status" value="rcvd" <?php echo ($status=='rcvd')?'checked':'';?> />
									&nbsp;Entry Received</label> 
									<label>
										<input type="radio" name="status" value="paid" <?php echo ($status=='paid')?'checked':'';?> />
									&nbsp;Payment Received</label>
									<label>
										<input type="radio" name="status" value="waitList" <?php echo ($status=='waitList')?'checked':'';?> />
									&nbsp;Wait Listed</label>
									<label>
										<input type="radio" name="status" value="cancel" <?php echo ($status=='cancel')?'checked':'';?> />
									&nbsp;Entry Cancelled</label>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<h4>
										<small>Waiver</small>
									</h4>
									<div class="error"><?php echo $waiverError ?></div>
									<label>
										<input type="checkbox" name="waiver" <?php echo ($waiver=="Y")?'checked': ''?> value="Y" />
										&nbsp;Waiver
									</label>
								</div>
								<div class="large-4 medium-6 small-12 large-offset-2 columns">
										<input class="button round small" type="submit" name="submitButton" value="Update Details" 
											onclick="return confirm('Are you sure that you want to change these details?')">
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Notes</legend>
							<div class="row">
								<div class="small-12 columns">
									<textarea name="comments" cols="64" rows="4" wrap="soft"
										maxlength="1024"><?php echo $comments; ?></textarea>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</form>
<?php else : ?>
      <p>You are not authorized to access this page.</p>
			<p>Please return to the list of entrants.</p>
			<p>
				<a class="button" href="includes/logout.php?id=<?php echo $_SESSION['eventId'];?>">Entrants</a>
			</p>
<?php endif; ?>
    </div>
		<hr />
	</div>
<?php include 'footer.php'?>
</body>
</html>
