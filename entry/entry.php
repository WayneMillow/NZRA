<?php
include_once 'includes/functions.php';
sec_session_start();
// Reset the session variable if we come to this page with the query string ?eventId=
if (isset($_GET['eventId'])) {
	$_SESSION['eventId'] = $_GET['eventId'];
}
if (find_event($mysqli) == false){
	header('location:no_event_error.php');
	exit();
}
if (event_open($_SESSION['eventId'], $mysqli) == false){
	header('location:closed_error.php');
	exit();
}
$monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
// Set up variables to hold the form fields
$event_id = $_SESSION['eventId'];
$event_date = get_event_date($event_id, $mysqli);
$numberOfEntrant = 5;
$numberOfExtra = getNumberOfExtra($event_id, $mysqli);
$use_country = 'N';
$use_date_of_birth = 'N';
if ($stmt = $mysqli->prepare("SELECT use_country, use_date_of_birth FROM event WHERE event_id = ?")) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($use_country, $use_date_of_birth);
	$stmt->fetch();
}
$team_name = '';
$gender = '';
$age_group = '';
$event = '';
$entrant_count = 2;
$first_name = array_fill(0, $numberOfEntrant, '');
$last_name = array_fill(0, $numberOfEntrant, '');
$street = array_fill(0, $numberOfEntrant, '');
$town = array_fill(0, $numberOfEntrant, '');
$country = array_fill(0, $numberOfEntrant, '');
$postcode = array_fill(0, $numberOfEntrant, '');
$email = array_fill(0, $numberOfEntrant, '');
$phone = array_fill(0, $numberOfEntrant, '');
$entry_fee = array_fill(0, $numberOfEntrant, '');
$dob_yyyy = array_fill(0, $numberOfEntrant, 0);
$dob_mm = array_fill(0, $numberOfEntrant, 0);
$dob_dd = array_fill(0, $numberOfEntrant, 0);
$age_at_event = array_fill(0, $numberOfEntrant, 0);
$extraCode = array_fill(0, $numberOfExtra, '');
$extraValue = array_fill(0, $numberOfExtra, 0);
$extraQuantity = array_fill(0, $numberOfExtra, 0);
$payment_value = 0;
$payment_type = '';
$comments = '';
$waiver = '';
$lower_age = 0;
$upper_age = 0;
// Field Errors
$team_nameError = '';
$genderError = '';
$age_groupError = '';
$eventError = '';
$first_nameError = array_fill(0, $numberOfEntrant, '');
$last_nameError = array_fill(0, $numberOfEntrant, '');
$streetError = array_fill(0, $numberOfEntrant, '');
$townError = array_fill(0, $numberOfEntrant, '');
$countryError = array_fill(0, $numberOfEntrant, '');
$postcodeError = array_fill(0, $numberOfEntrant, '');
$emailError = array_fill(0, $numberOfEntrant, '');
$phoneError = array_fill(0, $numberOfEntrant, '');
$entry_feeError = array_fill(0, $numberOfEntrant, '');
$age_at_eventError = array_fill(0, $numberOfEntrant, '');
$extraError = array_fill(0, $numberOfExtra, '');
$payment_typeError = '';
$commentsError = '';
$waiverError = '';
$sqlError = '';
$stripeError = '';
$paystationError = '';
// Generic page error
$pageError = FALSE;
// If we come back to this page after setting the session variables use them
if (isset($_SESSION['team_name'])){
	$team_name = $_SESSION['team_name'];
}
if (isset($_SESSION['gender'])) {
	$gender = $_SESSION['gender'];
}
if (isset($_SESSION['age_group'])) {
	$age_group = $_SESSION['age_group'];
}
if (isset($_SESSION['event'])) {
	$event = $_SESSION['event'];
}
if (isset($_SESSION['entrant_count'])) {
	$entrant_count = $_SESSION['entrant_count'];
}
if (isset($_SESSION['first_name'])) {
	$first_name = $_SESSION['first_name'];
}
if (isset($_SESSION['last_name'])) {
	$last_name = $_SESSION['last_name'];
}
if (isset($_SESSION['street'])) {
	$street = $_SESSION['street'];
}
if (isset($_SESSION['town'])) {
	$town = $_SESSION['town'];
}
if ($use_country === 'Y' && isset($_SESSION['country'])) {
	$country = $_SESSION['country'];
}
if (isset($_SESSION['postcode'])) {
	$postcode = $_SESSION['postcode'];
}
if (isset($_SESSION['email'])) {
	$email = $_SESSION['email'];
}
if (isset($_SESSION['phone'])) {
	$phone = $_SESSION['phone'];
}
if (isset($_SESSION['entry_fee'])) {
	$entry_fee = $_SESSION['entry_fee'];
}
if ($use_date_of_birth === 'Y' && isset($_SESSION['dob_yyyy'])) {
	$dob_yyyy = $_SESSION['dob_yyyy'];
}
if ($use_date_of_birth === 'Y' && isset($_SESSION['dob_mm'])) {
	$dob_mm = $_SESSION['dob_mm'];
}
if ($use_date_of_birth === 'Y' && isset($_SESSION['dob_dd'])) {
	$dob_dd = $_SESSION['dob_dd'];
}
if ($use_date_of_birth === 'Y' && isset($_SESSION['age_at_event'])) {
	$age_at_event = $_SESSION['age_at_event'];
}
if (isset($_SESSION['extraCode'])) {
	$extraCode = $_SESSION['extraCode'];
}
if (isset($_SESSION['extraValue'])) {
	$extraValue = $_SESSION['extraValue'];
}
if (isset($_SESSION['extraQuantity'])) {
	$extraQuantity = $_SESSION['extraQuantity'];
}
if (isset($_SESSION['payment_value'])) {
	$payment_value = $_SESSION['payment_value'];
}
if (isset($_SESSION['payment_type'])) {
	$payment_type = $_SESSION['payment_type'];
}
if (isset($_SESSION['comments'])) {
	$comments = $_SESSION['comments'];
}
if (isset($_SESSION['waiver'])) {
	$waiver = $_SESSION['waiver'];
}
// Check for wait list - Do this before we add anything
$max_entrants = 0;
$cur_entrants = 0;
$sql = "SELECT max_entrants, count(*) FROM event JOIN entrant ON entrant.event_id = event.event_id"
	. " JOIN entry ON entry.entry_id = entrant.entry_id AND entry.status != 'cancel'"
	. " WHERE event.event_id = ? group by max_entrants";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($max_entrants, $cur_entrants);
	if ($stmt->num_rows > 0) {
		$stmt->fetch();
	}
}
// If this form submitted the data then validate it
if (!empty($_POST['submitButton'])) {
	// If the user wants to go back to
	if ($_POST ['submitButton'] == "Cancel") {
		session_destroy();
		header ( "Location: entry.php?eventId=" . $event_id );
		exit();
	}
	$team_name = strip_tags(trim($_POST['team_name']));
	if (empty($team_name) || strlen($team_name) < 1 || strlen($team_name) > 32){
		$team_nameError = 'Please choose a Team Name between 1 and 32 characters';
		$pageError = TRUE;
	}
	if (empty($_POST['gender'])) {
		$genderError = 'Please select a Gender';
		$pageError = TRUE;
	} else {
		$gender = $_POST['gender'];
	}
	if (empty($_POST['age_group'])) {
		$age_groupError = 'Please select an Age Group';
		$pageError = TRUE;
		$lower_age = 0;
		$upper_age = 0;
	} else {
		$age_group = $_POST['age_group'];
		// Get the age limits for the group
		$sql="SELECT lower_age, upper_age from age_group where event_id= ? and code = ?";
		if ($stmt = $mysqli->prepare ($sql)) {
			$stmt->bind_param('is', $event_id, $age_group);
			$stmt->execute ();
			$stmt->store_result();
			$stmt->bind_result($lower_age, $upper_age);
			$stmt->fetch();
		}
	}
	if (empty($_POST['event'])) {
		$eventError = 'Please select an Event';
		$pageError = TRUE;
	} else {
		$event = $_POST['event'];
	}
	if ($age_group == 'YJ' && $event == '6 hour') {
		$eventError = 'A junior team may not enter the 6 hour event';
		$pageError = TRUE;
	} 
	if (str_starts_with($age_group, 'Y') && ($event == '24 hour' || $event == '15 hour')) {
	    $eventError = 'A school team may not enter the longer events';
	    $pageError = TRUE;
	}
	if ($gender == 'F' && $event != 'Family') {
	    $eventError = 'Please select the Family event for a Family entry';
	    $pageError = TRUE;
	}
	if ($gender != 'F' && $event == 'Family') {
	    $eventError = 'You may not select the Family event if you are not a family';
	    $pageError = TRUE;
	}
	$entrant_count = $_POST['entrant_count'];
	// These will be arrays for the entrant_count
	$first_name = f_clean($_POST['first_name']);
	$last_name = f_clean($_POST['last_name']);
	$street = f_clean($_POST['street']);
	$town = f_clean($_POST['town']);
	if ($use_country === 'Y') {
		$country = f_clean($_POST['country']);
	}
	$postcode = f_clean($_POST['postcode']);
	$email = f_clean($_POST['email']);
	$phone = f_clean($_POST['phone']);
	$entry_fee = $_POST['entry_fee'];
	if ($use_date_of_birth === 'Y') {
		$dob_yyyy = $_POST['dob_yyyy'];
		$dob_mm = $_POST['dob_mm'];
		$dob_dd = $_POST['dob_dd'];
		$age_at_event = $_POST['age_at_event'];
	}
	for ($idx = 0; $idx < $entrant_count; $idx++) {
		$first_name[$idx] = strip_tags(trim($first_name[$idx]));
		if (empty($first_name[$idx]) || strlen($first_name[$idx]) < 2 || strlen($first_name[$idx]) > 32) {
			$first_nameError[$idx] = "Please enter the full name. An initial is not sufficient. A maximum of 32 characters.";
			$pageError = TRUE;
		}
		$last_name[$idx] = strip_tags(trim($last_name[$idx]));
		if (empty($last_name[$idx]) || strlen($last_name[$idx]) < 2 || strlen($last_name[$idx]) > 32) {
			$last_nameError[$idx] = "Please enter a valid last name. A maximum of 32 characters.";
			$pageError = TRUE;
		}
		$street[$idx] = strip_tags(trim($street[$idx]));
		if (empty($street[$idx])) {
		    $street[$idx] = "Not provided";
		}
		if (strlen($street[$idx] ) < 2 || strlen($street[$idx]) > 32) {
			$streetError[$idx] = "Please enter a valid street address. A maximum of 32 characters.";
			$pageError = TRUE;
		}
		$town[$idx] = strip_tags(trim($town[$idx]));
		if (empty($town[$idx])) {
		    $town[$idx] = "Not provided";
		}
		if (strlen($town[$idx]) < 2 || strlen($town[$idx]) > 32) {
			$townError[$idx] = "Please enter a valid town or city. A maximum of 32 characters.";
			$pageError = TRUE;
		}
		$country[$idx] = strip_tags(trim($country[$idx]));
		if (empty($country[$idx])) {
			$country[$idx] = "New Zealand";
		}
		if (strlen($country[$idx]) < 2 || strlen($country[$idx]) > 32) {
			$countryError[$idx] = "Please enter a valid country. A maximum of 32 characters.";
			$pageError = TRUE;
		}
		$postcode[$idx] = strip_tags(trim($postcode[$idx]));
		if (empty($postcode[$idx])) {
		    $postcode[$idx] = "0000";
		}
		// Don't worry about a postcode outside of NZ
		if ($country[$idx] == "New Zealand" || $country[$idx] == "NZ") {
			if (empty($postcode[$idx])) {
				$postcodeError[$idx] = "For NZ please enter a postcode. Check the NZ post site";
				$pageError = TRUE;
			} elseif (!preg_match( "/^([0-9]){4}?$/", $postcode[$idx])) {
				$postcodeError[$idx] = "Please enter four digits";
				$pageError = TRUE;
			}
		} else {
			$postcode[$idx] = "";
		}
		$email[$idx] = strip_tags(trim($email[$idx]));
		if (empty($email[$idx])) {
			$emailError[$idx] = "Please enter an email address";
			$pageError = TRUE;
		} elseif (!filter_var($email[$idx], FILTER_VALIDATE_EMAIL)) {
			$emailError[$idx] = "Please enter a valid email address";
			$pageError = TRUE;
		}
		$phone[$idx] = strip_tags(trim($phone[$idx]));
		if (empty($phone[$idx])) {
				$phoneError[$idx] = "Please enter a valid phone number. Use + followed by the International Access Code outside of NZ.";
				$pageError = TRUE;
		} else {
			$phone[$idx] = preg_replace('/[^\d\(\)\+]/', '', $phone[$idx]);
			if (empty($phone[$idx])) {
				$phoneError[$idx] = "Please enter a valid phone number. Use + followed by the International Access Code outside of NZ.";
				$pageError = TRUE;
			} elseif (preg_match("/(^\++\d{8,15})/", $phone[$idx])) {
					// OK leading +
			} elseif (!preg_match("/(^\(?(03|04|06|07|09)\)?\d{7,8}$)|(^(021|022|025|027|028|029)\d{6,8}$)/", $phone[$idx])) {
				$phoneError[$idx] = "Please enter a valid NZ phone number including STD code";
				$pageError = TRUE;
			}
		}
		// Has the entry fee been entered
		// Family 1.5 hour is different. Others have to have one
		if ($gender == 'F' && ($event == '1.5 hour' || $event == '3 hour')) {
			if ($idx == 0 && ($entry_fee[$idx] == "")) {
				$entry_feeError[$idx] = 'Please select the correct entry fee';
				$pageError = TRUE;
			}
		} else if ($event == 'Family') {
		    if ($idx == 0 && ($entry_fee[$idx] == "")) {
		        $entry_feeError[$idx] = 'Please select the correct entry fee';
		        $pageError = TRUE;
		    }
		} else {
			if ($entry_fee[$idx] == '') {
			    $entry_feeError[$idx] = 'Please select the correct entry fee';
				$pageError = TRUE;
			} 
		}
		// Calculate age at event date
		if ($use_date_of_birth === 'Y') {
			$dob = strtotime($dob_yyyy[$idx].'-'.$dob_mm[$idx].'-'.$dob_dd[$idx]);
			if ($dob) {
				if ($lower_age > 0 && $upper_age > 0) {
					if ($age_group == 'J' && ($age_at_event[$idx] < $lower_age || $age_at_event[$idx] > $upper_age)) {
						$age_at_eventError[$idx] = 'Entrant must be between ' . $lower_age . ' and ' . $upper_age . ' to be in Junior grade.';
						$pageError = TRUE;
					} elseif ($age_group != 'O' && $age_at_event[$idx] < $lower_age) {
						$age_at_eventError[$idx] = 'Entrant must be older than ' . $lower_age . ' to be in this grade.';
						$pageError = TRUE;
					}
				}
				// Check entry fee
				if ($event_id == 26 && $age_at_event[$idx] > 22 && $entry_fee[$idx] == '0') {
				    $entry_feeError[$idx] = 'The competitor must be a junior less than 23 for this entry fee.';
				    $pageError = TRUE;
				}
			} else {
				$age_at_eventError[$idx] = 'Please select a valid date of birth';
				$pageError = TRUE;
			}
		}
	}
	if (!empty($_POST['extraCode'])) {
		$extraCode = f_clean($_POST['extraCode']);
		$extraValue = f_clean($_POST['extraValue']);
		$extraQuantity = f_clean($_POST['extraQuantity']);
		for ($idx = 0; $idx < $numberOfExtra; $idx++) {
			if ($extraQuantity[$idx] < 0 || $extraQuantity[$idx] > 10) {
				$extraError[$idx] = 'Please key a number between 0 and 10';
				$pageError = TRUE;
				}
		}
	}
	if (!empty($_POST['payment_value'])) {
		$payment_value = $_POST['payment_value'];
		if (empty($_POST['payment_type'])) {
			$payment_typeError = 'Please select a Payment Type';
			$pageError = TRUE;
		} else {
			$payment_type = $_POST['payment_type'];
		}
	}
	if (!empty($_POST['comments'])) {
		$comments = htmlentities(trim($_POST ['comments']));
	}
	if (empty($_POST['waiver'])) {
		$waiverError = 'You must accept the terms and conditions in order to enter this event';
		$pageError = TRUE;
	} else {
		$waiver = $_POST['waiver'];
	}
	// If no errors go to the page submitted
	if ($pageError === FALSE) {
		$_SESSION['team_name'] = $team_name;
		$_SESSION['gender'] = $gender;
		$_SESSION['age_group'] = $age_group;
		$_SESSION['event'] = $event;
		$_SESSION['entrant_count'] = $entrant_count;
		$_SESSION['first_name'] = $first_name;
		$_SESSION['last_name'] = $last_name;
		$_SESSION['street'] = $street;
		$_SESSION['town'] = $town;
		$_SESSION['country'] = $country;
		$_SESSION['postcode'] = $postcode;
		$_SESSION['email'] = $email;
		$_SESSION['phone'] = $phone;
		$_SESSION['entry_fee'] = $entry_fee;
		$_SESSION['dob_yyyy'] = $dob_yyyy;
		$_SESSION['dob_mm'] = $dob_mm;
		$_SESSION['dob_dd'] = $dob_dd;
		$_SESSION['age_at_event'] = $age_at_event;
		$_SESSION['extraCode'] = $extraCode;
		$_SESSION['extraValue'] = $extraValue;
		$_SESSION['extraQuantity'] = $extraQuantity;
		$_SESSION['payment_value'] = $payment_value;
		$_SESSION['payment_type'] = $payment_type;
		$_SESSION['comments'] = $comments;
		$_SESSION['waiver'] = $waiver;
		// Recalculate payment value
		$payment_value = 0;
		for ($idx = 0; $idx < $entrant_count; $idx++) {
			$payment_value += $entry_fee[$idx];
		}
		for ($idx = 0; $idx < $numberOfExtra; $idx++) {
			$payment_value += $extraQuantity[$idx] * $extraValue[$idx];
		}
		// Do the post
		// Everything should be valid
		// Get the next team number for this event
		if (!($stmt = $mysqli->prepare("UPDATE sequence SET entry_number = last_insert_id(entry_number + 1) WHERE event_id = ? AND sub_event_code = ?" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (!($stmt->bind_param ( 'is', $event_id, $event))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (!($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		$team_number = mysqli_insert_id($mysqli);
		$stmt->close();
		if ($stmt = $mysqli->prepare("SELECT entry_number FROM sequence WHERE event_id = ? AND sub_event_code = ?")) {
		    $stmt->bind_param('ii', $event_id, $event);
		    $stmt->execute();
		    $stmt->store_result();
		    $stmt->bind_result($team_number);
		    $stmt->fetch();
		}
		// Add the entry
		if (!($stmt = $mysqli->prepare("INSERT INTO entry(team_number, team_name, gender_code, age_group_code, sub_event_code, payment_type, payment_value, waiver, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (! ($stmt->bind_param ( 'isssssiss', $team_number, $team_name, $gender, $age_group, $event, $payment_type, $payment_value, $waiver, $comments ))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (! ($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		$entry_id = mysqli_insert_id ( $mysqli );
		$stmt->close();
		// Members
		if (!($stmt1 = $mysqli->prepare("INSERT INTO member (first_name, last_name, street, town, country, postcode, email, phone, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (!($stmt2 = $mysqli->prepare("INSERT INTO entrant (event_id, entry_id, member_id, entry_fee) VALUES (?, ?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		// Loop for each member
		for ($i = 0; $i < $entrant_count; $i++) {
			$dob = $dob_yyyy[$i].'-'.$dob_mm[$i].'-'.$dob_dd[$i];
			if ($dob_yyyy[$i] == 0 || $dob_mm[$i] ==0 || $dob_dd[$i] == 0 || !strtotime($dob)) {
				$dob = '1900-01-01';
			}
			if (!($stmt1->bind_param('sssssssss', $first_name[$i], $last_name[$i], $street[$i], $town[$i], $country[$i], $postcode[$i], $email[$i], $phone[$i], $dob))) {
				$sqlError .= "Binding parameters failed: (" . $stmt1->errno . ") " . $stmt1->error;
				$pageError = TRUE;
			}
			if (!($stmt1->execute ())) {
				$sqlError .= "Execute failed: (" . $stmt1->errno . ") " . $stmt1->error;
				$pageError = TRUE;
			}
			$member_id = mysqli_insert_id($mysqli);
			if (!($stmt2->bind_param ( 'iiii', $event_id, $entry_id, $member_id, $entry_fee[$i] ))) {
				$sqlError .= "Binding parameters failed: (" . $stmt2->errno . ") " . $stmt2->error;
				$pageError = TRUE;
			}
			if (! ($stmt2->execute ())) {
				$sqlError .= "Execute failed: (" . $stmt2->errno . ") " . $stmt2->error;
				$pageError = TRUE;
			}
			if ($pageError === TRUE) {
				break;
			}
		}
		$stmt1->close();
		$stmt2->close();
		// Extras
		if (!($stmt = $mysqli->prepare("INSERT INTO entry_extra(entry_id, code, quantity) VALUES (?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		// Loop for each extra
		for ($i = 0; $i < $numberOfExtra; $i++) {
			if ($extraQuantity[$i] > 0) {
				if (!($stmt->bind_param('isi', $entry_id, $extraCode[$i], $extraQuantity[$i] ))) {
					$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				}
				if (!($stmt->execute ())) {
					$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				}
			}
		}
		$stmt->close();
	}
	if ($pageError === FALSE) {
		$team_number = get_team_number($entry_id, $mysqli);
		$description = get_description($event_id, $mysqli);
		$to = $email[0];
		$subject = $description. " Entry - Team " . $team_number;
		// Check for wait list
		if ($cur_entrants >= $max_entrants && $cur_entrants != 0) {
			// Update the status
				if (!($stmt = $mysqli->prepare("UPDATE entry SET status = 'waitList' WHERE entry_id = ?" ))) {
					$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					$pageError = TRUE;
				}
				if (!($stmt->bind_param ('i', $entry_id))) {
					$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					$pageError = TRUE;
				}
				if (!($stmt->execute ())) {
					$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
					$pageError = TRUE;
				}
				$stmt->close();
			// Send an email
			// Email entry confirmation
			// Maximum length 70 characters per line
			// 1234567890123456789012345678901234567890123456789012345678901234567890
			$message = 'You have been placed on the wait list' . "\r\n"
			. 'for the ' . $description . '.' . "\r\n"
			. 'There are already ' . $cur_entrants . ' entrants' . 'and the maximum is ' . $max_entrants . "\r\n"
			. 'however we expect some withdrawals and will contact you' . "\r\n"
			. 'if a place becomes available' . "\r\n"
			. 'We have recorded you as team number ' . $team_number . '.' . "\r\n"
			. 'Please keep a note of this and include it in any communication.' . "\r\n";
		} else {
			// Email entry confirmation
			// Maximum length 70 characters per line
			// 1234567890123456789012345678901234567890123456789012345678901234567890
			$message = 'Congratulations.' . "\r\n"
			. 'You have entered the ' . $description . '.' . "\r\n"
			. 'We look forward to seeing you there and hope that you enjoy it.' . "\r\n"
			. "\r\n"
			. 'You are team number ' . $team_number . '.' . "\r\n"
			. 'Please keep a note of this and include it in any communication.' . "\r\n"
			. 'Your entry will be displayed immediately in the list of entrants ' . "\r\n"
			. 'however it will not be confirmed until we have received payment.' . "\r\n";
			if ($payment_type == 'direct credit') {
				$message .= "\r\n"
				. 'Please transfer payment of $' . $payment_value . "\r\n";
				$sql="SELECT trim(account_name), trim(account_number)" .
					" from event inner join bank_account on bank_account.account_id = event.account_id" .
					" where event_id= ?";
				if ($stmt = $mysqli->prepare ($sql)) {
					$stmt->bind_param ( 'i', $event_id );
					$stmt->execute ();
					$stmt->store_result();
					$stmt->bind_result($accountName, $accountNumber);
					$stmt->fetch();
					$message .= 'to '.$accountName." Account No.\r\n";
					$message .= $accountNumber. "\r\n";
					$stmt->close();
				}
				          // 1234567890123456789012345678901234567890123456789012345678901234567890
				$message .= 'using your team number ' . $team_number . ' as REFERENCE, surname as CODE' . "\r\n"
				          . 'and this event name as PARTICULARS to identify your payment.';
			} elseif ($payment_type == 'cheque') {
				$message .= "\r\n" 
				. 'Please send your cheque payment of $' . $payment_value . "\r\n";
				$sql="SELECT trim(description), trim(first_name), trim(last_name), trim(line_1), trim(line_2), trim(line_3), postcode" .
					" from event inner join contact on contact_id = entry_contact_id" .
					" join cheque_address on event.address_id = cheque_address.address_id" .
					" where event_id= ?";
				if ($stmt = $mysqli->prepare ($sql)) {
					$stmt->bind_param ( 'i', $event_id );
					$stmt->execute ();
					$stmt->store_result();
					$stmt->bind_result($description, $firstName, $lastName, $line1, $line2, $line3, $postCode);
					$stmt->fetch();
					$message .= 'to: '.$description."\r\n";
					$message .=  'c/o '. $firstName.' '. $lastName."\r\n";
					$message .=  $line1."\r\n";
					$message .=  $line2."\r\n";
					$message .=  $line3.' '.$postCode."\r\n";
					$stmt->close();
				}
			}
		}
	}
	if ($pageError === FALSE && $payment_type == 'credit card') {
	    $amount		= $payment_value * 100 ; // Needs to be passed as cents
	    include "stripe/stripe_checkout.php";
	    $stripeError .= "\r\n<strong>This is not enabled</strong>"; 
	    $pageError = TRUE;
	}
	// Paystation is no longer used but this code is kept for reference
	if ($pageError === FALSE && $payment_type == 'Paystation credit card') {
	    $amount		= $payment_value * 100 ; // Needs to be passed as cents
		$merchantRef = urlencode('NZRA'); //merchant reference is optional, but is a great way to tie a transaction in with a customer (this is displayed in Paystation Administration when looking at transaction details). Max length is 64 char. Make sure you use it!
		$hmacParameters = '';
		$returnUrl = 'http://'.RETURN_HOST.'/entry/paystation_return.php';
		$merchantSession = urlencode(time().'-'.makePaystationSessionID(8,8));
		
		$paystationParameters = "paystation=_empty&pstn_pi=".PAYSTATIONID."&pstn_gi=".GATEWAYID."&pstn_ms=".$merchantSession."&pstn_mr=".urlencode($merchantRef)."&pstn_am=".$amount."&pstn_nr=t";
		if 	(TESTMODE == 'true'){
			$paystationParameters = $paystationParameters."&pstn_tm=t";
		}
		/* Team number and name to customer details */
		$paystationParameters = $paystationParameters."&pstn_mc=".urlencode($team_number.'-'.$team_name);
		/* Event Name to order details */
		$paystationParameters = $paystationParameters."&pstn_mo=".urlencode(get_description($event_id, $mysqli));
		/* Set dynamic return URL */
		$paystationParameters = $paystationParameters."&pstn_du=".urlencode($returnUrl);
		/* If hmacMode is true generate hmacParameters */
		if(HMACMODE){
			$hmacParameters = generateHMACParams($paystationParameters,HMAC_SECURITY_CODE);
		}
		$result = doPostRequest(PAYSTATIONURL, $paystationParameters, HMACMODE, $hmacParameters);
		/*Now we grab the details from the request we have just made - NOTE: it is best to store everything you can about a transaction - if you ever have to debug you will understand!
		 */
		$xmlData = new SimpleXMLElement($result);
		$digitalOrder = $xmlData->DigitalOrder; // The URL that we re-direct the customer too.
		$transactionID =  $xmlData->PaystationTransactionID;  //The transaction ID Paystation has just created.
		$paymentRequestTime =  $xmlData->PaymentRequestTime; // The time that the transaction was initiated
		$digitalOrderTime =  $xmlData->DigitalOrderTime;  //The time Paystation responds
		// Add the entry
		if (!($stmt = $mysqli->prepare("INSERT INTO entry_credit_card(transaction_id, entry_id, payment_value, payment_request_time, digital_order_time) VALUES (?, ?, ?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (! ($stmt->bind_param ( 'siiss', $transactionID, $entry_id, $payment_value, $paymentRequestTime, $digitalOrderTime ))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (! ($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		$stmt->close();
		// Redirect
		if ($digitalOrder) {
			header("Location:".$digitalOrder); //Successful initiation, so redirect to page given in digitalorder variable
			exit();
		} else {
			$paystationError .= "<pre>".htmlentities($result)."</pre>"; //no digitalorder variable, so initiation must have failed.  Print out xml packet for debugging purposes
			$pageError = TRUE;
		}
	}
	if ($pageError === FALSE) {
	  // Send an email to the captain
	  $headers = 'From: entries@rogaine.org.nz' . "\r\n"
	        . 'Reply-To: entries@rogaine.org.nz' . "\r\n"
	        . 'Bcc: wayne.millow@gmail.com' . "\r\n"
	        . 'X-Mailer: PHP/' . phpversion ();
      mail($to, $subject, $message, $headers);
	  //And go to look at the entrants
	  session_destroy ();
	  header ( "Location: entrants.php?eventId=" . $event_id );
	  exit();
	}
}
?>
<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
<?php include 'head.php'?>
<script src="https://js.stripe.com/clover/stripe.js"></script>
<body>
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
			//if (isset($event_phone) && strlen(trim($event_phone)) > 0) {
			//	echo 'Phone: '.$event_phone;
			//}
			echo '</p></div>';
		
			echo '<div class="large-4 medium-6 small-12 columns">';
			echo '<h3><small>Entry Enquiries to</small></h3>';
			echo '<p>'.$entry_name.'<br>';
			echo 'Email: <a href="mailto:'.$entry_email.'">'.$entry_email.'</a><br>';
			//if (isset($entry_phone) && strlen(trim($entry_phone)) > 0) {
			//	echo 'Phone: '.$entry_phone;
			//}
			echo '</p></div>';
		}
		$stmt->close();
	}
	$sql = 
	  "SELECT sub_event.description, sub_event.senior_price, sub_event.junior_price" .
		" FROM event" .
		" JOIN sub_event ON sub_event.event_id = event.event_id" .
		" WHERE event.event_id = ?" .
		" ORDER BY sub_event.code DESC";
	if ($stmt = $mysqli->prepare ($sql)) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($description, $senior_price, $junior_price);
		echo '<div class="large-4 medium-6 small-12 columns">';
		echo '<h3><small>Entry Fees</small></h3>';
		if ($event_id == -36) { 
		    echo '<div>Earlybird until 30th Sept</div>';
		}
		    echo '<p>';
		while ($stmt->fetch()){
				echo $description.' - $'. $senior_price;
				if (stripos($description, "family") === FALSE) echo ' per person';
				if ($junior_price != 0) echo ' (Junior-$'. $junior_price. ')';
				echo '<br>';
		}
		$stmt->close();
		if ($event_id == 31) {
		    echo '3hr Family: $80';
		    echo ' - ';
		    echo '1.5hr Family: $40';
		}
		echo '</p></div>';
	}
?>
		<div class="large-12 columns">
			<strong>Entries close <?php echo get_entry_close($event_id, $mysqli);?></strong>.
<?php if ($max_entrants > 0 && $max_entrants < 4096) :?>			
			Numbers will be limited to a total of <?php echo $max_entrants; ?>.
<?php endif; ?>			
			For current numbers see the <a target="_blank" href="entrants.php?eventId=<?php echo $event_id;?>">List of Entrants</a>.
<?php if ($event_id != 36) : ?>
			Late entries at the discretion of the organizer with a potential late entry fee.
<?php endif; ?>
		</div>
	</div>
	<hr>
</div>
<div id="main" class="row">
		<div class="small-12 columns">
			<form id="headerForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<input id="eventId" type="hidden" value="<?php echo $event_id;?>" />
				<input id="eventDate" type="hidden" value="<?php echo $event_date;?>" />
				<input id="numberOfExtra" type="hidden" value="<?php echo $numberOfExtra;?>" />
<?php if ($pageError === TRUE) : ?>
				<div class="row">
					<div class="error">
						Errors exist! Please check the form for the details, correct them and press enter again.
						<?php if ($sqlError) echo $sqlError;?>
						<?php if ($stripeError) echo $stripeError;?>
						<?php if ($paystationError) echo $paystationError;?>
					</div>	
				</div>			
<?php endif; ?>				
				<div class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Category<br><em>Junior: all team members must be within the specified age range on the date of the event.<br>Veteran +: based on the youngest team member on the date of the event.</em></legend>
							<div class="row">
								<div class="small-12 columns">
									<label>Team Name<input type="text" name="team_name" maxlength="32" value="<?php echo $team_name;?>"></label>
									<div class="error"><?php echo $team_nameError; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-3 medium-6 small-12 columns">
									<h4><small>Type</small></h4>
									<div class="error"><?php echo $genderError; ?></div>
									<div id="genders">
<?php
$sql = "SELECT code, description FROM gender WHERE event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$count = $stmt->num_rows;
	$stmt->bind_result($code, $description);
	while ($stmt->fetch()) {
	    if ($count == 1) {
	      echo '<input type="hidden" name="gender" value="'.$code.'">';  
	    } else {
    		echo '<label><input type="radio" name="gender" value="'.$code.'" onChange="checkForFamily();checkForAdventure();setEntryFees();"';
    		echo ($gender==$code) ? ' checked' : '';
    		echo ' />&nbsp;' .$description.'</label>';
	    }
	}
	$stmt->close();
}
?>								
									</div>
								</div>
								<div class="large-3 medium-6 small-12 columns">
									<h4><small>Age Group</small></h4>
									<div class="error"><?php echo $age_groupError; ?></div>
								    <div id="ageGroups">
<?php 
$sql = "SELECT code, description, lower_age, upper_age FROM age_group WHERE event_id = ? ORDER BY lower_age";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$count = $stmt->num_rows;
	$stmt->bind_result($code, $description, $lower, $upper);
	while ($stmt->fetch()) {
	    if ($count == 1) {
	        echo '<input type="hidden" name="age_group" value="'.$code.'">';
	    } else {
	        echo '<label><input type="radio" name="age_group" value="'.$code.'" onChange="checkForSchool();"';
    		echo ($age_group==$code) ? ' checked' : '';
    		echo ' />&nbsp;' .$description; 
    		if ($code!="O" && substr($code, 0, 1) != "Y") {echo ' ('.$lower.'-'.$upper.')';};
    		echo '</label>';
	    }
	}
	$stmt->close();
}
?>									
                                    </div>
								    <div id="familyText" class="hide">A family should be one or two adults with one to four children to a maximum of five.</div>
								</div>
								<div class="large-3 medium-6 small-12 columns">
									<h4><small>Event</small></h4>
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
		echo ('09am'==$code) ? ' disabled': '';
		echo ('10am'==$code) ? ' disabled': '';
		echo ' onclick="setEntryFees();"/>&nbsp;' .$description.'</label>';
	}
	$stmt->close();
}
?>					
								</div>
								<div class="large-3 medium-6 small-12 columns">
<?php if ($event_id == 36) : ?>
									<p>School teams will all be registered in the miXed grade. (ie. no gender distinction)</p>
									<p>They may only enter the 3 or 6 hour events.</p>
<?php endif; ?>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="row">
					<div class="small-2 columns">
						&nbsp;
					</div>
					<div class="small-3 columns">
					  <label>Number of Entrants
						  <select id="entrant_count" name="entrant_count" onchange="displayEntrants()">
						    <option value="2" <?php echo ($entrant_count=='2')?' selected':''?>>2</option>
						    <option value="3" <?php echo ($entrant_count=='3')?' selected':''?>>3</option>
						    <option value="4" <?php echo ($entrant_count=='4')?' selected':''?>>4</option>
						    <option value="5" <?php echo ($entrant_count=='5')?' selected':''?>>5</option>
						  </select>
						</label>
					</div>
					<div class="small-7 columns">
						In case we need to contact you it is <strong>IMPORTANT</strong> that you supply sufficient contact details for <strong>EVERY</strong> member of your team.  
					</div>
				</div>
				
				<div id="entrant1" class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Contact</legend>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>First Name<input name="first_name[0]" type="text" maxlength="32" value="<?php echo $first_name[0];?>" /></label>
									<div class="error"><?php echo $first_nameError[0]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Last Name<input name="last_name[0]" type="text" maxlength="32" value="<?php echo $last_name[0];?>" /></label>
									<div class="error"><?php echo $last_nameError[0]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($use_country === 'Y') : ?>
									<label>Country<input name="country[0]" type="text" maxlength="32" value="<?php echo $country[0];?>" /></label>
<?php endif; ?>								
									<div class="error"><?php echo $countryError[0]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Street (optional)<input name="street[0]" type="text" maxlength="32" value="<?php echo $street[0];?>" /></label>
									<div class="error"><?php echo $streetError[0]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Town / City (optional)<input name="town[0]" type="text" maxlength="32" value="<?php echo $town[0];?>" /></label>
									<div class="error"><?php echo $townError[0]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Postcode (optional)<input name="postcode[0]" type="text" maxlength="5" value="<?php echo $postcode[0];?>" /></label>
									<div class="error"><?php echo $postcodeError[0]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Email<input name="email[0]" type="text" maxlength="64" value="<?php echo $email[0];?>" /></label>
									<div class="error"><?php echo $emailError[0]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Phone<input name="phone[0]" type="text" maxlength="16" value="<?php echo $phone[0];?>" /></label>
									<div class="error"><?php echo $phoneError[0]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Entry Fee
									  <select id="entry_fee[0]" name="entry_fee[0]" onchange="displayPaymentValue()">
<?php
if ($gender === 'F' && $event === '3 hour') {
	echo '<option value="">Please Select</option>';
	echo '<option value="80"';
	echo ($entry_fee[0]==80)?' selected':'';
	echo '>$80</option>';
} elseif ($gender === 'F' && $event === '1.5 hour') {
    echo '<option value="">Please Select</option>';
    echo '<option value="40"';
    echo ($entry_fee[0]==40)?' selected':'';
    echo '>$40</option>';
} elseif ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ? ORDER BY event_id, code DESC" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo ($entry_fee[0]==$senior_price)?' selected':'';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo ($entry_fee[0]==$junior_price)?' selected':'';
		echo '>$'.$junior_price.' Junior</option>';
	}	else {
		echo '<option value="">Please Select an Event</option>';
	}
}	else {
	echo '<option value="">Please Select an Event</option>';
}
?>										  
									  </select>
									</label>
									<div class="error"><?php echo $entry_feeError[0]; ?></div>
								</div>
							</div>
<?php if ($use_date_of_birth === 'Y') : ?>							
							<div id="dob[0]" class="row">
								<div class="large-2 medium-4 small-6 columns">
									<label for="dob_yyyy[0]" class="right inline">Birth Date</label>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_yyyy[0]" name="dob_yyyy[0]" onchange="OnMonthYearChange('dob', 0);">
										<option value="">Year</option>
<?php
$thisYear = date("Y");
for ($i = $thisYear-10; $i > $thisYear-90; $i--) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_yyyy[0]) {
		echo ' selected';
	}
	echo '>' . $i . '</option>';
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_mm[0]" name="dob_mm[0]" onchange="OnMonthYearChange('dob', 0);">
										<option value="">Month</option>
<?php
for ($i = 1; $i < 13; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_mm[0]) {
		echo ' selected';
	}
	echo '>' . $monthNames[$i-1] . '</option>';
}
?>					
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_dd[0]" name="dob_dd[0]" onchange="OnDayChange('dob', 0);">
										<option value="">Day</option>
<?php
if ($dob_yyyy[0] > 0 && $dob_mm[0]) {
	for ($i = 1; $i < getDaysInMonth($dob_yyyy[0], $dob_mm[0]) + 1; $i++) {
		echo '<option value="' . $i . '"';
		if ($i == $dob_dd[0]) {
			echo ' selected';
		}
		echo '>' . $i . '</option>';
	}
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<label for="age_at_event[0]" class="right inline">Age at event date</label>
									<div class="error"><?php echo $age_at_eventError[0]; ?></div>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<input id="age_at_event[0]" name="age_at_event[0]" readonly="readonly" value="<?php echo $age_at_event[0]?>" />
								</div>
							</div>
<?php endif; ?>							
						</fieldset>
					</div>
				</div>

				<div id="entrant2" class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Member two</legend>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>First Name<input name="first_name[1]" type="text" maxlength="32" value="<?php echo $first_name[1];?>" /></label>
									<div class="error"><?php echo $first_nameError[1]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Last Name<input name="last_name[1]" type="text" maxlength="32" value="<?php echo $last_name[1];?>" /></label>
									<div class="error"><?php echo $last_nameError[1]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($use_country === 'Y') : ?>
									<label>Country<input name="country[1]" type="text" maxlength="32" value="<?php echo $country[1];?>" /></label>
<?php endif; ?>								
									<div class="error"><?php echo $countryError[1]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Street (optional)<input name="street[1]" type="text" maxlength="32" value="<?php echo $street[1];?>" /></label>
									<div class="error"><?php echo $streetError[1]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Town / City (optional)<input name="town[1]" type="text" maxlength="32" value="<?php echo $town[1];?>" /></label>
									<div class="error"><?php echo $townError[1]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Postcode (optional)<input name="postcode[1]" type="text" maxlength="5" value="<?php echo $postcode[1];?>" /></label>
									<div class="error"><?php echo $postcodeError[1]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Email<input name="email[1]" type="text" maxlength="64" value="<?php echo $email[1];?>" /></label>
									<div class="error"><?php echo $emailError[1]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Phone<input name="phone[1]" type="text" maxlength="16" value="<?php echo $phone[1];?>" /></label>
									<div class="error"><?php echo $phoneError[1]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Entry Fee
									  <select id="entry_fee[1]"  name="entry_fee[1]" onchange="displayPaymentValue()">
<?php 
if ($gender === 'F' && ($event === '1.5 hour' || $event === '3 hour')) {
	echo '<option value="">Please Select</option>';
	echo '<option value="0"';
	echo ($entry_fee[1]==0)?' selected':'';
	echo '>$0</option>';
} elseif ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ? ORDER BY event_id, code DESC" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo ($entry_fee[1]==$senior_price)?' selected':'';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo ($entry_fee[1]==$junior_price)?' selected':'';
		echo '>$'.$junior_price.' Junior</option>';
	}	else {
		echo '<option value="">Please Select an Event</option>';
	}
}	else {
	echo '<option value="">Please Select an Event</option>';
}
?>										  
									  </select>
									</label>
									<div class="error"><?php echo $entry_feeError[1]; ?></div>
								</div>
							</div>
<?php if ($use_date_of_birth === 'Y') : ?>							
							<div id="dob[1]" class="row">
								<div class="large-2 medium-4 small-6 columns">
									<label for="dob_yyyy[1]" class="right inline">Birth Date</label>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_yyyy[1]" name="dob_yyyy[1]" onchange="OnMonthYearChange('dob', 1);">
										<option value="">Year</option>
<?php
$thisYear = date("Y");
for ($i = $thisYear-10; $i > $thisYear-90; $i--) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_yyyy[1]) {
		echo ' selected';
	}
	echo '>' . $i . '</option>';
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_mm[1]" name="dob_mm[1]" onchange="OnMonthYearChange('dob', 1);">
										<option value="">Month</option>
<?php
for ($i = 1; $i < 13; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_mm[1]) {
		echo ' selected';
	}
	echo '>' . $monthNames[$i-1] . '</option>';
}
?>					
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_dd[1]" name="dob_dd[1]" onchange="OnDayChange('dob', 1);">
										<option value="">Day</option>
<?php
if ($dob_yyyy[1] > 0 && $dob_mm[1]) {
	for ($i = 1; $i < getDaysInMonth($dob_yyyy[1], $dob_mm[1]) + 1; $i++) {
		echo '<option value="' . $i . '"';
		if ($i == $dob_dd[1]) {
			echo ' selected';
		}
		echo '>' . $i . '</option>';
	}
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<label for="age_at_event[1]" class="right inline">Age at event date</label>
									<div class="error"><?php echo $age_at_eventError[1]; ?></div>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<input id="age_at_event[1]" name="age_at_event[1]" readonly="readonly" value="<?php echo $age_at_event[1]?>" />
								</div>
							</div>
<?php endif; ?>							
						</fieldset>
					</div>
				</div>

				<div id="entrant3" class="row hide">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Member three</legend>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>First Name<input name="first_name[2]" type="text" maxlength="32" value="<?php echo $first_name[2];?>" /></label>
									<div class="error"><?php echo $first_nameError[2]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Last Name<input name="last_name[2]" type="text" maxlength="32" value="<?php echo $last_name[2];?>" /></label>
									<div class="error"><?php echo $last_nameError[2]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($use_country === 'Y') : ?>
									<label>Country<input name="country[2]" type="text" maxlength="32" value="<?php echo $country[2];?>" /></label>
<?php endif; ?>								
									<div class="error"><?php echo $countryError[2]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Street (optional)<input name="street[2]" type="text" maxlength="32" value="<?php echo $street[2];?>" /></label>
									<div class="error"><?php echo $streetError[2]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Town / City (optional)<input name="town[2]" type="text" maxlength="32" value="<?php echo $town[2];?>" /></label>
									<div class="error"><?php echo $townError[2]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Postcode (optional)<input name="postcode[2]" type="text" maxlength="5" value="<?php echo $postcode[2];?>" /></label>
									<div class="error"><?php echo $postcodeError[2]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Email<input name="email[2]" type="text" maxlength="64" value="<?php echo $email[2];?>" /></label>
									<div class="error"><?php echo $emailError[2]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Phone<input name="phone[2]" type="text" maxlength="16" value="<?php echo $phone[2];?>" /></label>
									<div class="error"><?php echo $phoneError[2]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Entry Fee
									  <select id="entry_fee[2]" name="entry_fee[2]" onchange="displayPaymentValue()">
<?php 
if ($gender === 'F' && ($event === '1.5 hour' || $event === '3 hour')) {
    echo '<option value="">Please Select</option>';
	echo '<option value="0"';
	echo ($entry_fee[2]==0)?' selected':'';
	echo '>$0</option>';
} elseif ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ? ORDER BY event_id, code DESC" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo ($entry_fee[2]==$senior_price)?' selected':'';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo ($entry_fee[2]==$junior_price)?' selected':'';
		echo '>$'.$junior_price.' Junior</option>';
	}	else {
		echo '<option value="">Please Select an Event</option>';
	}
}	else {
	echo '<option value="">Please Select an Event</option>';
}
?>										  
									  </select>
									</label>
									<div class="error"><?php echo $entry_feeError[2]; ?></div>
								</div>
							</div>
<?php if ($use_date_of_birth === 'Y') : ?>							
							<div id="dob[2]" class="row">
								<div class="large-2 medium-4 small-6 columns">
									<label for="dob_yyyy[2]" class="right inline">Birth Date</label>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_yyyy[2]" name="dob_yyyy[2]" onchange="OnMonthYearChange('dob', 2);">
										<option value="">Year</option>
<?php
$thisYear = date("Y");
for ($i = $thisYear-10; $i > $thisYear-90; $i--) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_yyyy[2]) {
		echo ' selected';
	}
	echo '>' . $i . '</option>';
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_mm[2]" name="dob_mm[2]" onchange="OnMonthYearChange('dob', 2);">
										<option value="">Month</option>
<?php
for ($i = 1; $i < 13; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_mm[2]) {
		echo ' selected';
	}
	echo '>' . $monthNames[$i-1] . '</option>';
}
?>					
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_dd[2]" name="dob_dd[2]" onchange="OnDayChange('dob', 2);">
										<option value="">Day</option>
<?php
if ($dob_yyyy[2] > 0 && $dob_mm[2]) {
	for ($i = 1; $i < getDaysInMonth($dob_yyyy[2], $dob_mm[2]) + 1; $i++) {
		echo '<option value="' . $i . '"';
		if ($i == $dob_dd[2]) {
			echo ' selected';
		}
		echo '>' . $i . '</option>';
	}
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<label for="age_at_event[2]" class="right inline">Age at event date</label>
									<div class="error"><?php echo $age_at_eventError[2]; ?></div>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<input id="age_at_event[2]" name="age_at_event[2]" readonly="readonly" value="<?php echo $age_at_event[2]?>" />
								</div>
							</div>
<?php endif; ?>							
						</fieldset>
					</div>
				</div>

				<div id="entrant4" class="row hide">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Member four</legend>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>First Name<input name="first_name[3]" type="text" maxlength="32" value="<?php echo $first_name[3];?>" /></label>
									<div class="error"><?php echo $first_nameError[3]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Last Name<input name="last_name[3]" type="text" maxlength="32" value="<?php echo $last_name[3];?>" /></label>
									<div class="error"><?php echo $last_nameError[3]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($use_country === 'Y') : ?>
									<label>Country<input name="country[3]" type="text" maxlength="32" value="<?php echo $country[3];?>" /></label>
<?php endif; ?>								
									<div class="error"><?php echo $countryError[3]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Street (optional)<input name="street[3]" type="text" maxlength="32" value="<?php echo $street[3];?>" /></label>
									<div class="error"><?php echo $streetError[3]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Town / City (optional)<input name="town[3]" type="text" maxlength="32" value="<?php echo $town[3];?>" /></label>
									<div class="error"><?php echo $townError[3]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Postcode (optional)<input name="postcode[3]" type="text" maxlength="5" value="<?php echo $postcode[3];?>" /></label>
									<div class="error"><?php echo $postcodeError[3]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Email<input name="email[3]" type="text" maxlength="64" value="<?php echo $email[3];?>" /></label>
									<div class="error"><?php echo $emailError[3]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Phone<input name="phone[3]" type="text" maxlength="16" value="<?php echo $phone[3];?>" /></label>
									<div class="error"><?php echo $phoneError[3]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Entry Fee
									  <select id="entry_fee[3]" name="entry_fee[3]" onchange="displayPaymentValue()">
<?php 
if ($gender === 'F' && ($event === '1.5 hour' || $event === '3 hour')) {
	echo '<option value="">Please Select</option>';
	echo '<option value="0"';
	echo ($entry_fee[3]==0)?' selected':'';
	echo '>$0</option>';
} elseif ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ? ORDER BY event_id, code DESC" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo ($entry_fee[3]==$senior_price)?' selected':'';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo ($entry_fee[3]==$junior_price)?' selected':'';
		echo '>$'.$junior_price.' Junior</option>';
	}	else {
		echo '<option value="">Please Select an Event</option>';
	}
}	else {
	echo '<option value="">Please Select an Event</option>';
}
?>										  
									  </select>
									</label>
									<div class="error"><?php echo $entry_feeError[3]; ?></div>
								</div>
							</div>
<?php if ($use_date_of_birth === 'Y') : ?>							
							<div id="dob[3]" class="row">
								<div class="large-2 medium-4 small-6 columns">
									<label for="dob_yyyy[3]" class="right inline">Birth Date</label>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_yyyy[3]" name="dob_yyyy[3]" onchange="OnMonthYearChange('dob', 3);">
										<option value="">Year</option>
<?php
$thisYear = date("Y");
for ($i = $thisYear-10; $i > $thisYear-90; $i--) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_yyyy[3]) {
		echo ' selected';
	}
	echo '>' . $i . '</option>';
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_mm[3]" name="dob_mm[3]" onchange="OnMonthYearChange('dob', 3);">
										<option value="">Month</option>
