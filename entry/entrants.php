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
//if (event_open($_SESSION['eventId'], $mysqli) === false){
//	header('location:closed_error.php');
//  exit();
//}
$event_id = $_SESSION['eventId'];
// Check if there is anything on the wait list
$useCountry = FALSE;
$maxEntrants = 2147483647;
$hasWaitList = FALSE;
$sql = "SELECT use_country, max_entrants, count(*) FROM event JOIN entrant ON entrant.event_id = event.event_id"
  . " JOIN entry ON entry.entry_id = entrant.entry_id AND entry.status != 'cancel'"
  . " WHERE event.event_id = ? GROUP BY use_country, max_entrants";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($use_country, $max_entrants, $cur_entrants);
	if ($stmt->num_rows > 0) {
		$stmt->fetch();
		$useCountry = $use_country == 'Y';
		$hasWaitList = $cur_entrants > $max_entrants;
	}
}
$stmt->close();
?>
<!doctype html>
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
	  <h2>List of Entrants</h2>
	  <strong>Entries close <?php echo get_entry_close($event_id, $mysqli);?></strong>.
<?php if ($max_entrants > 0 and $max_entrants < 4096): ?>	    
	  There will be NO late entries. 
	  You will be wait listed if number of entrants exceed <?php echo $max_entrants; ?>.
<?php elseif ($event_id != 36) : ?>
	  Late entries at the discretion of the organizer. There has to be time to finalize catering and map printing etc. Please do not risk being late.
<?php endif; ?>	      
	  <br><br>
	  <div class="row">
	    <div class="medium-4 small-12 columns text-center">
	      <strong>Enter now at <a target="_blank" href="entry.php?eventId=<?php echo $event_id;?>">Event Entry</a>.</strong>
	    </div>
	    <div class="medium-4 small-12 columns text-center">
<?php if ($hasWaitList): ?>	    
	      <strong>Go to the <a href="#waitList">Wait List</a></strong>
<?php endif; ?>	      
	    </div>
	    <div class="medium-4 small-12 columns text-center">
	      <strong>Looking for a partner.<br>Try <a target="_blank" href="find_a_partner.php?eventId=<?php echo $event_id;?>">Find a Partner</a>.</strong>
	    </div>
	  </div>
	</div>
  </div>
	<hr>
</div>
<div id="main" class="row">
  <div class="row">
    <div class="large-5 columns">
	  <p class="panel">This list shows the teams and their members for which entries have been received.</p>
    </div>
 	<div class="large-5 columns">
   	  <p class="panel callout">If you have entered and your name does not appear in this list please contact <a href="mailto:<?php echo get_event_entry_email($event_id, $mysqli);?>">Event Entries</a></p>
 	</div>
    <div class="large-2 columns">
      <!-- <p><a href="https://www.rogaine.org.nz/nzra"><img class="center" src="./img/Logo3_130x118.gif" alt="logo" width="130" height="118"></a></p> -->
	  <a id="loginDropdown" class="button secondary small" data-dropdown="admin_login" aria-controls="admin_login" aria-expanded="false">Administrator Login</a>
	  <div id="admin_login" data-dropdown-content class="f-dropdown content" aria-hidden="true" tabindex="-1">
	    <form action="includes/process_login.php" method="post" name="login_form">
		  <label>Email <input type="text" name="email" id="email" /></label>
		  <label>Password <input type="password" name="password" id="password" /></label> 
		  <input name="eventId" type="hidden" value="<?php echo $event_id;?>" />
		  <input id="loginButton" type="button" value="Login" onclick="formhash(this.form, this.form.password);" />
	    </form>
	  </div>
	</div>
  </div>
  <hr />
  <div class="row">
		  <div class="large-12 columns">
		    <h3 id="entrants">Entrants</h3>
		    <table>
				  <thead>
				    <tr>
					    <th>Grade</th>
					    <th>Team</th>
					    <th>Members</th>
					    <th></th>
				    </tr>
			    </thead>
			    <tbody>
