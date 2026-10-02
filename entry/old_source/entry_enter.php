<?php
include_once '../includes/functions.php';
sec_session_start ();
if (! isset ( $_SESSION ['eventId'] )) {
	session_destroy ();
	header ( 'Location: home.html' );
	exit ();
}
if (event_open($_SESSION['eventId'], $mysqli) === false){
	header('location:closed_error.php');
}
if (! isset ($_SESSION['team_name'])) {
	header('location:entry_header.php');
}
// Check everything is valid - It should never get to this state but ...
$entrant_count = $_SESSION ['entrant_count'];
if (! isset ( $_SESSION ['team_name']) || ! isset ($_SESSION ['gender'] ) || ! isset ( $_SESSION ['age_group'] ) || ! isset ( $_SESSION ['gender'] ) || ! isset ( $_SESSION ['event'] ) || ! isset ( $_SESSION ['payment_type'] ) || ! isset ( $_SESSION ['payment_value'] ) || ! isset ( $_SESSION ['waiver'] ) || ! isset ( $_SESSION ['comments'] ) || ! isset ( $_SESSION ['entrant_count'] ) || ! isset ( $_SESSION ['first_name'] ) || ! isset ( $_SESSION ['last_name'] ) || ! isset ( $_SESSION ['street'] ) || ! isset ( $_SESSION ['town'] ) || ! isset ( $_SESSION ['postcode'] ) || ! isset ( $_SESSION ['email'] ) || ! isset ( $_SESSION ['phone'] ) || ! isset ( $_SESSION ['entry_fee'] ) || $entrant_count < 1 || $entrant_count > 4) {
	header ( 'Location: entry_header.php' );
	exit ();
}
$event_id = $_SESSION ['eventId'];
$team_name = $_SESSION ['team_name'];
$gender = $_SESSION ['gender'];
$age_group = $_SESSION ['age_group'];
$event = $_SESSION ['event'];
$payment_type = $_SESSION ['payment_type'];
$payment_value = $_SESSION ['payment_value'];
$waiver = $_SESSION ['waiver'];
$comments = $_SESSION ['comments'];
$first_name = $_SESSION ['first_name'];
$last_name = $_SESSION ['last_name'];
$street = $_SESSION ['street'];
$town = $_SESSION ['town'];
$postcode = $_SESSION ['postcode'];
$email = $_SESSION ['email'];
$phone = $_SESSION ['phone'];
$entry_fee = $_SESSION ['entry_fee'];
$entry_email = get_entry_contact_email($event_id, $mysqli);