<?php
for ($i = 1; $i < 13; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_mm[3]) {
		echo ' selected';
	}
	echo '>' . $monthNames[$i-1] . '</option>';
}
?>					
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_dd[3]" name="dob_dd[3]" onchange="OnDayChange('dob', 3);">
										<option value="">Day</option>
<?php
if ($dob_yyyy[3] > 0 && $dob_mm[3]) {
	for ($i = 1; $i < getDaysInMonth($dob_yyyy[3], $dob_mm[3]) + 1; $i++) {
		echo '<option value="' . $i . '"';
		if ($i == $dob_dd[3]) {
			echo ' selected';
		}
		echo '>' . $i . '</option>';
	}
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<label for="age_at_event[3]" class="right inline">Age at event date</label>
									<div class="error"><?php echo $age_at_eventError[3]; ?></div>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<input id="age_at_event[3]" name="age_at_event[3]" readonly="readonly" value="<?php echo $age_at_event[3]?>" />
								</div>
							</div>
<?php endif; ?>							
						</fieldset>
					</div>
				</div>

				<div id="entrant5" class="row hide">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Member five</legend>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>First Name<input name="first_name[4]" type="text" maxlength="32" value="<?php echo $first_name[4];?>" /></label>
									<div class="error"><?php echo $first_nameError[4]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Last Name<input name="last_name[4]" type="text" maxlength="32" value="<?php echo $last_name[4];?>" /></label>
									<div class="error"><?php echo $last_nameError[4]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($use_country === 'Y') : ?>
									<label>Country<input name="country[4]" type="text" maxlength="32" value="<?php echo $country[4];?>" /></label>
<?php endif; ?>								
									<div class="error"><?php echo $countryError[4]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Street (optional)<input name="street[4]" type="text" maxlength="32" value="<?php echo $street[4];?>" /></label>
									<div class="error"><?php echo $streetError[4]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Town / City (optional)<input name="town[4]" type="text" maxlength="32" value="<?php echo $town[4];?>" /></label>
									<div class="error"><?php echo $townError[4]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Postcode (optional)<input name="postcode[4]" type="text" maxlength="5" value="<?php echo $postcode[4];?>" /></label>
									<div class="error"><?php echo $postcodeError[4]; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Email<input name="email[4]" type="text" maxlength="64" value="<?php echo $email[4];?>" /></label>
									<div class="error"><?php echo $emailError[4]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Phone<input name="phone[4]" type="text" maxlength="16" value="<?php echo $phone[4];?>" /></label>
									<div class="error"><?php echo $phoneError[4]; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Entry Fee
									  <select id="entry_fee[4]" name="entry_fee[4]" onchange="displayPaymentValue()">
<?php 
if ($gender === 'F' && ($event === '1.5 hour' || $event === '3 hour')) {
	echo '<option value="">Please Select</option>';
	echo '<option value="0"';
	echo ($entry_fee[4]==0)?' selected':'';
	echo '>$0</option>';
} elseif ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ? ORDER BY event_id, code DESC" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo ($entry_fee[4]==$senior_price)?' selected':'';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo ($entry_fee[4]==$junior_price)?' selected':'';
		echo '>$'.$junior_price.' Junior</option>';
	}	else {
		echo '<option value="">Please Select an Event</option>';
	}
}	else {
	echo '<option value="">Please Select an Event</option>';
}
?>										  
									  </select>
									</label>
									<div class="error"><?php echo $entry_feeError[4]; ?></div>
								</div>
							</div>
