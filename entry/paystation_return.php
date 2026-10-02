<?php
include_once 'includes/functions.php';
$event_id = 0;
$entry_id = 0;
$payment_type = 'credit card';
$payment_value = 0;		
$payment_typeError = '';
$sqlError = '';
$pageError = FALSE;
if (empty($_POST['submitButton']) && isset($_GET['ti']) && isset($_GET['ec'])) {
	$merchantSession = $_GET['ms'];
	$transactionId = $_GET['ti'];
	$errorCode = $_GET['ec'];
	$errorMessage = $_GET['em'];
	$amount = $_GET['am'];
	$cardNumber = $_GET['cardno'];
	$cardExpiry = $_GET['cardexp'];
	//$merchantRef = $_GET['merchant_ref'];
	if (! ($stmt = $mysqli->prepare("UPDATE entry_credit_card SET error_code = ?, error_message = ?, amount = ?, card_number = ?, card_expiry = ? WHERE transaction_id = ?" ))) {
		$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if (! ($stmt->bind_param ( 'isisss', $errorCode, $errorMessage, $amount, $cardNumber, $cardExpiry, $transactionId))) {
		$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	if (! ($stmt->execute ())) {
		$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	$stmt->close();
	if ($stmt = $mysqli->prepare("SELECT entry_id FROM entry_credit_card WHERE transaction_id = ?")) {
		$stmt->bind_param('s', $transactionId);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($entry_id);
		$stmt->fetch();
		$stmt->close();
	}
	if ($stmt = $mysqli->prepare("select distinct event_id from entrant where entry_id = ?")) {
		$stmt->bind_param('i', $entry_id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($event_id);
		$stmt->fetch();
		$stmt->close();
	}
	if ($stmt = $mysqli->prepare("select payment_type, payment_value from entry where entry_id = ?")) {
		$stmt->bind_param('i', $entry_id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payment_type, $payment_value);
		$stmt->fetch();
		$stmt->close();
	}
	/* If the transaction is successful update the payment record with the entry and go to the entrants page */
	if ($errorCode == '0') {
		if (! ($stmt = $mysqli->prepare("UPDATE entry SET status = 'paid', payment_received = payment_received + ? WHERE entry_id = ?" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		$received = $amount / 100;
		if (! ($stmt->bind_param ( 'ii', $received, $entry_id))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close();
	    //session_destroy();
	    //header ( "Location: entrants.php?eventId=" . $event_id );
	    //exit();
	}
} // End return from paystation
else {
	if (isset($_SESSION['event_id'])){
		$event_id = $_SESSION['event_id'];
	}
	if (isset($_SESSION['entry_id'])){
		$entry_id = $_SESSION['entry_id'];
	}
	if (isset($_SESSION['payment_type'])){
		$payment_type = $_SESSION['payment_type'];
	}
	if (isset($_SESSION['payment_value'])){
		$payment_value = $_SESSION['payment_value'];
	}
}
// If this form submitted the data then validate it
if (!empty($_POST['submitButton'])) {
	if (isset($_POST['event_id'])){
		$event_id = $_POST['event_id'];
	}
	// If the user wants to go back to
	if ($_POST['submitButton'] == "Cancel" || $_POST['submitButton'] == "Continue") {
		session_destroy();
		header("Location: entrants.php?eventId=" . $event_id );
		exit();
	}
	$entry_id = $_POST['entry_id'];
	$payment_value = $_POST['payment_value'];
	if (empty($_POST['payment_type'])) {
		$payment_typeError = 'Please select a Payment Type';
		$pageError = TRUE;
	} else {
		$payment_type = $_POST['payment_type'];
	}
	// If no errors go to the page submitted
	if ($pageError === FALSE) {
		$_SESSION['event_id'] = $event_id;
		$_SESSION['entry_id'] = $entry_id;
		$_SESSION['payment_type'] = $payment_type;
		$_SESSION['payment_value'] = $payment_value;
		if (! ($stmt = $mysqli->prepare("UPDATE entry SET payment_type = ? WHERE entry_id = ?" ))) {
			$sqlError .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (! ($stmt->bind_param ( 'si', $payment_type, $entry_id))) {
			$sqlError .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (! ($stmt->execute ())) {
			$sqlError .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		$stmt->close();
	}
	if ($pageError === FALSE && $payment_type == 'credit card') {
		if ($stmt = $mysqli->prepare("select team_number, team_name, payment_value from entry where entry_id = ?")) {
			$stmt->bind_param('i', $entry_id);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($team_number, $team_name, $payment_value);
			$stmt->fetch();
			$stmt->close();
		}
		$amount		= $payment_value * 100 ; // Needs to be passed as cents
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
		if (! ($stmt->bind_param ( 'siiss', $transactionID, $entry_id, $payment_value, $paymentRequestTime, $digitalOrderTime ))) {
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
			$sqlError .= "<pre>".htmlentities($result)."</pre>"; //no digitalorder variable, so initiation must have failed.  Print out xml packet for debugging purposes
			$pageError = TRUE;
		}
	}
	if ($pageError === FALSE) {
		session_destroy();
		header ( "Location: entrants.php?eventId=" . $event_id );
		exit();
	}
}
?>
<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
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
      <div class="error">This is a test site only. Completing this form does not enter any event. Nor does it make any obligation for payment of any value.</div>
    </div>
  </div>
 -->  
	<form id="paymentForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<input name="event_id" type="hidden" value="<?php echo $event_id;?>" />
		<input name="entry_id" type="hidden" value="<?php echo $entry_id;?>" />
<?php if (empty($_POST['submitButton']) && isset($_GET['ti']) && isset($_GET['ec']) && $_GET['ec'] == '0') : ?>
	<div class="row">
		<div class="small-12 medium-6 medium-centered columns">
		  Thank you. A payment of NZ$<?php $value = $_GET['am']; $value = $value / 100; echo $value; ?> has been received on card number <?php echo $_GET['cardno']; ?>.
		  Your entry will display on <a target="_blank" href="entrants.php?eventId=<?php echo $event_id;?>">List of Entrants</a>. 
        </div>
    </div>
	<div class="row">
		<div class="small-12 medium-6 medium-centered columns">
			<input id="continueButton" class="button success" type="submit" name="submitButton" value="Continue">
		</div>
	</div>
<?php else : ?>  
<?php if ($pageError === TRUE) : ?>
	<div class="row">
		<div class="error">
			Errors exist! Please check the form for the details, correct them and press enter again.
			<?php if ($sqlError) echo $sqlError;?>
		</div>	
	</div>			
<?php endif; ?>				
	<div class="row">
		<div class="small-12 columns">
			<fieldset>
				<legend>Payment</legend>
				<div class="row">
					<div class="small-12 columns">
						<span class="error">There was an error processing your credit card payment. Error is <?php echo $errorMessage;?></span>
						Your entry has been received without payment. 
						Please re-try or choose an alternative payment method.
						If this does not succeed please contact:
<?php
$sql = "SELECT trim(first_name), trim(last_name), trim(email)"
		. " FROM event JOIN contact ON contact.contact_id = event.entry_contact_id"
		. " JOIN cheque_address ON cheque_address.address_id = event.address_id"
		. " WHERE event.event_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param ( 'i', $event_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($firstName, $lastName, $emailAddr);
	$stmt->fetch();
	if ($stmt->num_rows > 0) {
		echo '<p>'.$firstName.' '. $lastName.' - Email: <a href="mailto:'.$emailAddr.'">'.$emailAddr.'</a></p>';
	}
	$stmt->close();
}
?>
					</div>
				</div>
				<div class="row">
					<div class="medium-6 small-12 columns">
						Your entry will be confirmed by posting it in the <a target="_blank" href="entrants.php">list of entrants</a> on this website once payment has been received.
					</div>
					<div class="medium-6 small-12 columns">
						<label>Total Entry Fees $
							<input type="text" id="payment_value" name="payment_value" readonly="readonly" value="$<?php echo $payment_value?>" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-4 medium-6 small-12 columns">
						<label><input type="radio" name="payment_type" value="credit card" <?php echo ($payment_type=='credit card')?' checked':''?> >&nbsp;Credit Card</label>
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
						<label><input type="radio" name="payment_type" value="direct credit" <?php echo ($payment_type=='direct credit')?' checked':''?> >&nbsp;Direct credit</label>
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
						<label><input type="radio" name="payment_type" value="cheque" <?php echo ($payment_type=='cheque')?' checked':''?> >&nbsp;Cheque</label>
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
			</fieldset>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<fieldset>
				<legend>Confirm payment details</legend>
				<div class="row">
					<div class="small-12 columns">
						<p class="text-center">Your entry should immediately appear in the list of Entrants and a confirmation email will be sent to the Team Contact.<br>
						Please check this. If it does not happen please contact <a href="mailto:<?php echo $emailAddr;?>"><?php echo $emailAddr;?></a></p>
					</div>
				</div>
				<div class="row">
					<div class="large-4 medium-6 small-12 columns">
						<input id="payButton" class="button success" type="submit" name="submitButton" value="Pay">
					</div>
					<div class="large-4 medium-6 small-12 columns">
						<div id="formErrors" class="error">
<?php if ($pageError === TRUE) : ?>Errors exist! Please check the form for the details, correct them and press enter again.<?php endif; ?>
						</div>				
					</div>
					<div class="large-4 medium-6 small-12 columns">
						<input id="cancelButton" class="button info" type="submit" name="submitButton" value="Cancel">
					</div>
				</div>
			</fieldset>
		</div>
	</div>
<?php endif; ?>				
	</form>
</div>
<?php include 'footer.php'?>
</body>
</html>