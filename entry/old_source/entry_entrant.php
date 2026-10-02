<?php
include_once '../includes/functions.php';
sec_session_start ();
if (find_event($mysqli) === false){
	header('Location: no_event_error.php');
}
if (event_open($_SESSION['eventId'], $mysqli) === false){
	header('location:closed_error.php');
}
if (! isset ($_SESSION['team_name'])) {
	header('location:entry_header.php');
}
$f_first_name = '';
$f_last_name = '';
$f_street = '';
$f_town = '';
$f_postcode = '';
$f_email = '';
$f_phone = '';
$f_entry_fee = 0;
$first_nameError = '';
$last_nameError = '';
$streetError = '';
$townError = '';
$postcodeError = '';
$emailError = '';
$phoneError = '';
$entry_feeError = '';
$pageError = false;
// Populate variables from the session
$first_name = array ();
$last_name = array ();
$street = array ();
$town = array ();
$postcode = array ();
$email = array ();
$phone = array ();
$entry_fee = array ();
if (isset ( $_SESSION ['entrant_count'] )) {
	$entrant_count = $_SESSION ['entrant_count'];
	$first_name = $_SESSION ['first_name'];
	$last_name = $_SESSION ['last_name'];
	$street = $_SESSION ['street'];
	$town = $_SESSION ['town'];
	$postcode = $_SESSION ['postcode'];
	$email = $_SESSION ['email'];
	$phone = $_SESSION ['phone'];
	$entry_fee = $_SESSION ['entry_fee'];
} else {
	$entrant_count = - 1;
}
// If this form was submitted then
// First check if delete was requested
if (isset ( $_POST ['deleteButton'] ) && $entrant_count >= 0) {
	// $index should be the index of the session array that we waht to delete
	// Note that the entrant_count has already been incremented to allow for this page
	$index = key ( $_POST ['deleteButton'] );
	// Just move all the array values back one and decrement the entrant_count
	for($i = $index; $i < $entrant_count; $i ++) {
		$first_name [$i] = $first_name [$i + 1];
		$last_name [$i] = $last_name [$i + 1];
		$street [$i] = $street [$i + 1];
		$town [$i] = $town [$i + 1];
		$postcode [$i] = $postcode [$i + 1];
		$email [$i] = $email [$i + 1];
		$phone [$i] = $phone [$i + 1];
		$entry_fee [$i] = $entry_fee [$i + 1];
	}
	$first_name [$entrant_count] = '';
	$last_name [$entrant_count] = '';
	$street [$entrant_count] = '';
	$town [$entrant_count] = '';
	$postcode [$entrant_count] = '';
	$email [$entrant_count] = '';
	$phone [$entrant_count] = '';
	$entry_fee [$entrant_count] = '';
	$entrant_count --;
	$_SESSION ['entrant_count'] = $entrant_count;
	$_SESSION ['first_name'] = $first_name;
	$_SESSION ['last_name'] = $last_name;
	$_SESSION ['street'] = $street;
	$_SESSION ['town'] = $town;
	$_SESSION ['postcode'] = $postcode;
	$_SESSION ['email'] = $email;
	$_SESSION ['phone'] = $phone;
	$_SESSION ['entry_fee'] = $entry_fee;
}
// Check for submit button
if (! empty ( $_POST ['submitButton'] )) {
	$_POST = f_clean ( $_POST );
	// Now validate
	$f_first_name = strip_tags ( trim ( $_POST ['first_name'] ) );
	if (empty ( $f_first_name ) || strlen ( $f_first_name ) < 2) {
		$first_nameError = "Please enter the full name. An initial is not sufficient";
		$pageError = true;
	}
	$f_last_name = strip_tags ( trim ( $_POST ['last_name'] ) );
	if (empty ( $f_last_name ) || strlen ( $f_last_name ) < 2) {
		$last_nameError = "Please enter a valid last name";
		$pageError = true;
	}
	$f_street = strip_tags ( trim ( $_POST ['street'] ) );
	if (empty ( $f_street ) || strlen ( $f_street ) < 2) {
		$streetError = "Please enter a valid street address";
		$pageError = true;
	}
	$f_town = strip_tags ( trim ( $_POST ['town'] ) );
	if (empty ( $f_town ) || strlen ( $f_town ) < 2) {
		$townError = "Please enter a valid town or city";
		$pageError = true;
	}
	$f_postcode = trim ( $_POST ['postcode'] );
	if (empty ( $f_postcode )) {
		$postcodeError = "Please enter a postcode. Check the NZ post site";
		$pageError = true;
	} elseif (! preg_match ( "/^([0-9]){4}?$/", $f_postcode )) {
		$postcodeError = "Please enter four digits";
		$pageError = true;
	}
	$f_email = trim ( $_POST ['email'] );
	if (empty ( $f_email )) {
		$emailError = "Please enter an email address";
		$pageError = true;
	} elseif (! filter_var ( $f_email, FILTER_VALIDATE_EMAIL )) {
		$emailError = "Please enter a valid email address";
		$pageError = true;
	}
	$f_phone = trim ( $_POST ['phone'] );
	$f_phone = preg_replace ( '/[^\d\(\)]/', '', $f_phone );
	if (empty ( $f_phone )) {
		$phoneError = "Please enter a valid phone number";
		$pageError = true;
	} elseif (! preg_match ( "/(^(03|04|06|07|09)\d{7}$)|(^(021|022|025|027|028|029)\d{6,8}$)/", $f_phone )) {
		$phoneError = "Please enter a valid NZ phone number including STD code";
		$pageError = true;
	}
	if (empty ( $_POST ['entry_fee'] )) {
		if ($_SESSION['eventId'] == 5 and $_SESSION['event'] === 'Family') {
			$f_entry_fee = 0;
		} else {
			$entry_feeError = 'Please select the correct entry fee';
			$pageError = true;
		}
	} else {
		$f_entry_fee = $_POST ['entry_fee'];
	}
	// If they want to go on to the payment page and nothing has been keyed let them go
	if ($_POST ['submitButton'] == "Payment" && empty ( $f_first_name ) && empty ( $f_last_name ) && empty ( $f_street ) && empty ( $f_town ) && empty ( $f_postcode ) && empty ( $f_email ) && empty ( $f_phone )) {
		header ( 'Location: entry_payment.php' );
		exit ();
	}
	// If no errors
	if ($pageError === FALSE) {
		// First populate the session
		$entrant_count += 1;
		$first_name [$entrant_count] = $f_first_name;
		$last_name [$entrant_count] = $f_last_name;
		$street [$entrant_count] = $f_street;
		$town [$entrant_count] = $f_town;
		$postcode [$entrant_count] = $f_postcode;
		$email [$entrant_count] = $f_email;
		$phone [$entrant_count] = $f_phone;
		$entry_fee [$entrant_count] = $f_entry_fee;
		$_SESSION ['entrant_count'] = $entrant_count;
		$_SESSION ['first_name'] = $first_name;
		$_SESSION ['last_name'] = $last_name;
		$_SESSION ['street'] = $street;
		$_SESSION ['town'] = $town;
		$_SESSION ['postcode'] = $postcode;
		$_SESSION ['email'] = $email;
		$_SESSION ['phone'] = $phone;
		$_SESSION ['entry_fee'] = $entry_fee;
		// Only to the payment screen if there is valid data
		if ($_POST ['submitButton'] == "Payment") {
			header ( 'Location: entry_payment.php' );
		} elseif ($_POST ['submitButton'] == "Another Entrant") {
			header ( 'Location: entry_entrant.php' );
		}
	}
	// if they want to go back to the entry screen let them
	// They will loose any invalid data that they keyed.
	if ($_POST ['submitButton'] == "Event") {
		header ( 'Location: entry_header.php' );
	}
}
?>
<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
<?php include '../head.php'?>
<body>
<?php include '../header.php'?>
<div id="main" class="row">
		<div class="small-12 columns">
			<form id="entrantForm" method="post">
				<div class="row">
					<div class="small-12 columns">
						<h4>Team</h4>
						<table>
							<tbody>