<?php if ($use_date_of_birth === 'Y') : ?>							
							<div id="dob[4]" class="row">
								<div class="large-2 medium-4 small-6 columns">
									<label for="dob_yyyy[4]" class="right inline">Birth Date</label>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_yyyy[4]" name="dob_yyyy[4]" onchange="OnMonthYearChange('dob', 4);">
										<option value="">Year</option>
<?php
$thisYear = date("Y");
for ($i = $thisYear-10; $i > $thisYear-90; $i--) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_yyyy[4]) {
		echo ' selected';
	}
	echo '>' . $i . '</option>';
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_mm[4]" name="dob_mm[4]" onchange="OnMonthYearChange('dob', 4);">
										<option value="">Month</option>
<?php
for ($i = 1; $i < 13; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $dob_mm[4]) {
		echo ' selected';
	}
	echo '>' . $monthNames[$i-1] . '</option>';
}
?>					
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<select id="dob_dd[4]" name="dob_dd[4]" onchange="OnDayChange('dob', 4);">
										<option value="">Day</option>
<?php
if ($dob_yyyy[4] > 0 && $dob_mm[4]) {
	for ($i = 1; $i < getDaysInMonth($dob_yyyy[4], $dob_mm[4]) + 1; $i++) {
		echo '<option value="' . $i . '"';
		if ($i == $dob_dd[4]) {
			echo ' selected';
		}
		echo '>' . $i . '</option>';
	}
}
?>										
									</select>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<label for="age_at_event[4]" class="right inline">Age at event date</label>
									<div class="error"><?php echo $age_at_eventError[4]; ?></div>
								</div>
								<div class="large-2 medium-4 small-6 columns">
									<input id="age_at_event[4]" name="age_at_event[4]" readonly="readonly" value="<?php echo $age_at_event[4]?>" />
								</div>
							</div>
