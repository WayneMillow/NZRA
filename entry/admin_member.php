<?php
include_once 'includes/functions.php';
sec_session_start ();
if (find_event($mysqli) === false){
	header('Location: no_event_error.php');
	exit();
}
//$_POST = f_clean ( $_POST );
//$_GET = f_clean ( $_GET );
// Set up variables to hold the form fields and errors
if (isset ( $_SESSION ['eventId'] )) {
	$event_id = $_SESSION ['eventId'];
}
if (isset ( $_SESSION ['entry_id'] )) {
	$entry_id = $_SESSION ['entry_id'];
}
$member_id = filter_input ( INPUT_GET, 'id', $filter = FILTER_SANITIZE_STRING );
if (isset($member_id)) {
	$_SESSION ['member_id'] = $member_id;
}
if (isset($_SESSION ['member_id'])) {
	$member_id = $_SESSION ['member_id'];
} else {	
	$_SESSION ['member_id'] = $member_id;
}
$monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$event_date = get_event_date($event_id, $mysqli);
$use_country = 'N';
$use_date_of_birth = 'N';
if ($stmt = $mysqli->prepare("SELECT use_country, use_date_of_birth FROM event WHERE event_id = ?")) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($use_country, $use_date_of_birth);
	$stmt->fetch();
}
$first_name = '';
$last_name = '';
$street = '';
$town = '';
$country = '';
$postcode = '';
$email = '';
$phone = '';
$entry_fee = 0;
// Leave these as an array for shared javascript
$dob_yyyy[0] = 1970;
$dob_mm[0] = 1;
$dob_dd[0] = 1;
$age_at_event[0] = 0;
$event = '';
$age_group = '';
$lower_age = 0;
$upper_age = 0;
$first_nameError = '';
$last_nameError = '';
$streetError = '';
$townError = '';
$countryError = '';
$postcodeError = '';
$emailError = '';
$phoneError = '';
$entry_feeError = '';
$age_at_eventError = '';
$entrant_countError = '';
$pageError = false;
// Get the number of members in this team
$entrant_count = 0;
if (! ($stmt = $mysqli->prepare ( "SELECT count(*) FROM entrant where event_id = ? and entry_id = ?" ))) {
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (! ($stmt->bind_param ( 'ii', $event_id, $entry_id ))) {
	echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->execute ())) {
	echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->bind_result ( $entrant_count ))) {
	echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->fetch ())) {
	echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
}
$stmt->close ();
if ($member_id == 0 and $entrant_count > 4) {
	$entrant_countError = "There can be a maximum of five members in a team.";
	$pageError = true;
}
// Get the entry details
if (! ($stmt = $mysqli->prepare ( "SELECT sub_event_code from entry where entry_id = ?" ))) {
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (! ($stmt->bind_param ( 'i', $entry_id))) {
	echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->execute ())) {
	echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->bind_result ( $event ))) {
	echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->fetch ())) {
	echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
}
$stmt->close();
// Get the age group details
if (! ($stmt = $mysqli->prepare ( "SELECT code, lower_age, upper_age from entry" 
		. " join age_group on age_group.event_id = ? and age_group.code = entry.age_group_code"    
		. " where entry_id = ?" ))) {
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (! ($stmt->bind_param ( 'ii', $event_id, $entry_id))) {
	echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->execute ())) {
	echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->bind_result ( $age_group, $lower_age, $upper_age ))) {
	echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (! ($stmt->fetch ())) {
	echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
}
$stmt->close();
// First time into the form
if (empty ( $_POST ['updateButton'] ) and empty ( $_POST ['removeButton'] ) and empty ( $_POST ['addButton'] )) {
	if ($member_id != 0) {
		if (! ($stmt = $mysqli->prepare ( "SELECT first_name, last_name, street, town, country, postcode, email, phone, YEAR(date_of_birth), MONTH(date_of_birth), DAY(date_of_birth), entry_fee FROM entrant"  
				. " JOIN member on member.member_id = entrant.member_id"
				. " WHERE entrant.event_id = ? and entrant.entry_id = ? and entrant.member_id = ?" ))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param ( 'iii', $event_id, $entry_id, $member_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute ())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->bind_result ( $first_name, $last_name, $street, $town, $country, $postcode, $email, $phone, $dob_yyyy[0], $dob_mm[0], $dob_dd[0], $entry_fee ))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->fetch ())) {
			echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close ();
	}
	// If this form submitted the data to be updated
} else {
	if (! empty ( $_POST ['member_id'] )) {
		$member_id = $_POST ['member_id'];
	}
	// Check if we can remove the member
	if (! empty ( $_POST ['removeButton'] ) and $entrant_count < 2) {
		$entrant_countError = "There must be at least one members in a team.";
		$pageError = true;
	}
	// Check if we can add a member
	if ($member_id == 0 and $entrant_count > 4) {
		$entrant_countError = "There can be a maximum of five members in a team.";
		$pageError = true;
	}
	$first_name = strip_tags(trim($_POST ['first_name'] ) );
	if (empty ( $first_name ) || strlen ( $first_name ) < 2 || strlen($first_name) > 32) {
		$first_nameError = "Please enter the full name. An initial is not sufficient. A maximum of 32 characters.";
		$pageError = true;
	}
	$last_name = strip_tags ( trim ( $_POST ['last_name'] ) );
	if (empty ( $last_name ) || strlen ( $last_name ) < 2 || strlen($last_name) > 32) {
		$last_nameError = "Please enter a valid last name. A maximum of 32 characters.";
		$pageError = true;
	}
	$street = strip_tags ( trim ( $_POST ['street'] ) );
	if (empty ( $street ) || strlen ( $street ) < 2 || strlen($street) > 32) {
		$streetError = "Please enter a valid street address. A maximum of 32 characters.";
		$pageError = true;
	}
	$town = strip_tags ( trim ( $_POST ['town'] ) );
	if (empty ( $town ) || strlen ( $town ) < 2 || strlen($town) > 32) {
		$townError = "Please enter a valid town or city. A maximum of 32 characters.";
		$pageError = true;
	}
	if ($use_country === 'Y') {
		$country = strip_tags(trim($_POST ['country']));
	}
	if (empty($country)) {
		$country = "New Zealand";
	}
	if (strlen($country) < 2 || strlen($country) > 32 || strlen($country) > 32) {
		$countryError = "Please enter a valid country. A maximum of 32 characters.";
		$pageError = TRUE;
	}
	$postcode = strip_tags(trim($_POST ['postcode']));
	// Don't worry about a postcode outside of NZ
	if ($country == "New Zealand" || $country == "NZ") {
		if (empty ( $postcode )) {
			$postcodeError = "Please enter a postcode. Check the NZ post site";
			$pageError = true;
		} elseif (! preg_match ( "/^([0-9]){4}?$/", $postcode )) {
			$postcodeError = "Please enter four digits";
			$pageError = true;
		}
	}
	$email = strip_tags(trim($_POST ['email']));
	if (empty ( $email )) {
		$emailError = "Please enter an email address";
		$pageError = true;
	} elseif (! filter_var ( $email, FILTER_VALIDATE_EMAIL )) {
		$emailError = "Please enter a valid email address";
		$pageError = true;
	}
	$phone = strip_tags(trim($_POST ['phone']));
	if (empty($phone)) {
			$phoneError = "Please enter a valid phone number. Use + followed by the International Access Code outside of NZ.";
			$pageError = TRUE;
	} else {
		$phone = preg_replace('/[^\d\(\)\+]/', '', $phone);
		if (empty($phone)) {
			$phoneError[$idx] = "Please enter a valid phone number. Use + followed by the International Access Code outside of NZ.";
			$pageError = TRUE;
		} elseif (preg_match("/(^\++\d{8,15})/", $phone)) {
				// OK leading +
		} elseif (!preg_match("/(^\(?(03|04|06|07|09)\)?\d{7,8}$)|(^(021|022|025|027|028|029)\d{6,8}$)/", $phone)) {
			$phoneError = "Please enter a valid NZ phone number including STD code";
			$pageError = TRUE;
		}
	}
	if (empty ( $_POST ['entry_fee'] )) {
		//$entry_feeError = 'Please select the correct entry fee';
		//$pageError = true;
		$entry_fee = 0;
	} else {
		$entry_fee = $_POST ['entry_fee'];
	}
		// Calculate age at event date
	if ($use_date_of_birth === 'Y' && !empty($_POST['age_at_event'])) {
		$age_at_event = $_POST['age_at_event'];
	}
	if ($use_date_of_birth === 'Y' && !empty($_POST['dob_yyyy'])) {
		$dob_yyyy = $_POST['dob_yyyy'];
	}
	if ($use_date_of_birth === 'Y' && !empty($_POST['dob_mm'])) {
		$dob_mm = $_POST['dob_mm'];
	}
	if ($use_date_of_birth === 'Y' && !empty($_POST['dob_dd'])) {
		$dob_dd = $_POST['dob_dd'];
	}
	if ($use_date_of_birth === 'Y') {
		$dob = strtotime($dob_yyyy[0].'-'.$dob_mm[0].'-'.$dob_dd[0]);
		if ($dob) {
			if ($lower_age > 0 && $upper_age > 0) {
				if ($age_group == 'J' && ($age_at_event[0] < $lower_age || $age_at_event[0] > $upper_age)) {
					$age_at_eventError = 'Entrant must be between ' . $lower_age . ' and ' . $upper_age . ' to be in Junior grade.';
					$pageError = TRUE;
				} else if ($age_group != 'O' && $age_at_event[0] < $lower_age) {
					$age_at_eventError = 'Entrant must be older than ' . $lower_age . ' to be in this grade.';
					$pageError = TRUE;
				}
			}
		} else {
			$age_at_eventError = 'Please select a valid date of birth';
			$pageError = TRUE;
		}
	}
	// If no errors
	if ($pageError === FALSE) {
		$dob = $dob_yyyy[0].'-'.$dob_mm[0].'-'.$dob_dd[0];
		if ($use_date_of_birth != 'Y' || !strtotime($dob)) {
			$dob = '1900-01-01';
		}
		if (! empty ( $_POST ['updateButton'] ) ) {
			if (! ($stmt = $mysqli->prepare ( "UPDATE member set first_name = ?, last_name = ?, street = ?, town = ?, country = ?, postcode = ?, email = ?, phone = ?, date_of_birth = ? WHERE member_id = ?" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit ();
			}
			if (! ($stmt->bind_param ( 'sssssssssi', $first_name, $last_name, $street, $town, $country, $postcode, $email, $phone, $dob, $member_id ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
			if (! ($stmt = $mysqli->prepare ( "UPDATE entrant set entry_fee = ? where event_id = ? and entry_id = ? and member_id = ?" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit ();
			}
			if (! ($stmt->bind_param ( 'iiii', $entry_fee, $event_id, $entry_id, $member_id ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
		}
		if (! empty ( $_POST ['removeButton'] ) ) {
			if (! ($stmt = $mysqli->prepare ( "DELETE from entrant where event_id = ? and entry_id = ? and member_id = ?" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit ();
			}
			if (! ($stmt->bind_param ( 'iii', $event_id, $entry_id, $member_id ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				exit ();
			}
		}
		if (! empty ( $_POST ['addButton'] ) ) {
			if (! ($stmt = $mysqli->prepare ( "INSERT INTO member (first_name, last_name, street, town, country, postcode, email, phone, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)" ))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (! ($stmt->bind_param ( 'sssssssss', $first_name, $last_name, $street, $town, $country, $postcode, $email, $phone, $dob ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			$member_id = mysqli_insert_id ( $mysqli );
			if (! ($stmt = $mysqli->prepare ( "INSERT INTO entrant (event_id, entry_id, member_id, entry_fee) VALUES (?, ?, ?, ?)" ))) {
			  echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (! ($stmt->bind_param ( 'iiii', $event_id, $entry_id, $member_id, $entry_fee ))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (! ($stmt->execute ())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
		}
		// Reset the total entry fee
		if (! ($stmt = $mysqli->prepare ( "update entry set payment_value = (select sum(entry_fee) from entrant where event_id = ? and entry_id = ?) where entry_id = ?" ))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param ( 'iii', $event_id, $entry_id, $entry_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute ())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close ();
		if (isset ( $entry_id )) {
			header ( 'Location: admin_entry.php?id=' . $entry_id );
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
<body onload="OnDayChange('dob', 0)">
	<div id="header" class="row">
		<div class="small-12 large-2 columns">
			<img class="center" src="img/Logo3_130x118.gif" alt="logo" width="130" height="118">
		</div>
		<div class="small-12 large-6 columns">
			<h1><?php $result=get_description($_SESSION['eventId'], $mysqli); echo $result; ?></h1>
			<h2 class="text-center subheader">Administration</h2>
		</div>
		<div class="small-12 large-4 columns">
			<div class="nav-bar right">
				<ul class="button-group">
					<li><a class="button small" href="admin_entrants.php">Entrants</a></li>
<?php
if (isset ( $entry_id )) {
	echo '<li><a class="button small" href="admin_entry.php?id=' . $entry_id . '">Entry</a></li>';
}
?>
          <li><a class="button small" href="includes/logout.php?id=<?php echo $_SESSION['eventId'];?>">Logout</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="main" class="row">
		<div class="large-12 columns">
<?php if (login_check($mysqli) == true) : ?>
      <form id="headerForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<input id="eventDate" type="hidden" value="<?php echo $event_date;?>" />
				<input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />
				<fieldset>
					<legend>Member</legend>
					<div class="row">
						<div class="large-4 medium-6 small-12 columns">
							<label>First Name <input name="first_name" type="text" maxlength="32" value="<?php echo $first_name;?>" /></label>
							<div class="error"><?php echo $first_nameError; ?></div>
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<label>Last Name <input name="last_name" type="text" maxlength="32" value="<?php echo $last_name;?>" /></label>
							<div class="error"><?php echo $last_nameError; ?></div>
						</div>
						<div class="large-4 medium-6 small-12 columns">
<?php if ($use_country === 'Y') : ?>
							<label>Country<input name="country" type="text" maxlength="32" value="<?php echo $country;?>" /></label>
<?php endif; ?>							
							<div class="error"><?php echo $countryError; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="large-4 medium-6 small-12 columns">
							<label>Street <input name="street" type="text" maxlength="32" value="<?php echo $street;?>" /></label>
							<div class="error"><?php echo $streetError; ?></div>
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<label>Town / City<input name="town" type="text" maxlength="32" value="<?php echo $town;?>" /></label>
							<div class="error"><?php echo $townError; ?></div>
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<label>Postcode <input name="postcode" type="text" maxlength="5" value="<?php echo $postcode;?>" /></label>
							<div class="error"><?php echo $postcodeError; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="large-4 medium-6 small-12 columns">
							<label>Email <input name="email" type="text" maxlength="64" value="<?php echo $email;?>" /></label>
							<div class="error"><?php echo $emailError; ?></div>
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<label>Phone <input name="phone" type="text" maxlength="16" value="<?php echo $phone;?>" /></label>
							<div class="error"><?php echo $phoneError; ?></div>
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<label>Entry Fee <select name="entry_fee">
<?php
if ($stmt = $mysqli->prepare ( "SELECT senior_price, junior_price FROM sub_event WHERE event_id = ? AND code = ?" )) {
	$stmt->bind_param ( 'is', $event_id, $event);
	$stmt->execute ();
	$stmt->store_result ();
	if ($stmt->num_rows == 1) {
		$stmt->bind_result($senior_price, $junior_price);
		$stmt->fetch ();
		echo '<option value="">Please Select</option>';
		echo '<option value="'.$senior_price.'"';
		echo ($entry_fee==$senior_price)?' selected':'';
		echo '>$'.$senior_price.' Senior</option>';
		echo '<option value="'.$junior_price.'"';
		echo ($entry_fee==$junior_price)?' selected':'';
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
							<div class="error"><?php echo $entry_feeError; ?></div>
						</div>
					</div>
<?php if ($use_date_of_birth === 'Y') : ?>							
					<div id="dob" class="row">
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
							<div class="error"><?php echo $age_at_eventError; ?></div>
						</div>
						<div class="large-2 medium-4 small-6 columns">
							<input id="age_at_event[0]" name="age_at_event[0]" readonly="readonly" value="<?php echo $age_at_event[0]?>" />
						</div>
					</div>
<?php endif; ?>					
				</fieldset>
					
				<div class="row">
					<div class="large-4 medium-6 small-12 columns">
<?php if ($member_id == 0 and $entrant_count < 5) : ?>						
						<input class="button success small round" type="submit" name="addButton" value="Add Member">
<?php elseif ($member_id != 0) : ?>
						<input class="button success small round" type="submit" name="updateButton" value="Update Details">
<?php endif; ?>								
						<div class="error"><?php echo $entrant_countError; ?></div>
					</div>
					<div class="large-4 medium-6 small-12 columns">
					</div>
					<div class="large-4 medium-6 small-12 columns">
<?php if ($member_id != 0 and $entrant_count > 1) : ?>						
						<input class="button alert small round" type="submit" name="removeButton" value="Remove Member">
<?php endif; ?>								
						<div class="error"><?php echo $entrant_countError; ?></div>
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