<?php
include_once 'includes/functions.php';
sec_session_start ();
if (find_event($mysqli) === false){
    header('Location: no_event_error.php');
    exit();
}
// Set up variables to hold the form fields and errors
if (isset ( $_SESSION ['eventId'] )) {
    $event_id = $_SESSION ['eventId'];
}
$sql = "select trim(account_name), trim(account_number)" .
    " from event inner join bank_account on bank_account.account_id = event.account_id" .
    " where event_id= ?";
if ($stmt = $mysqli->prepare ($sql)) {
    $stmt->bind_param ( 'i', $event_id );
    $stmt->execute ();
    $stmt->store_result();
    $stmt->bind_result($accountName, $accountNumber);
    $stmt->fetch();
    $stmt->close();
}
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
      <h1>Payment Reminder</h1>
    </div>
    <hr />
  </div>
  <div id="main" class="row">
<?php if (login_check($mysqli) == true) : ?>
    <div class="large-12 columns">

<!-- and entry.entry_date < date_sub(now(), INTERVAL 21 day) -->    
<?php 
$sql = "select trim(description), team_number, trim(team_name), entry_date, payment_value, payment_received, trim(first_name), trim(last_name), trim(email)" .
    " from event" .
    " join entrant on entrant.event_id = event.event_id" .
    " join entry on entry.entry_id = entrant.entry_id and status not in ('cancel', 'waitlist')" .
    " and entry.entry_date < date_sub(now(), INTERVAL 14 day)" .
    " and payment_received < payment_value" .
    " join member on member.member_id = entrant.member_id" .
    " where event.event_id = ? order by team_number";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $event_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($description, $team_number, $team_name, $entry_date, $payment_value, $payment_received, $first_name, $last_name, $email);
  $oldTeamNumber = 99999;
  $to = "";
  $greeting = "";
  while ($stmt->fetch()) {
      $phpdate = strtotime($entry_date);
      // Send an email
      if ($oldTeamNumber != $team_number) {
          if ($oldTeamNumber != 99999) {
              if (mail($to, $subject, $message, $headers)) {
                  echo "<p>Mail to " . $to . " successful</p>";
              } else {
                  echo "<p>Mail to " . $to . " FAILED</p>";
              }
              $to = "";
              $greeting = "";
          }
          $oldTeamNumber = $team_number;
      }
      // Email entry confirmation
      if ($to == "") {
          $to = $email;
      } else {
          $to .= ", " . $email;
      }
      if ($greeting == "") {
          $greeting = $first_name;
      } else {
          $greeting .= ", " . $first_name;
      }
      $subject = $description. " Entry - Team " . $team_number;
      // Maximum length 70 characters per line
      // 1234567890123456789012345678901234567890123456789012345678901234567890
      $message = "Hi " . $greeting . ", You entered " . $description . " on " . date("d/m/Y", $phpdate) . "\r\n";
      if ($payment_received === 0) {
          $message .= "Our records show that your team haven't paid the entry fee of $" . $payment_value . ".";
      } else {
          $message .= "Our records show that your team have only paid $" . $payment_received . " of the $" . $payment_value . " entry fee.";
      }
      //           1234567890123456789012345678901234567890123456789012345678901234567890
      $payment_owed = $payment_value - $payment_received;
      //$message .= "\r\n" . "We have people on a wait list." .
      //    " If we do not receive payment your entry will be moved to this list." . "\r\n" .
      //    "To avoid this please pay $" . $payment_owed . " to " . $accountName. " Account No. " . $accountNumber .
     $message .= "\r\n" . "Please pay $" . $payment_owed . " to " . $accountName. " Account No. " . $accountNumber .
      " using your team name and your entry number as reference and particulars to identify your payment." . "\r\n" .
      "If you have recently paid we apologise and please ignore this message.";
      $message = wordwrap($message, 70, "\r\n");
      $headers = "From: entries@rogaine.org.nz" . "\r\n" .
          "Reply-To: entries@rogaine.org.nz" . "\r\n" .
          "Bcc: wayne.millow@gmail.com" . "\r\n" .
          "X-Mailer: PHP/" . phpversion ();
  }
  if ($oldTeamNumber != 99999) {
      if (mail($to, $subject, $message, $headers)) {
          echo "<p>Mail to " . $to . " successful</p>";
      } else {
          echo "<p>Mail to " . $to . " FAILED</p>";
      }
      $to = "";
  }
  $stmt->close();
} else {
  echo "<p>Prepare failed with error " . $mysqli->error . "</p>";
}
?>    
    </div>
    <hr />
<?php else : ?>
        <p>You are not authorized to access this page.</p>
			<p>Please return to the list of entrants.</p>
			<p>
				<a class="button" href="includes/logout.php?id=<?php echo $_SESSION['eventId'];?>">Entrants</a>
			</p>
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