<?php endif; ?>							
						</fieldset>
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
	  echo '<div class="row"><div class="small-12 columns"><fieldset><legend>Optional Extras</legend>';
		echo '<p>Please enter the number of each extra that you wish to purchase. The cost will be added to your entry total.</p>';
		echo '<div class="row">';
	  $index = 0;
	  while ($stmt->fetch()) {
			echo '<div class="large-3 small-12 columns">';
			echo '<input type="hidden" name="extraCode['.$index.']" value="'.$code.'" />';
			echo '<input type="hidden" name="extraValue['.$index.']" value="'.$cost.'" />';
			echo '<label>'.$description.' - <b>$'.$cost.'</b><input type="number" name="extraQuantity['.$index.']" min="0" max="10" value="'.$extraQuantity[$index].'" onchange="displayPaymentValue()" /></label>';
			echo '<div class="error">'.$extraError[$index].'</div>';
			echo '</div>';
			$index += 1;
	  }
	  echo '</div>'; 
	  echo '<p>The team leader will be contacted closer to the event to request further details for the extras purchased.</p>';
	  echo '</fieldset></div></div>';
	}
	$stmt->close();
}
?>
				<div class="row">
					<div class="small-12 columns">
<?php if ($cur_entrants == 0 || $cur_entrants < $max_entrants) : ?>
						<fieldset>
							<legend>Payment</legend>
							<div class="row">
								<div class="small-12 columns panel">
									<p class="text-justify">
										Each competitor must agree to abide by the NZRA rules and Code of Conduct. These can be found at <a target="_blank" href="https://www.rogaine.org.nz/nzra/about-nzra/">About NZRA</a>.<br>
										<strong>Your attention is drawn particularly to the condition that you enter this event entirely at your own risk.</strong>
										All participants will be required to sign an indemnity that neither the Association, the organisers, nor the landowners will be held responsible for any accident or injury which may occur.
									</p>
								</div>
							</div>
							<div class="row">
								<div class="medium-8 small-12 columns">
									Your entry will be displayed immediately in the <a target="_blank" href="entrants.php">list of entrants</a> on this website however it will not be confirmed until we have received payment.
