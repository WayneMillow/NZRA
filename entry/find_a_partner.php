<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
sec_session_start ();
// Reset the session variable if we come to this page with the query string ?eventId=
if (isset ($_GET['eventId']) ) {
	$_SESSION['eventId'] = $_GET['eventId'];
}
if (find_event($mysqli) === false){
	header('location:no_event_error.php');
	exit();
}
if (event_open($_SESSION['eventId'], $mysqli) === false){
	header('location:closed_error.php');
  exit();
}
$event_id = $_SESSION['eventId'];
$full_name = '';
$contact_email = '';
$gender = '';
$age_group = '';
$experience = '';
$expectations = '';
$full_nameError = '';
$contact_emailError = '';
$genderError = '';
$age_groupError = '';
$experienceError = '';
$expectationsError = '';
$sqlError = '';
$pageError = FALSE;
if (isset($_SESSION['full_name'])) {
	$full_name = $_SESSION['full_name'];
}
if (isset($_SESSION['contact_email'])) {
	$contact_email = $_SESSION['contact_email'];
}
if (isset($_SESSION['gender'])) {
	$gender = $_SESSION['gender'];
}
if (isset($_SESSION['age_group'])) {
	$age_group = $_SESSION['age_group'];
}
if (isset($_SESSION['experience'])) {
	$experience = $_SESSION['experience'];
}
if (isset($_SESSION['expectations'])) {
	$expectations = $_SESSION['expectations'];
}
// If this form submitted the data then validate it
if (!empty($_POST['submitButton'])) {
	$full_name = strip_tags(trim($_POST['full_name']));
	if (empty($full_name) || strlen($full_name) < 2 || strlen($full_name) > 32){
		$full_nameError = 'Please enter a Full Name between 2 and 32 characters';
		$pageError = TRUE;
	}
	$contact_email = strip_tags(trim($_POST['contact_email']));
	if (empty($contact_email)){
		$contact_emailError = 'Please enter a Contact Email address';
		$pageError = TRUE;
	} elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
		$contact_emailError = "Please enter a valid email address";
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
	} else {
		$age_group = $_POST['age_group'];
	}
	$experience = strip_tags(trim($_POST['experience']));
	if (empty($experience) || strlen($experience) < 2 || strlen($experience) > 64){
		$experienceError = 'Please key experience between 2 and 64 characters';
		$pageError = TRUE;
	}
	$expectations = strip_tags(trim($_POST['expectations']));
	if (empty($expectations) || strlen($expectations) < 2 || strlen($expectations) > 64){
		$expectationsError = 'Please key expectations between 2 and 64 characters';
		$pageError = TRUE;
	}
	$_SESSION['full_name'] = $full_name;
	$_SESSION['contact_email'] = $contact_email;
	$_SESSION['gender'] = $gender;
	$_SESSION['age_group'] = $age_group;
	$_SESSION['experience'] = $experience;
	$_SESSION['expectations'] = $expectations;
	if ($pageError === FALSE) {
		if (!($stmt = $mysqli->prepare("INSERT INTO find_a_partner(event_id, full_name, contact_email, gender_code, age_group_code, experience, expectations) VALUES (?, ?, ?, ?, ?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (! ($stmt->bind_param ('issssss', $event_id, $full_name, $contact_email, $gender, $age_group, $experience, $expectations))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (! ($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
	}
	if ($pageError === FALSE) {
	  session_destroy ();
	  header ( "Location: find_a_partner.php?eventId=" . $event_id );
	  exit();
	}
}
?>
<html class="no-js" lang="en">
<?php include 'head.php'?>
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
      <div class="error">This is a test site only. All entries displayed here are fictitious as is the event itself.</div>
    </div>
  </div>
 -->  
  <div class="row">
	<div class="large-12 columns">
	  <strong>Entries close <?php echo get_entry_close($event_id, $mysqli);?></strong>. Late entries at the discretion of the organizer.
	  <br><br> 
	  <strong>Enter now at <a target="_blank" href="entry.php?eventId=<?php echo $event_id;?>">Event Entry</a>.</strong>
	</div>
  </div>
	<hr>
</div>
<div id="main" class="row">
    <div class="row">
	  <div class="large-12 columns">
	  	<fieldset>
		  	<legend>Partners Wanted</legend>
		    <table>
				<thead>
					<tr>
						<th>Name</th>
						<th>Contact</th>
					    <th>Gender</th>
					    <th>Age Group</th>
					    <th>Experience</th>
						<th>Expectations</th>
				    </tr>
			    </thead>
			    <tbody>
<?php
$sql =
	"SELECT full_name, contact_email, gender.description, age_group.description, experience, expectations" .
	" FROM find_a_partner" .
	" JOIN gender ON gender.event_id = find_a_partner.event_id AND gender.code = find_a_partner.gender_code" .
	" JOIN age_group ON age_group.event_id = find_a_partner.event_id AND age_group.code = find_a_partner.age_group_code" .
	" WHERE find_a_partner.event_id = ?" .
	" AND find_a_partner.status = ''";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows() > 0) {
		$stmt->bind_result($full_name1, $contact_email1, $gender1, $age_group1, $experience1, $expectations1);
		while ($stmt->fetch()){
		echo '<tr>';
			echo '<td>' . $full_name1 . '</td>';
			echo '<td><a target=_top href="mailto:' . $contact_email1 . '">' . $contact_email1 . '</a></td>';
			echo '<td>' . $gender1 . '</td>';
			echo '<td>' . $age_group1 . '</td>';
			echo '<td>' . $experience1 . '</td>';
			echo '<td>' . $expectations1 . '</td>';
		echo '</tr>';
		}
	}
	$stmt->close();
}
?>			    	
			    </tbody>
			</table>
			<p>Email <a target="_top" href="mailto:entries@rogaine.org.nz?subject=FindAPartner">entries@rogaine.org.nz</a> with your name to remove yourself from this list</p>
		</fieldset>
	  </div>
	</div>			   
    <div class="row">
	  <div class="large-12 columns">
	 	<fieldset>
	 		<legend>Find a Partner</legend>
	 		<form method="post" action="#">
	 			<div class="row">
	 				<div class="small-12 large-6 columns">
	 					<label>Full Name<input type="text" name="full_name" maxlength="32" value="<?php echo $full_name;?>"></label>
	 					<div class="error"><?php echo $full_nameError; ?></div>
	 					<label>Contact Email<input type="text" name="contact_email" maxlength="64" value="<?php echo $contact_email;?>"></label>
	 					<div class="error"><?php echo $contact_emailError; ?></div>
	 				</div>
	 				<div class="small-6 large-3 columns">
						<h4><small>Gender</small></h4>
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
	 				<div class="small-6 large-3 columns">
									<h4><small>Age Group</small></h4>
									<div class="error"><?php echo $age_groupError; ?></div>