<?php
echo '<tr>';
echo '<td></td>';
echo '<td colspan="5">' .'Team Name: '.$_SESSION['team_name'] . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td colspan="5">' .'Grade: ' . $_SESSION ['gender'] . $_SESSION ['age_group'] . ' ' . $_SESSION ['event'] . '</td>';
echo '</tr>';
if (isset ( $_SESSION ['entrant_count'] )) {
	$entrant_count = $_SESSION ['entrant_count'];
	$first_name = $_SESSION ['first_name'];
	$last_name = $_SESSION ['last_name'];
	$street = $_SESSION ['street'];
	$town = $_SESSION ['town'];
	$postcode = $_SESSION ['postcode'];
	$email = $_SESSION ['email'];
	$phone = $_SESSION ['phone'];
	$entry_fee = $_SESSION ['entry_fee'];
	for($i = 0; $i <= $entrant_count; $i ++) {
		echo '<tr>';
		echo '<td><button class="delete" title="Click to delete" name="deleteButton[' . $i . ']" type="submit" value="delete"><img alt="Delete" src="./img/delete.png" height="16" width="16"></button></td>';
		echo '<td>' . $first_name [$i] . ' ' . $last_name [$i];
		if ($i == 0) {
			echo ' (Team Contact)';
		}
		echo '</td>';
		echo '<td>' . $street [$i] . ', ' . $town [$i] . ' ' . $postcode [$i] . '</td>';
		echo '<td>' . $email [$i] . '</td>';
		echo '<td>' . $phone [$i] . '</td>';
		echo '<td>Fee $' . $entry_fee [$i] . '</td>';
		echo '</tr>';
	}
}
?>
            </tbody>
						</table>
					</div>
					<hr>
				</div>
				<div class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Member</legend>
