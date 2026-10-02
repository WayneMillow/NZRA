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
/*
if (event_open($_SESSION['eventId'], $mysqli) === false){
	header('location:closed_error.php');
	exit();
}
*/
if (isset ($_GET['entryId']) ) {
	$_SESSION['entryId'] = $_GET['entryId'];
}
if (!isset ($_SESSION['eventId']) || !isset ($_SESSION['entryId'])) {
	header('location:woops_error.php');
	exit();
	}
$event_id = $_SESSION['eventId'];
$entry_id = $_SESSION['entryId'];
$team_number = 0; 
$team_name = ''; 
$sub_event = ''; 
$gender = '';
$age_group = '';
$payment_type = '';
$payment_received = 0;
$payment_owed = 0;
$total_fees = 0;
$payment_typeError = '';
$sqlError = '';
$paystationError = '';
$pageError = FALSE;
if (isset($_SESSION['team_number'])) {
	$team_number = $_SESSION['team_number'];
}
if (isset($_SESSION['team_name'])) {
	$team_name = $_SESSION['team_name'];
}
if (isset($_SESSION['payment_type'])) {
	$payment_type = $_SESSION['payment_type'];
}
if (isset($_SESSION['payment_owed'])) {
	$payment_owed = $_SESSION['payment_owed'];
}
if (isset($_SESSION['payment_received'])) {
	$payment_received = $_SESSION['payment_received'];
}
if (isset($_SESSION['total_fees'])) {
	$total_fees = $_SESSION['total_fees'];
}
// Populate fields if we a coming in without a submit
if (empty($_POST['submitButton'])) {
	if ($entry_id > 0) {
		$sql =
			"SELECT team_number, team_name, sub_event.description, gender.description, age_group.description, payment_type, payment_received"
			. " FROM entry"
			. " JOIN event ON event.event_id = ?"
			. " JOIN sub_event ON sub_event.event_id = event.event_id AND sub_event.code = entry.sub_event_code"
			. " JOIN gender ON gender.event_id = event.event_id AND gender.code = entry.gender_code"
			. " JOIN age_group ON age_group.event_id = event.event_id AND age_group.code = entry.age_group_code"
			. " WHERE entry.entry_id = ?";
		if (! ($stmt = $mysqli->prepare($sql))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param( 'ii', $event_id, $entry_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->bind_result($team_number, $team_name, $sub_event, $gender, $age_group, $payment_type, $payment_received))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->fetch())) {
			echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close();
	}
} else {
	if ($_POST['submitButton'] == "Cancel") {
		session_destroy();
		header ( "Location: entrants.php?eventId=" . $event_id );
		exit();
	}
	$payment_owed = str_replace("$", "", $_POST['payment_owed']);
	$payment_received = str_replace("$", "", $_POST['payment_received']);
	$total_fees = str_replace("$", "", $_POST['total_fees']);
	if (empty($_POST['payment_type'])) {
		$payment_typeError = 'Please select a Payment Type';
		
	} else {
		$payment_type = $_POST['payment_type'];
	}
// Go and do it
	if ($pageError === FALSE) {
		$_SESSION['payment_type'] = $payment_type;
		$_SESSION['payment_owed'] = $payment_owed;
		$_SESSION['payment_received'] = $payment_received;
		$_SESSION['total_fees'] = $total_fees;
		if (! ($stmt = $mysqli->prepare("UPDATE entry set payment_type = ?, payment_value = ? WHERE entry_id = ?"))) {
			$sqlError = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (! ($stmt->bind_param('sii', $payment_type, $total_fees, $entry_id ))) {
			$sqlError = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (! ($stmt->execute())) {
			$sqlError = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		$stmt->close();
	}
	if ($pageError === FALSE) {
		// Email entry confirmation
		$team_number = get_team_number($entry_id, $mysqli);
		$description = get_description($event_id, $mysqli);
		$to = $email[0];
		$subject = $description. " Payment for team " . $team_number;
		// Maximum length 70 characters per line
		// 1234567890123456789012345678901234567890123456789012345678901234567890
		$message = 
		  'You are team number ' . $team_number . '.' . "\r\n"
		. 'Please keep a note of this and include it in any communication.' . "\r\n"
		. "\r\n"
		. 'Please pay $NZ' . $payment_owed;
		if ($payment_type == 'direct credit') {
			$message .= "\r\n";
			$sql="SELECT trim(account_name), trim(account_number)" .
				" from event inner join bank_account on bank_account.account_id = event.account_id" .
				" where event_id= ?";
			if ($stmt = $mysqli->prepare ($sql)) {
				$stmt->bind_param ( 'i', $event_id );
				$stmt->execute ();
				$stmt->store_result();
				$stmt->bind_result($accountName, $accountNumber);
				$stmt->fetch();
				$message .= 'to '.$accountName." Account No.\r\n";
				$message .= $accountNumber. "\r\n";
				$stmt->close();
			}
			$message .= 'using your name and your entry number as reference and particulars' . "\r\n"
			. 'to identify your payment.';
		} else if ($payment_type == 'cheque') {
			$message .= "\r\n" 
			. 'Please send your cheque payment' . "\r\n";
			$sql="SELECT trim(description), trim(first_name), trim(last_name), trim(line_1), trim(line_2), trim(line_3), postcode" .
				" from event inner join contact on contact_id = entry_contact_id" .
				" join cheque_address on event.address_id = cheque_address.address_id" .
				" where event_id= ?";
				if ($stmt = $mysqli->prepare ($sql)) {
					$stmt->bind_param ( 'i', $event_id );
					$stmt->execute ();
					$stmt->store_result();
					$stmt->bind_result($description, $firstName, $lastName, $line1, $line2, $line3, $postCode);
					$stmt->fetch();
					$message .= 'to: '.$description."\r\n";
					$message .=  'c/o '. $firstName.' '. $lastName."\r\n";
					$message .=  $line1."\r\n";
					$message .=  $line2."\r\n";
					$message .=  $line3.' '.$postCode."\r\n";
					$stmt->close();
				}
		}
		$headers = 'From: entries@rogaine.org.nz' . "\r\n" 
			. 'Reply-To: entries@rogaine.org.nz' . "\r\n" 
			. 'Bcc: wayne.millow@gmail.com' . "\r\n" 
			. 'X-Mailer: PHP/' . phpversion ();
		mail($to, $subject, $message, $headers);
	}
	if ($pageError === FALSE && $payment_type == 'credit card') {
		$amount	= $payment_owed * 100 ; // Needs to be passed as cents
		$merchantRef = urlencode('NZRA'); //merchant reference is optional, but is a great way to tie a transaction in with a customer (this is displayed in Paystation Administration when looking at transaction details). Max length is 64 char. Make sure you use it!
		$hmacParameters = '';
		$returnUrl = 'http://'.RETURN_HOST.'/entry/paystation_return.php';
		$merchantSession = urlencode(time().'-'.makePaystationSessionID(8,8));
		
		$paystationParameters = "paystation=_empty&pstn_pi=".PAYSTATIONID."&pstn_gi=".GATEWAYID."&pstn_ms=".$merchantSession."&pstn_mr=".urlencode($merchantRef)."&pstn_am=".$amount."&pstn_nr=t";
		if 	(TESTMODE == 'true'){
			$paystationParameters = $paystationParameters."&pstn_tm=t";
		}
		/* Team number and name to customer details */
		$paystationParameters = $paystationParameters."&pstn_mc=".urlencode($team_number.'-'.$team_name);
		/* Event Name to order details */
		$paystationParameters = $paystationParameters."&pstn_mo=".urlencode(get_description($event_id, $mysqli));
		/* Set dynamic return URL */
		$paystationParameters = $paystationParameters."&pstn_du=".urlencode($returnUrl);
		/* If hmacMode is true generate hmacParameters */
		if(HMACMODE){
			$hmacParameters = generateHMACParams($paystationParameters,HMAC_SECURITY_CODE);
		}
		$result = doPostRequest(PAYSTATIONURL, $paystationParameters, HMACMODE, $hmacParameters);
		/*Now we grab the details from the request we have just made - NOTE: it is best to store everything you can about a transaction - if you ever have to debug you will understand!
		 */
		$xmlData = new SimpleXMLElement($result);
		$digitalOrder = $xmlData->DigitalOrder; // The URL that we re-direct the customer too.
		$transactionID =  $xmlData->PaystationTransactionID;  //The transaction ID Paystation has just created.
		$paymentRequestTime =  $xmlData->PaymentRequestTime; // The time that the transaction was initiated
		$digitalOrderTime =  $xmlData->DigitalOrderTime;  //The time Paystation responds
		// Add the entry
		if (!($stmt = $mysqli->prepare("INSERT INTO entry_credit_card(transaction_id, entry_id, payment_value, payment_request_time, digital_order_time) VALUES (?, ?, ?, ?, ?)" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (! ($stmt->bind_param ( 'siiss', $transactionID, $entry_id, $payment_owed, $paymentRequestTime, $digitalOrderTime ))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (! ($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		$stmt->close();
		// Redirect
		if ($digitalOrder) {
			header("Location:".$digitalOrder); //Successful initiation, so redirect to page given in digitalorder variable
			exit();
		} else {
			$paystationError .= "<pre>".htmlentities($result)."</pre>"; //no digitalorder variable, so initiation must have failed.  Print out xml packet for debugging purposes
			$pageError = TRUE;
		}
	}
	if ($pageError === FALSE) {
	  //And go to look at the entrants
	  session_destroy ();
	  header ( "Location: entrants.php?eventId=" . $event_id );
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
  <hr>
</div>
<div id="main" class="row">
  <div class="row">
	<div class="large-12 columns text-center">
	  <strong>Team: <?php echo $team_number;?> - <?php echo $team_name;?> - <?php echo $sub_event;?> <?php echo $gender;?> <?php echo $age_group;?></strong>
	  <hr>
	</div>
  </div>	
  <div class="row">
	<div class="medium 12 large-6 columns">
      <table>
        <thead>
          <tr>
            <th>Entrants</th>
            <th>Fees</th>
          </tr>
        </thead>
        <tbody>
<?php
$total_fees = 0;
$subtotal = 0;
$sql = "SELECT member.first_name, member.last_name, entrant.entry_fee"
		. " FROM entrant" 
		. " JOIN member ON member.member_id = entrant.member_id" 
		. " WHERE entrant.event_id = ? AND entrant.entry_id = ?"; 
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('ii', $event_id, $entry_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($firstName, $lastName, $entry_fee);
  while ($stmt->fetch() ) {
	echo '<tr>';
	  echo '<td>' . $firstName . '&nbsp;' . $lastName . '</td>';
	  echo '<td>$' . $entry_fee . '</td>';
	echo '</tr>';
	$subtotal += $entry_fee;  
  }
  $stmt->close();
}
$total_fees += $subtotal;
?>
        </tbody>
        <tfoot>
          <tr>
            <td>Sub total:</td>
            <td>$<?php echo $subtotal; ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
	<div class="medium 12 large-6 columns">
<?php 
$subtotal = 0;
$sql = "SELECT description, quantity, cost"
		. " FROM entry_extra" 
		. " JOIN extra ON extra.event_id = ? AND extra.code = entry_extra.code"  
		. " WHERE entry_extra.entry_id = ?"; 
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('ii', $event_id, $entry_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($description, $quantity, $cost);
  if ($stmt->num_rows > 0) {
	echo '<table>';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Extras</th>';
	echo '<th>Number</th>';
	echo '<th>Cost</th>';
	echo '<th>Total</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
  	while ($stmt->fetch() ) {
	  echo '<tr>';
	    echo '<td>' . $description . '</td>';
	    echo '<td>' . $quantity . '</td>';
	    echo '<td>' . $cost . '</td>';
	    echo '<td>$' . $quantity * $cost . '</td>';
	  echo '</tr>';
	  $subtotal += $quantity * $cost;
    }
	echo '</tbody>';
	echo '<tfoot>';
	echo '<tr>';
	echo '<td></td><td colspan=2>Sub total:</td>';
	echo '<td>$' . $subtotal .'</td>';
	echo '</tr>';
	echo '</tfoot>';
	echo '</table>';
  }
  $stmt->close();
}
$total_fees += $subtotal;
?>
	</div>
  </div>
  <div class="row">
	<div class="small-12 columns">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<?php if ($pageError === TRUE) : ?>
		<div class="row">
			<div class="error">
				Errors exist! Please check the form for the details, correct them and press "Make Payment" again.
				<?php if ($sqlError) echo $sqlError;?>
				<?php if ($paystationError) echo $paystationError;?>
			</div>	
		</div>			
<?php endif; ?>				
		<div class="row">
		  <div class="large-3 medium-6 small-12 columns">
			<label>Payment Received NZ$
			  <input type="text" id="payment_received" name="payment_received" readonly="readonly" value="$<?php echo $payment_received?>" />
			</label>
		  </div>
		  <div class="large-3 medium-6 small-12 columns">
			<label>Total Fees NZ$
			  <input type="text" id="total_fees" name="total_fees" readonly="readonly" value="$<?php echo $total_fees?>" />
			</label>
		  </div>
		  <div class="large-3 medium-6 small-12 columns">
		    <?php $payment_owed = $total_fees - $payment_received; ?>
			<label><strong>Payment Owed NZ$</strong>
			  <input type="text" id="payment_owed" name="payment_owed" readonly="readonly" value="$<?php echo $payment_owed?>" />
			</label>
		  </div>
		  <div class="large-3 medium-6 small-12 columns">
			If you have any queries please contact <a href="mailto:entries@rogaine.org.nz">entries@rogaine.org.nz</a>. 
		  </div>
		</div>
        <fieldset>
		  <legend>Payment</legend>
			<div class="row">
			  <div class="large-4 medium-6 small-12 columns">
				<label>
				  <input type="radio" name="payment_type" value="credit card" <?php echo ($payment_type=='credit card')?' checked':''?> >&nbsp;Credit Card
				</label>
				<div class="error"><?php echo $payment_typeError; ?></div>
				<div class="row">
				  <div class="small-12 small-centered columns">
					<p><strong>Card Details</strong></p>
					<div class="panel dehighlight">
					  <h4><small>Payment Terms and Conditions</small></h4>
	                  By entering and making payment you agree to these terms and conditions.<br>		
					  The entry fee shown is in NZ$. Once payment is made the entry can not be cancelled however a manual process may be negotiated.
					  The details for this entry will only be used for the administration of this event and will not be shared with any outside party.
					  For any queries please contact:
<?php
$sql = "SELECT trim(first_name), trim(last_name), trim(email), trim(phone), trim(line_1), trim(line_2), trim(line_3), postcode"
		. " FROM event JOIN contact ON contact.contact_id = event.entry_contact_id"
		. " JOIN cheque_address ON cheque_address.address_id = event.address_id"
		. " WHERE event.event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($firstName, $lastName, $emailAddr, $phone, $line1, $line2, $line3, $postCode);
	$stmt->fetch();
	if ($stmt->num_rows > 0) {
		echo $firstName.' '. $lastName.'<br>';
		echo $line1.', ';
		echo $line2.', ';
		echo $line3.' '.$postCode.'<br>';
		if (isset($phone) && strlen(trim($phone)) > 0) {
  		  echo 'Phone: '.$phone.'<br>';
		}
		echo 'Email: <a href="mailto:'.$emailAddr.'">'.$emailAddr.'</a>';
	}
	$stmt->close();
}
?>
			        </div>
			        <img src="img/paystation_logo.jpg" alt="Paystation Logo">
			        <!-- <p>Test credit cards can be found <a href="http://paystation.co.nz/test-card-numbers" target="_blank">here</a></p> -->
			      </div>
		        </div>
	          </div>
	          <div class="large-4 medium-6 small-12 columns">
		        <label>
		          <input type="radio" name="payment_type" value="direct credit" <?php echo ($payment_type=='direct credit')?' checked':''?> >
		          &nbsp;Direct credit
		        </label>
		        <div class="error"><?php echo $payment_typeError; ?></div>
		        <div class="row">
			      <div class="small-12 small-centered columns">
			        
<?php 			        
$sql="SELECT trim(account_name), trim(account_number)" .
	" from event inner join bank_account on bank_account.account_id = event.account_id" .
	" where event_id= ?";
if ($stmt = $mysqli->prepare ($sql)) {
	$stmt->bind_param ( 'i', $event_id );
	$stmt->execute ();
	$stmt->store_result();
	$stmt->bind_result($accountName, $accountNumber);
	$stmt->fetch();
	echo '<p><strong>To ' . $accountName. ' Account No.<br>' . $accountNumber. '</strong></p>';	
	$stmt->close();
}
?>			
			        <div class="panel">
				      Please use the Team Name as <strong>Particulars</strong> and Team Contact Name as <strong>Reference</strong> to identify your payment.
			        </div>
			      </div>
		        </div>
	          </div>
	          <div class="large-4 medium-6 small-12 columns">
		        <label>
		          <input type="radio" name="payment_type" value="cheque" <?php echo ($payment_type=='cheque')?' checked':''?> >
		          &nbsp;Cheque
		        </label>
		        <div class="error"><?php echo $payment_typeError; ?></div>
				<div class="row">
				  <div class="small-12 small-centered columns">
<?php
$sql = "SELECT trim(description), trim(first_name), trim(last_name), trim(email), trim(line_1), trim(line_2), trim(line_3), postcode, payable_to"
		. " FROM event JOIN contact ON contact.contact_id = event.entry_contact_id"
		. " JOIN cheque_address ON cheque_address.address_id = event.address_id"
		. " WHERE event.event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($description, $firstName, $lastName, $emailAddr, $line1, $line2, $line3, $postCode, $payableTo);
	$stmt->fetch();
	if ($stmt->num_rows > 0) {
		echo '<p><strong>Payable to<br>'. $payableTo. '</strong></p>';
		echo '<div class="panel">';
		echo 'Send to:<br>'.$description.'<br>';
		echo 'c/o '. $firstName.' '. $lastName.'<br>';
		echo $line1.'<br>';
		echo $line2.'<br>';
		echo $line3.' '.$postCode.'<br>';
		echo 'Email: <a href="mailto:'.$emailAddr.'">'.$emailAddr.'</a>';
		echo '</div>'; 
	}
	$stmt->close();
}
?>		
			      </div>
			    </div>
			  </div>
			</div>
	        <div class="row">
		      <div class="small-12 columns">
		        <div class="row">
                  <div class="small-12 columns">
  			        <p class="text-center">When you are happy that you have the correct entry details, press <strong>Enter</strong>.<br>
				      <strong>Please wait while your entry processes.</strong> This may take a few seconds.<br>
			          If you have any queries please contact <a href="mailto:<?php echo $emailAddr;?>"><?php echo $emailAddr;?></a></p>
			      </div>
		        </div>
		        <div class="row">
			      <div class="large-4 medium-6 small-12 columns">
			      	<div id="showSubmit" <?php if ($payment_owed == 0) :?>style="display: none;"<?php endif; ?>>
			          <input id="enterButton" class="button success" type="submit" name="submitButton" value="Make Payment">
			        </div>
		          </div>
			      <div class="large-4 medium-6 small-12 columns">
			        <div id="formErrors" class="error">
<?php if ($pageError === TRUE) : ?>Errors exist! Please check the form for the details, correct them and press "Make Payment" again.<?php endif; ?>
			        </div>				
		          </div>
			      <div class="large-4 medium-6 small-12 columns">
			        <input id="cancelButton" class="button info" type="submit" name="submitButton" value="Cancel">
			      </div>
		        </div>
              </div>
            </div>
		  </fieldset>
	    </form>
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