<?php 
$sql = "SELECT code, description, lower_age, upper_age FROM age_group WHERE event_id = ? ORDER BY lower_age";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($code, $description, $lower, $upper);
	while ($stmt->fetch()) {
	echo '<label><input type="radio" name="age_group" value="'.$code.'"';
		echo ($age_group==$code) ? ' checked' : '';
		echo ' />&nbsp;' .$description.' ('.$lower.'-'.$upper.')</label>';
	}
	$stmt->close();
}
?>									
	 				</div>
	 			</div>
	 			<div class="row">
	 				<div class="small-12 columns">
	 					<label>Experience<input type="text" name="experience" maxlength="64" value="<?php echo $experience;?>"></label>
	 					<div class="error"><?php echo $experienceError; ?></div>
	 				</div>
	 			</div>
	 			<div class="row">
	 				<div class="small-12 columns">
	 					<label>Expectations<input type="text" name="expectations" maxlength="64" value="<?php echo $expectations;?>"></label>
	 					<div class="error"><?php echo $expectationsError; ?></div>
	 				</div>
	 			</div>
	 			<div class="row">
	 				<div class="small-3 small-centered columns">
	 					<input class="button success" type="submit" name="submitButton" value="Add">
	 					<div class="error"><?php echo $sqlError; ?></div>
	 				</div>
	 			</div>
	 		</form>
	 	</fieldset>
	  </div>
	</div>
</div>
<?php include 'footer.php'?>
<script src="../foundation-5.5.3/js/vendor/jquery.js"></script>
<script src="../foundation-5.5.3/js/foundation.min.js"></script>
<script>
  $(document).foundation();
</script>
</body>
</html>