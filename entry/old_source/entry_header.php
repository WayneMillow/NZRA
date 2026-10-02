<?php
include_once '../includes/functions.php';
sec_session_start ();
// Reset the session variable if we come to this page with the query string ?eventId=
if (isset ($_GET['eventId']) ) {
	$_SESSION['eventId'] = $_GET['eventId'];
}
if (find_event($mysqli) === false){
	header('location:no_event_error.php');
}
if (event_open($_SESSION['eventId'], $mysqli) === false){
	header('location:closed_error.php');
}
$_POST = f_clean ( $_POST );
// Set up variables to hold the form fields and errors
$team_name = '';
$gender = '';
$age_group = '';
$event = '';
$team_nameError = '';
$genderError = '';
$age_groupError = '';
$eventError = '';
$pageError = false;
// If we come back to this page after setting the session variables use them
if (isset ( $_SESSION['team_name'])){
	$team_name = $_SESSION['team_name'];
}
if (isset ( $_SESSION ['gender'] )) {
	$gender = $_SESSION ['gender'];
}
if (isset ( $_SESSION ['event'] )) {
	$event = $_SESSION ['event'];
}
if (isset ( $_SESSION ['age_group'] )) {
	$age_group = $_SESSION ['age_group'];
}
// If this form submitted the data then validate it
if (! empty ( $_POST ['submitButton'] )) {
	// If the user wants to go back to
	if ($_POST ['submitButton'] == "Cancel") {
		session_destroy ();
		header ( 'Location: ../index.html' );
		exit ();
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
	// If no errors go to the page submitted
	if ($pageError === FALSE) {
		$_SESSION ['team_name'] = $team_name;
		$_SESSION ['gender'] = $gender;
		$_SESSION ['age_group'] = $age_group;
		$_SESSION ['event'] = $event;
		if ($_POST ['submitButton'] == "Payment") {
			header ( 'Location: entry_payment.php' );
		} elseif ($_POST ['submitButton'] == "Add Entrant") {
			header ( 'Location: entry_entrant.php' );
		}
	}
}
if ($_SESSION['eventId'] == 5) {
	$junior = 'U18';
} else {
	$junior = 'U23';
}?>
<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
<?php include '../head.php'?>
<body>
<?php include '../header.php'?>
<div id="main" class="row">
		<div class="small-12 columns">
			<div class="row">
				<?php display_contacts($_SESSION['eventId'], $mysqli); display_pricing($_SESSION['eventId'], $mysqli)?>
				<div class="large-12 columns">
					<strong>Entries close <?php echo get_entry_close($_SESSION['eventId'], $mysqli);?></strong>.<br> 
					For current numbers see the <a target="_blank" href="entrants.php">List of Entrants</a>.
					Late entries at the discretion of the organizer.
				</div>
				<hr>
			</div>
			<form id="headerForm" method="post"
				action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<div class="row">
					<div class="small-12 columns">
						<fieldset>
							<legend>Team Category (all team members must comply)</legend>
							<div class="row">
								<div class="small-12 columns">
									<label>
										Team Name
										<input type="text" name="team_name" maxlength="32" value="<?php echo $team_name;?>">
									</label>
									<div class="error"><?php echo $team_nameError; ?></div>
								</div>
							</div>
							<div class="row">
								<div class="large-4 medium-6 small-12 columns">
									<h4>
										<small>Gender</small>
									</h4>
									<div class="error"><?php echo $genderError; ?></div>
									<label><input type="radio" name="gender" value="M"
										<?php echo ($gender=='M')?'checked':''?> />&nbsp;Men</label> <label><input
										type="radio" name="gender" value="W"
										<?php echo ($gender=='W')?'checked':''?> />&nbsp;Women</label>
									<label><input type="radio" name="gender" value="X"
										<?php echo ($gender=='X')?'checked':''?> />&nbsp;miXed</label>
									<?php if ($_SESSION['eventId'] == 5) echo 'Gender applies only to the Open Age Group category' ?>	
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<h4>
										<small>Age Group</small>
									</h4>
									<div class="error"><?php echo $age_groupError; ?></div>
									<label><input type="radio" name="age_group" value="J"
										<?php echo ($age_group=='J')?'checked':''?> />&nbsp;<?php echo $junior ?></label>
									<label><input type="radio" name="age_group" value="O"
										<?php echo ($age_group=='O')?'checked':''?> />&nbsp;Open</label>
									<label><input type="radio" name="age_group" value="V"
										<?php echo ($age_group=='V')?'checked':''?> />
											&nbsp;Vet (<?php if ($_SESSION['eventId'] == 5) echo '50+'; else echo '40+'?>)</label>
									<?php if ($_SESSION['eventId'] == 5) : ?>	
									U18 team members must be at least 14 to enter the 6 hour event
									<?php else : ?>
									<label><input type="radio" name="age_group" value="SV"
										<?php echo ($age_group=='SV')?'checked':''?> />&nbsp;SuperVet
										(55+)</label>
									<?php endif; ?>
								</div>
								<div class="large-4 medium-6 small-12 columns">
									<h4>
										<small>Event</small>
									</h4>
									<div class="error"><?php echo $eventError; ?></div>
									<?php sub_event_radio_buttons($_SESSION['eventId'], $mysqli, $event)?>
									<p>(Please - no switching between events after you have
										entered)</p>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="row">
					<div class="large-4 medium-6 small-12 columns">
						<input id="cancelButton" class="button round" type="submit"
							name="submitButton" value="Cancel">
					</div>
					<div class="large-4 medium-6 small-12 columns">
						<input id="addEntrantButton" class="button round" type="submit"
							name="submitButton" value="Add Entrant">
					</div>
					<div class="large-4 medium-6 small-12 columns">
						<input id="paymentButton" class="button round" type="submit"
							name="submitButton" value="Payment">
					</div>
				</div>
			</form>
		</div>
	</div>
<?php include '../footer.php'?>
</body>
</html>