<?php if ($max_entrants < 4096) : ?>
									<p class="highlight">If we do not receive payment you may be placed on the wait list.</p>
<?php endif; ?>									
								</div>
								<div class="medium-4 small-12 columns">
									<label>Total Entry Fees NZ$
										<input type="text" id="payment_value" name="payment_value" readonly="readonly" value="$<?php echo $payment_value?>" />
									</label>
								</div>
							</div>
<?php
$sql = "SELECT pay_credit_card, pay_direct_credit, pay_cheque from event where event_id = ?";
$payCreditCard = "N";
$payDirectCredit = "Y";
$payCheque = "N";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($payCreditCard, $payDirectCredit, $payCheque);
	$stmt->fetch();
	$stmt->close();
}	
?>							
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
<?php if ($payCreditCard === "Y") : ?>
									<label><input type="radio" name="payment_type" value="credit card" <?php echo ($payment_type=='credit card')?' checked':''?> >&nbsp;Credit Card</label>
									<div class="error"><?php echo $payment_typeError; ?></div>
									<div class="row">
										<div class="small-12 small-centered columns">
										  <p><strong>Card Details</strong></p>
										  <div class="panel dehighlight">
										    <h4><small>Payment Terms and Conditions</small></h4>
                                            By entering and making payment you agree to these terms and conditions.<br>		
										    The entry fee shown is in NZ$. Once payment is made the entry can not be cancelled however a manual process may be negotiated.
										    The details for this entry will only be used for the administration of this event and will not be shared with any outside party.
										    For any queries please contact:
