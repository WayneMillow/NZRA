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
$_POST = f_clean ( $_POST );
// Set up variables to hold the form fields and errors
$payment_value = 0;
$payment_type = '';
$comments = '';
$waiver = '';
$payment_typeError = '';
$commentsError = '';
$waiverError = '';
$entrant_count = 0;
// Populate the payment value being the sum of the all entry fees
if (isset ( $_SESSION ['entrant_count'] )) {
	$entrant_count = $_SESSION ['entrant_count'];
	$entry_fee = $_SESSION ['entry_fee'];
	for($i = 0; $i <= $entrant_count; $i ++) {
		$payment_value += $entry_fee [$i];
	}
}
$pageError = false;
// If we come back to this page after setting the session variables use them
if (isset ( $_SESSION ['payment_type'] )) {
	$payment_type = $_SESSION ['payment_type'];
}
if (isset ( $_SESSION ['comments'] )) {
	$comments = $_SESSION ['comments'];
}
if (isset ( $_SESSION ['waiver'] )) {
	$waiver = $_SESSION ['waiver'];
}
// If this form submitted the data then validate it
if (! empty ( $_POST ['submitButton'] )) {
	if (empty ( $_POST ['payment_type'] )) {
		$payment_typeError = 'Please select a Payment Type';
		$pageError = true;
	} else {
		$payment_type = $_POST ['payment_type'];
	}
	if (empty ( $_POST ['waiver'] )) {
		$waiverError = 'You must accept the terms and conditions in order to enter this event';
		$pageError = true;
	} else {
		$waiver = $_POST ['waiver'];
	}
	if (! empty ( $_POST ['comments'] )) {
		$comments = htmlentities ( trim ( $_POST ['comments'] ) );
	}
	// If no errors go to the next page
	if ($pageError === FALSE) {
		$_SESSION ['payment_value'] = $payment_value;
		$_SESSION ['payment_type'] = $payment_type;
		$_SESSION ['waiver'] = $waiver;
		$_SESSION ['comments'] = $comments;
		// Carry on to entry if the data is valid
		if ($_POST ['submitButton'] == "Enter") {
			header ( 'Location: entry_enter.php' );
		}
	}
	// if they want to go back to the entry or entrant screen let them
	// They will loose any invalid data that they keyed.
	if ($_POST ['submitButton'] == "Another Entrant") {
		header ( 'Location: entry_entrant.php' );
	} elseif ($_POST ['submitButton'] == "Event") {
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
			<div class="row">
				<div class="small-12 columns">
					<h4>Payment</h4>
					<p class="text-justify">
						Each competitor must be a member of a Rogaining Association or an Orienteering Club, or must become a member of the NZ Rogaining Association (free), and hereby agrees to abide by the NZRA rules and Code of Conduct.
						These can be found at <a target="_blank" href="https://www.rogaine.org.nz/nzra/about-nzra/">Anout NZRA</a> where information and online membership are available.
						Your attention is drawn particularly to the condition of membership that you enter all events entirely at your own risk.
						All participants will be required to sign an indemnity that neither the Association, the organisers, nor the landowners will be held responsible for any accident or injury which may occur.
					</p>
				</div>
				<hr>
			</div>
			<form id="paymentForm" method="post">
				<div class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Payment</legend>
							<div class="row">
								<div class="medium-6 small-12 columns">
									Your entry will be confirmed by posting it in the <a target="_blank" href="entrants.php">list of entrants</a> on this website once payment has been received.
								</div>
								<div class="medium-6 small-12 columns">
									<label>Total Entry Fees $
										<input type="text" name="payment_value" readonly="readonly" value="<?php echo $payment_value;?>" />
									</label>
								</div>
							</div>
							<div class="row">
								<div class="medium-6 small-12 columns">
									<label>
										<input type="radio" name="payment_type" value="direct credit" <?php echo ($payment_type=='direct credit')? 'checked':''; ?>>
										&nbsp;Direct credit to NZRA Account No. 03 0674 0197735 00.
									</label>
									<div class="error"><?php echo $payment_typeError ?></div>
									<div class="row">
										<div class="small-10 small-centered columns">
											Please use the Team Name as <strong>Particulars</strong> and Team Contact Name as <strong>Reference</strong> to identify your payment.
										</div>
									</div>
								</div>
								<div class="medium-6 small-12 columns">
									<label>
										<input type="radio" name="payment_type" value="cheque" <?php echo ($payment_type=='cheque')? 'checked':''; ?>>
										&nbsp;Cheque payable to New Zealand Rogaining Association.
									</label>
									<div class="error"><?php echo $payment_typeError ?></div>
									<div class="row">
										<div class="small-10 small-centered columns">
											<?php cheque_address($_SESSION['eventId'], $mysqli) ?>
										</div>
									</div>
								</div>
							</div>
						</fieldset>
						<fieldset>
							<legend>Notes</legend>
							<div class="row">
								<div class="small-12 columns">
									<label>If you have any message for the organisers please type it here
										<textarea name="comments" cols="64" rows="4" wrap="soft" maxlength="1024"><?php echo $comments; ?></textarea>
									</label>
								</div>
							</div>
						</fieldset>
						<fieldset>
							<legend>Acceptance</legend>
							<div class="row">
								<div class="small-12 columns">
									<div class="error"><?php echo $waiverError ?></div>
									<input type="checkbox" name="waiver" <?php echo (empty($_POST['waiver'])) ? '':'checked'?> value="Y" />
									&nbsp; I (the Team Contact) undertake to ensure all team members have seen or been fully advised of the written event briefing information distributed to me and available at 
									<a target="_blank" href="https://www.rogaine.org.nz/nzra">rogaine.org.nz</a>
									before the event.
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="row">
					<div class="large-4 medium-6 small-12 columns">
          <?php
										// Entrant count is an array counter so it starts at 0.
										// There must be at least two team members so is must be >= 1
										if ($entrant_count < 1) {
											echo '<span class="error">There must be at least two members in the team</span>';
										} else {
											echo '<input id="enterButton" class="button round" type="submit" name="submitButton" value="Enter">';
										}
										?>
        </div>
					<div class="large-4 medium-6 small-12 columns">
						<input id="anotherEntrantButton" class="button round"
							type="submit" name="submitButton" value="Another Entrant">
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