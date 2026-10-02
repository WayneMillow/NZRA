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
if (isset ($_GET['entryId']) ) {
	$_SESSION['entryId'] = $_GET['entryId'];
}
$event_id = $_SESSION['eventId'];
$entry_id = $_SESSION['entryId'];
$numberOfExtra = getNumberOfExtra($event_id, $mysqli);
$team_number = 0; 
$team_name = ''; 
$sub_event = ''; 
$gender = '';
$age_group = '';
$extraCode = array_fill(0, $numberOfExtra, '');
$extraValue = array_fill(0, $numberOfExtra, 0);
$extraQuantity = array_fill(0, $numberOfExtra, 0);
$savedQuantity = array_fill(0, $numberOfExtra, 0);
$payment_type = '';
$payment_value = 0;
$payment_received = 0;
$payment_owed = 0;
$entry_fees = 0;
$extraError = array_fill(0, $numberOfExtra, '');
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
if (isset($_SESSION['extraCode'])) {
	$extraCode = $_SESSION['extraCode'];
}
if (isset($_SESSION['extraValue'])) {
	$extraValue = $_SESSION['extraValue'];
}
if (isset($_SESSION['extraQuantity'])) {
	$extraQuantity = $_SESSION['extraQuantity'];
}
if (isset($_SESSION['savedQuantity'])) {
	$savedQuantity = $_SESSION['savedQuantity'];
}
if (isset($_SESSION['payment_type'])) {
	$payment_type = $_SESSION['payment_type'];
}
if (isset($_SESSION['payment_owed'])) {
	$payment_owed = $_SESSION['payment_owed'];
}
if (isset($_SESSION['entry_fees'])) {
	$entry_fees = $_SESSION['entry_fees'];
}
// Populate fields if we a coming in without a submit
if (empty($_POST['submitButton'])) {
	if ($entry_id > 0) {
		$sql =
			"SELECT team_number, team_name, sub_event.description, gender.description, age_group.description, payment_type, payment_value, payment_received"
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
		if (! ($stmt->bind_result($team_number, $team_name, $sub_event, $gender, $age_group, $payment_type, $payment_value, $payment_received))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->fetch())) {
			echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close();
		// $payment_owed = $payment_value - $payment_received;
		$_SESSION['team_number'] = $team_number;
		$_SESSION['team_name'] = $team_name;
		// Calculate entry fees
		$sql = "SELECT SUM(entry_fee) FROM entrant WHERE entrant.event_id = ? AND entrant.entry_id = ?";
		if (! ($stmt = $mysqli->prepare($sql))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param( 'ii', $event_id, $entry_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->bind_result($entry_fees))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->fetch())) {
			echo "Fetch failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close();
		// Set up any extras purchased
		$sql = 
			"SELECT extra.code, extra.cost, IFNULL(entry_extra.quantity, 0)"
			. " FROM extra"
			. " LEFT OUTER JOIN entry_extra ON entry_id = ? AND entry_extra.code = extra.code"
			. " WHERE event_id = ?"
			. " ORDER BY event_id, extra.sequence, extra.code";
		if (! ($stmt = $mysqli->prepare($sql))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param('ii', $entry_id, $event_id ))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute())) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->store_result())) {
			echo "Store result failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->bind_result($code, $cost, $quantity))) {
			echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$index = 0;
		while ($stmt->fetch ()) {
			$extraCode[$index] = $code;
			$extraValue[$index] = $cost;
			$savedQuantity[$index] = $quantity;
			$index += 1;
		}
		$stmt->close ();
	}
} else {
	if ($_POST['submitButton'] == "Cancel") {
		session_destroy();
		header ( "Location: entrants.php?eventId=" . $event_id );
		exit();
	}
	$payment_received = $_POST['payment_received'];
	$entry_fees = $_POST['entry_fees'];
	$numberOfExtra = $_POST['numberOfExtra'];
	$extraCode = f_clean($_POST['extraCode']);
	$extraValue = f_clean($_POST['extraValue']);
	$extraQuantity = f_clean($_POST['extraQuantity']);
	$savedQuantity = f_clean($_POST['savedQuantity']);
	$extraTotal = 0;
	echo $entry_fees;
	for ($idx = 0; $idx < $numberOfExtra; $idx++) {
		if ($extraQuantity[$idx] < 0 || $extraQuantity[$idx] > 10) {
			$extraError[$idx] = 'Please key a number between 0 and 10';
			$pageError = TRUE;
		} else {
			$extraTotal += $extraQuantity[$idx] * $extraValue[$idx];
		}
		echo $extraTotal;
	}
	if (empty($_POST['payment_type'])) {
		$payment_typeError = 'Please select a Payment Type';
		$pageError = TRUE;
	} else {
		$payment_type = $_POST['payment_type'];
	}
	$payment_value = $entry_fees + $extraTotal;
	// $payment_owed = $payment_value - $payment_received;
	// Go and do it
	if ($pageError === FALSE) {
		$_SESSION['extraCode'] = $extraCode;
		$_SESSION['extraValue'] = $extraValue;
		$_SESSION['extraQuantity'] = $extraQuantity;
		$_SESSION['savedQuantity'] = $savedQuantity;
		$_SESSION['payment_type'] = $payment_type;
		$_SESSION['payment_owed'] = $payment_owed;
		$_SESSION['entry_fees'] = $entry_fees;
		if (! ($stmt = $mysqli->prepare("UPDATE entry set payment_type = ?, payment_value = payment_value + ? WHERE entry_id = ?"))) {
			$sqlError = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			$pageError = TRUE;
		}
		if (! ($stmt->bind_param('sii', $payment_type, $extraTotal, $entry_id ))) {
			$sqlError = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (! ($stmt->execute())) {
			$sqlError = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$pageError = TRUE;
		}
		if (!($stmt = $mysqli->prepare("INSERT INTO entry_extra(entry_id, code, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?" ))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		// Loop for each extra
		for ($i = 0; $i < $numberOfExtra; $i++) {
			if (!($stmt->bind_param('isii', $entry_id, $extraCode[$i], $extraQuantity[$i], $extraQuantity[$i]))) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if (!($stmt->execute())) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
		}
		$stmt->close();
	}
	if ($pageError === FALSE) {
		// Email entry confirmation
		$team_number = get_team_number($entry_id, $mysqli);
		$description = get_description($event_id, $mysqli);
		$to = $email[0];
		$subject = $description. " Optional Extras - Team " . $team_number;
		// Maximum length 70 characters per line
		// 1234567890123456789012345678901234567890123456789012345678901234567890
		$message = 
		  'Your request for event extras has been received.' . "\r\n"
		. 'You are team number ' . $team_number . '.' . "\r\n"
		. 'Please keep a note of this and include it in any communication.' . "\r\n"
		. "\r\n"
		. 'The cost of these extras will be $NZ.' . $extraTotal . '.' . "\r\n"
		. 'Please pay this and any other amount owing' . "\r\n"
				;
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
		$amount		= $extraTotal * 100 ; // Needs to be passed as cents
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
		if (! ($stmt->bind_param ( 'siiss', $transactionID, $entry_id, $extraTotal, $paymentRequestTime, $digitalOrderTime ))) {
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
	<div class="large-12 columns">
	  This form is specifically for making extra event payments. Please check that you are adding extras for:
	  <p class="text-center"><strong>Team: <?php echo $team_number;?> - <?php echo $team_name;?>
	  <br><?php echo $sub_event;?> <?php echo $gender;?> <?php echo $age_group;?></strong></p> 
    </div>
  </div>
  <div class="row">
	<div class="small-12 columns">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input id="entry_fees" name="entry_fees" type="hidden" value="<?php echo $entry_fees;?>" />
        <input id="payment_received" name="payment_received" type="hidden" value="<?php echo $payment_received;?>" />
        <input id="numberOfExtra" name="numberOfExtra" type="hidden" value="<?php echo $numberOfExtra;?>" />
<?php if ($pageError === TRUE) : ?>
				<div class="row">
					<div class="error">
						Errors exist! Please check the form for the details, correct them and press "Make Payment" again.
						<?php if ($sqlError) echo $sqlError;?>
						<?php if ($paystationError) echo $paystationError;?>
					</div>	
				</div>			
<?php endif; ?>				
<?php 
$sql = "SELECT code, description, cost FROM extra WHERE event_id = ? ORDER BY event_id, sequence, code";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($code, $description, $cost);
	if ($stmt->num_rows > 0) {
	  echo '<div class="row"><div class="small-12 columns"><fieldset><legend>Optional Extras</legend>';
		echo '<p>Please enter the number of each extra that you wish to purchase. The cost will be added to your entry total.</p>';
		echo '<div class="row">';
	  $index = 0;
	  while ($stmt->fetch()) {
			echo '<div class="large-3 small-12 columns">';
			echo '<input type="hidden" name="extraCode['.$index.']" value="'.$code.'" />';
			echo '<input type="hidden" name="extraValue['.$index.']" value="'.$cost.'" />';
			echo '<input type="hidden" name="savedQuantity['.$index.']" value="'.$savedQuantity[$index].'" />';
			echo '<label>'.$description.' - <b>$'.$cost.'</b><input type="number" name="extraQuantity['.$index.']" min="0" max="10" value="'.$extraQuantity[$index].'" onchange="owedPaymentValue()" /></label>';
			echo '<div class="error">'.$extraError[$index].'</div>';
			echo '<div><small>Number already requested: '.$savedQuantity[$index].'</small></div>';
			echo '</div>';
			$index += 1;
	  }
	  echo '</div>'; 
	  echo '</fieldset></div></div>';
	}
	$stmt->close();
}
?>
        <fieldset>
		  <legend>Payment</legend>
			<div class="row">
			  <div class="medium-8 small-12 columns">
				Please make a separate payment for these extras or add it to the amount still to be paid.<br> 
				If you have any queries please contact <a href="mailto:entries@rogaine.org.nz">entries@rogaine.org.nz</a>. 
			  </div>
			  <div class="medium-4 small-12 columns">
				<label>Additional Payment NZ$
				  <input type="text" id="payment_owed" name="payment_owed" readonly="readonly" value="$<?php echo $payment_owed?>" />
				</label>
			  </div>
			</div>
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