<?php
$sql = "SELECT trim(first_name), trim(last_name), trim(email), trim(phone), trim(line_1), trim(line_2), trim(line_3), postcode"
		. " FROM event JOIN contact ON contact.contact_id = event.entry_contact_id"
		. " JOIN cheque_address ON cheque_address.address_id = event.address_id"
		. " WHERE event.event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($firstName, $lastName, $emailAddr, $phone, $line1, $line2, $line3, $postCode);
	$stmt->fetch();
	if ($stmt->num_rows > 0) {
		echo $firstName.' '. $lastName.'<br>';
		//echo $line1.', ';
		//echo $line2.', ';
		//echo $line3.' '.$postCode.'<br>';
		//if (isset($phone) && strlen(trim($phone)) > 0) {
  		//  echo 'Phone: '.$phone.'<br>';
		//}
		echo 'Email: <a href="mailto:'.$emailAddr.'">'.$emailAddr.'</a>';
	}
	$stmt->close();
}
?>
										  </div>
										  <!--  <img src="img/paystation_logo.jpg" alt="Paystation Logo"> -->
										  <!-- <p>Test credit cards can be found <a href="http://paystation.co.nz/test-card-numbers" target="_blank">here</a></p> -->
										</div>
									</div>
<?php endif; ?>								
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($payDirectCredit === "Y") : ?>
									<label><input type="radio" name="payment_type" value="direct credit" <?php echo ($payment_type=='direct credit')?' checked':''?> >&nbsp;Direct credit</label>
									<div class="error"><?php echo $payment_typeError; ?></div>
									<div class="row">
										<div class="small-12 small-centered columns">