<?php
$sql = "SELECT entry.entry_id, entry.sub_event_code, entry.gender_code, entry.age_group_code, entry.team_number, entry.team_name, entry.status, member.first_name, member.last_name, ifnull(country.country_code, 'xxx')"
		. " FROM event" 
		. " JOIN entrant ON entrant.event_id = event.event_id" 
		. " JOIN entry ON entry.entry_id = entrant.entry_id AND entry.status not in ('cancel', 'waitList')"
		. " JOIN member ON member.member_id = entrant.member_id" 
		. " LEFT OUTER JOIN country ON country.country_id = ucase(replace(member.country,' ', ''))"	
		. " WHERE event.event_id = ?" 
		. " ORDER BY entry.sub_event_code desc, entry.gender_code, entry.age_group_code, entry.team_number, member.member_id";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($entryId, $subEventCode, $genderCode, $ageGroupCode, $teamNumber, $teamName, $status, $firstName, $lastName, $country);
  $entry_id = 0;
  $eventCount = 0;
  $subEventCount = 0;
  $save_subEventCode = "???";
  while ($stmt->fetch() ) {
	  if ($entry_id != $entryId) {
		  if ($entry_id > 0) {
			  echo '</td><td>';
			  //echo '<a href="event_payment.php?eventId='.$event_id.'&entryId='.$entry_id.'">Click here to order event extras</a>';
			  echo '</td></tr>';
			  if ($save_subEventCode != "???" && $save_subEventCode != $subEventCode) {
		  		echo '<tr><td></td><td class="highlight" colspan="2">Total entrants for ' . $save_subEventCode . ': ' . $subEventCount . '</td><td></td></tr>';
		  		$subEventCount = 0;
		  	}
		  }
		  echo '<tr class="top"><td class="highlight">' . $genderCode . $ageGroupCode . ' ' . $subEventCode 
    	  	. '</td><td class="highlight">' . $teamNumber . ' - ' . $teamName
    		. '</td><td>';
    	  $entry_id = $entryId;
    	  $save_subEventCode = $subEventCode;
	  } else {
	  	echo ', ';
	  }
	  echo $firstName . '&nbsp;' . $lastName;
	  if ($useCountry) {
	  	echo ' (' . $country . ')';
	  }
      $eventCount += 1;
      $subEventCount += 1;
  }
  if ($entry_id > 0) {
	  echo '</td><td>';
	  // echo '<a href="event_payment.php?eventId='.$event_id.'&entryId='.$entry_id.'">Click here to order event extras</a>';
	  echo '</td></tr>';
  	if ($save_subEventCode != "???") {
		echo '<tr><td></td><td class="highlight" colspan="2">Total entrants for ' . $save_subEventCode . ': ' . $subEventCount . '</td><td></td></tr>';
		$subEventCount = 0;
	  }
  }
  $stmt->close ();
}
?>
          </tbody>
          <tfoot><tr><td></td><td class="highlight" colspan="2">
          	Total entrants: <?php echo $eventCount?>
          </td><td></td></tr></tfoot>
	      </table>
	  </div>
  </div>
<?php if ($hasWaitList): ?>	    
  <div class="row">
		  <div class="large-12 columns">
		    <h3 id="waitList">Wait List</h3>
		    <table>
				  <thead>
				    <tr>
					    <th>Grade</th>
					    <th>Team</th>
					    <th>Members</th>
					    <th></th>
				    </tr>
			    </thead>
			    <tbody>
<?php
$sql = "SELECT entry.entry_id, entry.sub_event_code, entry.gender_code, entry.age_group_code, entry.team_number, entry.team_name, entry.status, member.first_name, member.last_name, ifnull(country.country_code, 'xxx')"
		. " FROM event" 
		. " JOIN entrant ON entrant.event_id = event.event_id" 
		. " JOIN entry ON entry.entry_id = entrant.entry_id AND entry.status = 'waitList'"
		. " JOIN member ON member.member_id = entrant.member_id" 
		. " LEFT OUTER JOIN country ON country.country_id = ucase(replace(member.country,' ', ''))"	
		. " WHERE event.event_id = ?" 
		. " ORDER BY entry.entry_date";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($entryId, $subEventCode, $genderCode, $ageGroupCode, $teamNumber, $teamName, $status, $firstName, $lastName, $country);
  $entry_id = 0;
  $eventCount = 0;
  $subEventCount = 0;
  $save_subEventCode = "???";
  while ($stmt->fetch() ) {
	  if ($entry_id != $entryId) {
		  if ($entry_id > 0) {
			  echo '</td><td>';
			  //echo '<a href="event_payment.php?eventId='.$event_id.'&entryId='.$entry_id.'">Click here to order event extras</a>';
			  echo '</td></tr>';
			  if ($save_subEventCode != "???" && $save_subEventCode != $subEventCode) {
		  		// echo '<tr><td></td><td class="highlight" colspan="2">Total wait list for ' . $save_subEventCode . ': ' . $subEventCount . '</td><td></td></tr>';
		  		$subEventCount = 0;
		  	}
		  }
		  echo '<tr class="top"><td class="highlight">' . $genderCode . $ageGroupCode . ' ' . $subEventCode 
    	  	. '</td><td class="highlight">' . $teamNumber . ' - ' . $teamName
    		. '</td><td>';
    	  $entry_id = $entryId;
    	  $save_subEventCode = $subEventCode;
	  } else {
	  	echo ', ';
	  }
  	  echo $firstName . '&nbsp;' . $lastName;
	  if ($useCountry) {
	  	echo ' (' . $country . ')';
	  }
	  $eventCount += 1;
      $subEventCount += 1;
  }
  if ($entry_id > 0) {
	  echo '</td><td>';
	  // echo '<a href="event_payment.php?eventId='.$event_id.'&entryId='.$entry_id.'">Click here to order event extras</a>';
	  echo '</td></tr>';
  	if ($save_subEventCode != "???") {
		// echo '<tr><td></td><td class="highlight" colspan="2">Total wait list for ' . $save_subEventCode . ': ' . $subEventCount . '</td><td></td></tr>';
		$subEventCount = 0;
	  }
  }
  $stmt->close ();
}
?>
          </tbody>
          <tfoot><tr><td></td><td class="highlight" colspan="2">
          	Total wait list: <?php echo $eventCount?>
          </td><td></td></tr></tfoot>
	      </table>
	      <strong>Go to the <a href="#entrants">Entrants List</a></strong>
	  </div>
  </div>
<?php endif; ?>	      
</div>
<?php include 'footer.php'?>
<script src="../foundation-5.5.3/js/vendor/jquery.js"></script>
<script src="../foundation-5.5.3/js/foundation.min.js"></script>
<script>
  $(document).foundation();
</script>
</body>
</html>
