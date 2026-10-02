<?php
include_once 'includes/functions.php';
sec_session_start ();
if (find_event($mysqli) === false){
	header('location:no_event_error.php');
	exit();
}
$_GET = f_clean ( $_GET );
$status = 'all';
if (isset ( $_SESSION ['status'] )) {
	$status = $_SESSION ['status'];
}
if (isset ( $_GET ['status'] )) {
	$status = $_GET ['status'];
	if ($status != 'all' && $status != 'unpaid') {
		$status = 'all';
	}
	$_SESSION ['status'] = $status;
}
unset ( $_SESSION ['entry_id'] );
unset ( $_SESSION ['member_id'] );
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
		<div class="small-12 large-5 columns">
			<h1><?php $result=get_description($_SESSION['eventId'], $mysqli); echo $result; ?></h1>
			<h2 class="text-center subheader">Administration</h2>
		</div>
		<div class="small-12 large-5 columns">
			<div class="nav-bar right">
				<ul class="button-group">
<?php if ($status == 'all') : ?>
          			<li><a class="button small" href="?status=unpaid">Show Unpaid</a></li>
<?php else : ?>
          			<li><a class="button small" href="?status=all">Show All</a></li>
<?php endif; ?>
		  			<li>
		  				<a href="#" data-dropdown="drop1" class="button small dropdown">Downloads</a>
		  				<ul  id="drop1" data-dropdown-content class="f-dropdown">
		  					<li><a href="admin_download.php">Entrants</a></li>
		  					<li><a href="admin_purchases.php">Purchases</a></li>
		  					<li><a href="admin_navlight.php">Navlight</a></li>
		  					<li><a href="admin_labels.php">Labels</a></li>
		  					<li><a href="membership.php">Members</a></li>
		  				</ul>
		  			</li>
					<li><a class="button small" href="includes/logout.php?id=<?php echo $_SESSION['eventId'];?>">Logout</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="main" class="row">
		<div class="large-12 columns">
<?php if (login_check($mysqli) == true) : ?>
      <table>
				<thead>
					<tr>
						<th><a href="admin_entry.php?id=0">New</a></th>
						<th></th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Payment</th>
						<th>Received</th>
						<th>Paid</th>
						<th>Type</th>
					</tr>
				</thead>
				<tbody>
 <?php 
	$event_id = $_SESSION['eventId'];
	$sql = "SELECT entry.entry_id, entry.sub_event_code, entry.gender_code, entry.age_group_code, entry.team_number, entry.team_name, entry.status, entry.payment_value, entry.payment_received, entry.payment_type, member.first_name, member.last_name, member.email, member.phone, entrant.entry_fee"
			. " FROM event" 
			. " JOIN entrant ON entrant.event_id = event.event_id" 
			. " JOIN entry ON entry.entry_id = entrant.entry_id"
			. " JOIN member ON member.member_id = entrant.member_id" 
			. " WHERE event.event_id = ?"; 
	if ($status == 'unpaid') {
		$sql .= " AND (payment_value != payment_received) AND entry.status NOT IN ('cancel', 'waitList')";
	}
	$sql .= " ORDER BY entry.entry_id, member.member_id";
	if ($stmt = $mysqli->prepare($sql)) {
		$stmt->bind_param('i', $event_id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($entryId, $subEventCode, $genderCode, $ageGroupCode, $teamNumber, $teamName, $status, $paymentValue, $paymentReceived, $paymentType, $firstName, $lastName, $email, $phone, $entryFee);
		$entry_id = 0;
	  while ($stmt->fetch ()) {
			if ($entry_id != $entryId) {
				echo '<tr class="highlight"><td><a href="admin_entry.php?id=' . $entryId . '">Edit</a></td>';
				echo '<td>' . $teamNumber . '</td><td colspan="2">Team: ' . $teamName . '</td><td colspan="2">Grade: ' . $genderCode . $ageGroupCode . ' ' . $subEventCode . '</td><td class="price">$' . $paymentValue . '</td><td class="price">$' . $paymentReceived . '</td><td>' . $status . '</td><td>' . $paymentType . '</td></tr>';
				$entry_id = $entryId;
			}
			echo '<tr class="narrow"><td></td><td></td><td>' . $firstName . '</td><td>' . $lastName . '</td><td>' . $email . '</td><td>' . $phone . '</td><td class="price">$' . $entryFee . '</td><td></td><td></td></tr>';
		}
		$stmt->close();
	}
	?>	
        </tbody>
			</table>
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
<script src="../foundation-5.5.3/js/vendor/jquery.js"></script>
<script src="../foundation-5.5.3/js/foundation.min.js"></script>
<script>
  $(document).foundation();
</script>
</body>
</html>