<?php 										
$sql="SELECT trim(account_name), trim(account_number)" .
	" from event inner join bank_account on bank_account.account_id = event.account_id" .
	" where event_id= ?";
if ($stmt = $mysqli->prepare ($sql)) {
	$stmt->bind_param ( 'i', $event_id );
	$stmt->execute ();
	$stmt->store_result();
	$stmt->bind_result($accountName, $accountNumber);
	$stmt->fetch();
	echo '<p><strong>To ' . $accountName. ' Account No.<br>' . $accountNumber. '</strong></p>';
	$stmt->close();
}
?>			
										  <div class="panel">
											Use your Team Number as <strong>Reference</strong>, Team Contact Surname as <strong>Code</strong> and this Event Name as <strong>Particulars</strong> to identify your payment.
											<br>Please make only one full payment per team.
										  </div>
										</div>
									</div>
<?php endif; ?>								
								</div>
								<div class="large-4 medium-6 small-12 columns">
<?php if ($payCheque === "Y") : ?>
									<label><input type="radio" name="payment_type" value="cheque" <?php echo ($payment_type=='cheque')?' checked':''?> >&nbsp;Cheque</label>
									<div class="error"><?php echo $payment_typeError; ?></div>
									<div class="row">
										<div class="small-12 small-centered columns">
