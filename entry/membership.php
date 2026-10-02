<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
sec_session_start ();
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
    <h1>NZRA Membership</h1>
    <h2 class="subheader"><?php echo 'As at: ' . date('jS M Y');?></h2>
  </div>
</div>
<div id="main" class="row">
  <div class="row">
      <div class="large-12 columns">
<?php if (login_check($mysqli) == true) : ?>
        <table>
    		  <thead>
    		    <tr>
    			    <th>Last Name</th>
    			    <th>First Name</th>
    			    <th>Email</th>
    			    <th>Phone</th>
    			    <th>Address</th>
    			    <th>Entry Date</th>
    		    </tr>
    	    </thead>
    	    <tbody>
<?php
$sql = "SELECT member.last_name, member.first_name, member.email, member.phone, concat_ws(' ', member.street, member.town), entry.entry_date"
		. " FROM member" 
		. " JOIN entrant ON entrant.member_id = member.member_id" 
		. " JOIN entry ON entry.entry_id = entrant.entry_id"
		. " ORDER BY member.last_name, member.first_name, entry.entry_date desc";
$oldLastName = 'Z';
$oldFirstName = 'Z';
$oldEmail = 'Z';
$oldPhone = '9';
$oldAddress = 'Z';
$weight = 0;
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($lastName, $firstName, $email, $phone, $address, $entryDate);
    while ($stmt->fetch() ) {
        if ($oldEmail == strtolower($email)) { $weight += 1;}
      if ($oldPhone == $phone) { $weight += 1;}
      if ($oldAddress == strtolower($address)) { $weight += 1;}
      if ($oldLastName == strtolower($lastName) && $oldFirstName == strtolower($firstName) && $weight > 1) { continue;}
      echo '<tr>';
	  echo '<td>'. $lastName. '</td>';
	  echo '<td>'. $firstName. '</td>';
	  echo '<td>'. $email. '</td>';
	  echo '<td>'. $phone. '</td>';
	  echo '<td>'. $address. '</td>';
	  echo '<td>'. date("Y-m-d", strtotime($entryDate)). '</td>';
	  echo '</tr>';
	  $oldLastName = strtolower($lastName);
	  $oldFirstName = strtolower($firstName);
	  $oldEmail = strtolower($email);
	  $oldPhone = $phone;
	  $oldAddress = strtolower($address);
	  $weight = 0;
    }
    $stmt->close ();
}
?>
			</tbody>
		</table>
<?php else : ?>
        <p>You are not authorized to access this page.</p>
<?php endif; ?>
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