if (! empty ( $_POST ['submitButton'] )) {
	// If the user wants to go back to
	if ($_POST ['submitButton'] == "Cancel") {
		session_destroy ();
		header ( 'Location: home.html' );
		exit ();
	} elseif ($_POST ['submitButton'] == "Another Entrant") {
		header ( 'Location: entry_entrant.php' );
		exit ();
	}
	// Do the post
	// Everything should be valid
	if (! ($stmt = $mysqli->prepare ( "INSERT INTO entry (team_name, gender, age_group, event, payment_type, payment_value, waiver, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?)" ))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if (! ($stmt->bind_param ( 'sssssiss', $team_name, $gender, $age_group, $event, $payment_type, $payment_value, $waiver, $comments ))) {
		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	if (! ($stmt->execute ())) {
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	$entry_id = mysqli_insert_id ( $mysqli );
	$stmt->close ();
	
	if (! ($stmt1 = $mysqli->prepare ( "INSERT INTO member (first_name, last_name, street, town, postcode, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)" ))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if (! ($stmt2 = $mysqli->prepare ( "INSERT INTO entrant (event_id, entry_id, member_id, entry_fee) VALUES (?, ?, ?, ?)" ))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	for($i = 0; $i <= $entrant_count; $i ++) {
		if (! ($stmt1->bind_param ( 'sssssss', $first_name [$i], $last_name [$i], $street [$i], $town [$i], $postcode [$i], $email [$i], $phone [$i] ))) {
			echo "Binding parameters failed: (" . $stmt1->errno . ") " . $stmt1->error;
		}
		if (! ($stmt1->execute ())) {
			echo "Execute failed: (" . $stmt1->errno . ") " . $stmt1->error;
		}
		$member_id = mysqli_insert_id ( $mysqli );
		
		if (! ($stmt2->bind_param ( 'iiii', $event_id, $entry_id, $member_id, $entry_fee [$i] ))) {
			echo "Binding parameters failed: (" . $stmt2->errno . ") " . $stmt2->error;
		}
		if (! ($stmt2->execute ())) {
			echo "Execute failed: (" . $stmt2->errno . ") " . $stmt2->error;
		}
	}
	$stmt1->close ();
	$stmt2->close ();
	// Email entry confirmation
	$to = $email [0];
	$subject = get_description($event_id, $mysqli). " Entry - Team " . $entry_id;
	// Maximum length 70 characters per line
	// 1234567890123456789012345678901234567890123456789012345678901234567890
	$message = 'Congratulations.' . "\r\n" . 'You have entered the ' . get_description($event_id, $mysqli) . '.' . "\r\n" . 'We look forward to seeing you there and hope that you enjoy it.' . "\r\n" . "\r\n" . 'You are entry number ' . $entry_id . '.' . "\r\n" . 'Please keep a note of this and include it in any communication.' . "\r\n" . 'Your entry will be confirmed in the list of entrants ' . "\r\n" . 'once we have received payment.' . "\r\n";
	if ($payment_type == 'direct credit') {
		$message .= "\r\n" . 'Please transfer payment of $' . $payment_value . "\r\n" . 'to NZRA Account No. 03 0674 0197735 00' . "\r\n" . 'using your name and you entry number as reference and particulars' . "\r\n" . 'to identify your payment.';
	} else if ($payment_type == 'cheque') {
		$message .= "\r\n" . 'Please send your cheque payment of $' . $payment_value . "\r\n";
		$query="SELECT trim(description), trim(first_name), trim(last_name), trim(line_1), trim(line_2), trim(line_3), postcode" .
				" from event inner join contact on contact_id = entry_contact_id" .
				" join cheque_address on event.address_id = cheque_address.address_id" .
				" where event_id=".$event_id;
		$result = $mysqli->query ( $query );
		$row = $result->fetch_array();
		$message .= 'to: '.$row[0]."\r\n";
		$message .=  'c/o '. $row[1].' '. $row[2]."\r\n";
		$message .=  $row[3]."\r\n";
		$message .=  $row[4]."\r\n";
		$message .=  $row[5].' '.$row[6]."\r\n";
	}
	$headers = 'From: webmaster@rogaine.org.nz' . "\r\n" . 'Reply-To: entries@rogaine.org.nz' . "\r\n" . 'Bcc: wayne.millow@gmail.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion ();
	mail ( $to, $subject, $message, $headers );
	// And go to look at the entrants
	session_destroy ();
	header ( "Location: entrants.php?eventId=$event_id" );
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
			<div class="row">
				<div class="small-12 columns">
					<h4>Entry</h4>
					<table id="entryTable">
						<tbody>
<?php
echo '<tr>';
echo '<td colspan="5">' .'<strong>Team Name</strong> '. $team_name . '</td>';
echo '</tr><tr>';
echo '<td colspan="2">' . '<strong>Grade</strong> ' . $gender . $age_group . ' ' . $event . '</td>';
echo '<td colspan="3">' . '<strong>Payment</strong> $' . $payment_value . ' by ' . $payment_type . '</td>';
echo '</tr>';
if (isset ( $entrant_count )) {
	for($i = 0; $i <= $entrant_count; $i ++) {
		echo '<tr>';
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
			</div>
			<form id="enterForm" method="post"
				action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<fieldset>
					<legend>Confirm entry details</legend>
					<div class="row">
						<div class="small-10 columns">
							<p class="text-center">When you are happy that you have the correct entry details, press <strong>Continue</strong>.<br>
							Please wait while your entry processes. This may take a few seconds.</p>
						</div>
					</div>
					<div class="row">
						<div class="small-12 columns">
							<p class="text-center">Your entry should immediately appear in the list of Entrants and a confirmation email will be sent to the Team Contact.<br>
							Please check this. If it does not happen please contact <a href="mailto:<?php echo $entry_email?>"><?php echo $entry_email?></a></p>
						</div>
					</div>
					<div class="row">
						<div class="large-4 medium-6 small-12 columns">
							<input id="anotherEntrantButton" class="button round"
								type="submit" name="submitButton" value="Another Entrant">
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<input id="continueButton" class="button round" type="submit"
								name="submitButton" value="Continue">
						</div>
						<div class="large-4 medium-6 small-12 columns">
							<input id="cancelButton" class="button round" type="submit"
								name="submitButton" value="Cancel">
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
<?php include '../footer.php'?>
</body>
</html>