<?php if ($entrant_count < 0) : ?>							
							<div class="row">
								<div class="large-12 columns">
								  <div id="firstMember" class="highlight">
								    Please note that the first team member will be the Team Contact and will be responsible to ensure that all team members are aware of the terms and conditions for competing in this event.
								  </div>
								</div>
								<hr>
							</div>
<?php endif; ?>							
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>First Name <input name="first_name" type="text"
										maxlength="32" value="<?php echo $f_first_name;?>" /></label>
									<div class="error"><?php echo $first_nameError; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Last Name <input name="last_name" type="text"
										maxlength="32" value="<?php echo $f_last_name;?>" /></label>
									<div class="error"><?php echo $last_nameError; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns"></div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Street <input name="street" type="text" maxlength="32"
										value="<?php echo $f_street;?>" /></label>
									<div class="error"><?php echo $streetError; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Town / City<input name="town" type="text" maxlength="32"
										value="<?php echo $f_town;?>" /></label>
									<div class="error"><?php echo $townError; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Postcode <input name="postcode" type="text"
										maxlength="5" value="<?php echo $f_postcode;?>" /></label>
									<div class="error"><?php echo $postcodeError; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<label>Email <input name="email" type="text" maxlength="64"
										value="<?php echo $f_email;?>" /></label>
									<div class="error"><?php echo $emailError; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Phone <input name="phone" type="text" maxlength="16"
										value="<?php echo $f_phone;?>" /></label>
									<div class="error"><?php echo $phoneError; ?></div>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<label>Entry Fee <select name="entry_fee">
										<option value="">Please Select</option>
										<?php price_options($_SESSION['eventId'], $_SESSION['event'], $mysqli, $f_entry_fee);?>
									</select>
									</label>
									<div class="error"><?php echo $entry_feeError; ?></div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="row">
					<div class="large-4 medium-6 small-12 columns">
						<input id="anotherEntrantButton" class="button round"
							type="submit" name="submitButton" value="Another Entrant">
					</div>
					<div class="large-4 medium-6 small-12 columns">
						<input id="paymentButton" class="button round" type="submit"
							name="submitButton" value="Payment">
					</div>
					<div class="large-4 medium-6 small-12 columns">
						<input id="eventButton" class="button round" type="submit"
							name="submitButton" value="Event">
					</div>
				</div>
			</form>
		</div>
	</div>
<?php include '../footer.php'?>
</body>
</html>