<?php
$sql = "SELECT trim(description), trim(first_name), trim(last_name), trim(email), trim(line_1), trim(line_2), trim(line_3), postcode, payable_to"
		. " FROM event JOIN contact ON contact.contact_id = event.entry_contact_id"
		. " JOIN cheque_address ON cheque_address.address_id = event.address_id"
		. " WHERE event.event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($description, $firstName, $lastName, $emailAddr, $line1, $line2, $line3, $postCode, $payableTo);
	$stmt->fetch();
	if ($stmt->num_rows > 0) {
		echo '<p><strong>Payable to<br>'. $payableTo. '</strong></p>';
		echo '<div class="panel">';
		echo 'Send to:<br>'.$description.'<br>';
		echo 'c/o '. $firstName.' '. $lastName.'<br>';
		echo $line1.'<br>';
		echo $line2.'<br>';
		echo $line3.' '.$postCode.'<br>';
		echo 'Email: <a href="mailto:'.$emailAddr.'">'.$emailAddr.'</a>';
		echo '</div>'; 
	}
	$stmt->close();
}
?>		
										</div>
									</div>
<?php endif; ?>								
								</div>
							</div>
						</fieldset>
<?php else : ?>
						<fieldset>
							<legend>Wait List</legend>
							<div class="row">
								<div class="small-10 small-centered columns panel callout">
									<p class="text-justify">
										The maximum number of entrants for this event is <?php echo $max_entrants; ?>.
										There are already <?php echo $cur_entrants; ?> entrants.
										Click ENTER and your entry will be added to the waitlist. 
										You will be notified if an entry becomes available and your entry will then be confirmed when payment is received.
									</p>
								</div>
							</div>
						</fieldset>
<?php endif; ?>							
						<fieldset>
							<legend>Notes</legend>
							<div class="row">
								<div class="small-12 columns">
									<label>If you have any message for the organisers please type it here. <!-- This should include any special dietary requirements. -->
									  <textarea name="comments" cols="64" rows="4" wrap="soft" maxlength="1024"><?php echo $comments?></textarea>
									</label>
								</div>
							</div>
						</fieldset>
						<fieldset>
							<legend>Acceptance</legend>
							<div class="row">
								<div class="small-12 columns">
									<div class="error"><?php echo $waiverError ?></div>
									<input type="checkbox" name="waiver" <?php echo (empty($_POST['waiver'])) ? '':' checked'?> value="Y" />
									&nbsp;I (the Team Contact) undertake to ensure all team members have seen or been fully advised of the written event briefing information distributed to me and available at <a target="_blank" href="https://www.rogaine.org.nz/nzra/">rogaine.org.nz/nzra</a> before the event.
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Confirm entry details</legend>
							<div class="row">
								<div class="small-12 columns">
									<p class="text-center">When you are happy that you have the correct entry details, press <strong>Enter</strong>.<br>
									<strong>Please wait while your entry processes.</strong> This may take a few seconds.</p>
									<p class="text-center">Your entry should immediately appear in the list of Entrants and a confirmation email will be sent to the Team Contact.<br>
<?php
$sql = "SELECT trim(email) FROM event"
		. " JOIN contact ON contact.contact_id = event.entry_contact_id"
		. " WHERE event.event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($emailAddr);
	$stmt->fetch();
	if ($stmt->num_rows > 0) {
		echo 'Please check this. If it does not happen please contact <a href="mailto:'.$emailAddr.'">'.$emailAddr.'</a>';
	}
	$stmt->close();
}
?>		
									</p>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<input id="enterButton" class="button success" type="submit" name="submitButton" value="Enter">
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<div id="formErrors" class="error">
<?php if ($pageError === TRUE) : ?>Errors exist! Please check the form for the details, correct them and press enter again.<?php endif; ?>
									</div>				
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<input id="cancelButton" class="button info" type="submit" name="submitButton" value="Cancel">
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php include 'footer.php'?>
</body>
</html>
