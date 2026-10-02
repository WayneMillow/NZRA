<?php
include_once 'db_connect.php';
/* Start a session securely */
date_default_timezone_set('NZ');
function sec_session_start() {
	$session_name = 'sec_session_id'; // Set a custom session name
	$secure = SECURE;
	// This stops JavaScript being able to access the session id.
	$httponly = true;
	// Forces sessions to only use cookies.
	if (ini_set ( 'session.use_only_cookies', 1 ) === FALSE) {
		header ( "Location: error.php?err=Could not initiate a safe session (ini_set)" );
		exit ();
	}
	// Gets current cookies params.
	$cookieParams = session_get_cookie_params ();
	session_set_cookie_params ( $cookieParams ["lifetime"], $cookieParams ["path"], $cookieParams ["domain"], $secure, $httponly );
	// Sets the session name to the one set above.
	session_name ( $session_name );
	session_start (); // Start the PHP session
	session_regenerate_id ( true ); // regenerated the session, delete the old one.
}
/* */
function login($email, $password, $mysqli) {
	// Using prepared statements means that SQL injection is not possible.
	if ($stmt = $mysqli->prepare ( "SELECT admin_id, username, password, salt FROM administrators WHERE email = ? LIMIT 1" )) {
		$stmt->bind_param ( 's', $email ); // Bind "$email" to parameter.
		$stmt->execute (); // Execute the prepared query.
		$stmt->store_result ();
		// get variables from result.
		$stmt->bind_result ( $user_id, $username, $db_password, $salt );
		$stmt->fetch ();
		// hash the password with the unique salt.
		$password = hash ( 'sha512', $password . $salt );
		if ($stmt->num_rows == 1) {
			// If the user exists we check if the account is locked
			// from too many login attempts
			if (checkbrute ( $user_id, $mysqli ) == true) {
				// Account is locked
				// Send an email to user saying their account is locked
				return false;
			} else {
				// Check if the password in the database matches
				// the password the user submitted.
				if ($db_password == $password) {
					// Password is correct!
					// Get the user-agent string of the user.
					$user_browser = $_SERVER ['HTTP_USER_AGENT'];
					// XSS protection as we might print this value
					$user_id = preg_replace ( "/[^0-9]+/", "", $user_id );
					$_SESSION ['user_id'] = $user_id;
					// XSS protection as we might print this value
					$username = preg_replace ( "/[^a-zA-Z0-9_\-]+/", "", $username );
					$_SESSION ['username'] = $username;
					$_SESSION ['login_string'] = hash ( 'sha512', $password . $user_browser );
					// Login successful.
					return true;
				} else {
					// Password is not correct
					// We record this attempt in the database
					$mysqli->query ( "INSERT INTO login_attempts(user_id) VALUES ('$user_id')" );
					return false;
				}
			}
		} else {
			// No user exists.
			return false;
		}
	}
}
/* */
function checkbrute($user_id, $mysqli) {
	// Get timestamp of current time
	$now = time ();
	// All login attempts are counted from the past 2 hours.
	$valid_attempts = $now - (2 * 60 * 60);
	if ($stmt = $mysqli->prepare ( "SELECT login_time
			FROM login_attempts
			WHERE user_id = ?
			AND login_time > '$valid_attempts'" )) {
		$stmt->bind_param ( 'i', $user_id );
		// Execute the prepared query.
		$stmt->execute ();
		$stmt->store_result ();
		// If there have been more than 5 failed logins
		if ($stmt->num_rows > 5) {
			return true;
		} else {
			return false;
		}
	}
}
/* */
function login_check($mysqli) {
	// Check if all session variables are set
	if (isset ( $_SESSION ['user_id'], $_SESSION ['username'], $_SESSION ['login_string'] )) {
		$user_id = $_SESSION ['user_id'];
		$login_string = $_SESSION ['login_string'];
		$username = $_SESSION ['username'];
		// Get the user-agent string of the user.
		$user_browser = $_SERVER ['HTTP_USER_AGENT'];
		if ($stmt = $mysqli->prepare ( "SELECT password FROM administrators WHERE admin_id = ? LIMIT 1" )) {
			// Bind "$user_id" to parameter.
			$stmt->bind_param ( 'i', $user_id );
			$stmt->execute (); // Execute the prepared query.
			$stmt->store_result ();
			if ($stmt->num_rows == 1) {
				// If the user exists get variables from result.
				$stmt->bind_result ( $password );
				$stmt->fetch ();
				$login_check = hash ( 'sha512', $password . $user_browser );
				if ($login_check == $login_string) {
					// Logged In!!!!
					return true;
				} else {
					// Not logged in
					return false;
				}
			} else {
				// Not logged in
				return false;
			}
		} else {
			// Not logged in
			return false;
		}
	} else {
		// Not logged in
		return false;
	}
}
/* */
function esc_url($url) {
	if ('' == $url) {
		return $url;
	}
	$url = preg_replace ( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );
	$strip = array (
			'%0d',
			'%0a',
			'%0D',
			'%0A' 
	);
	$url = ( string ) $url;
	$count = 1;
	while ( $count ) {
		$url = str_replace ( $strip, '', $url, $count );
	}
	$url = str_replace ( ';//', '://', $url );
	$url = htmlentities ( $url );
	$url = str_replace ( '&amp;', '&#038;', $url );
	$url = str_replace ( "'", '&#039;', $url );
	if ($url [0] !== '/') {
		// We're only interested in relative links from $_SERVER['PHP_SELF']
		return '';
	} else {
		return $url;
	}
}
/* Cleans an array to protect against injection attacks. */
function f_clean($array) {
	return array_map ( 'htmlspecialchars', $array );
}
/* Displays the description of an event with the given id. */
function get_description($event_id, $mysqli) {
	if ($stmt = $mysqli->prepare ( "SELECT trim(description) FROM event WHERE event_id = ?" )) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1) {
		  $stmt->bind_result($result);
		  $stmt->fetch ();
		  return $result;
		}
	}
	return null;
}
/* Displays the description of an event with the given id. */
function get_team_number($entry_id, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT team_number FROM entry WHERE entry_id = ?" )) {
		$stmt->bind_param ( 'i', $entry_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1) {
		  $stmt->bind_result($result);
		  $stmt->fetch ();
			return $result;
		}
	}
	return null;
}
/* Gets the event date of an event with the given id. */
function get_event_date($event_id, $mysqli) {
	if ($stmt = $mysqli->prepare ( "SELECT event_date FROM event WHERE event_id = ?" )) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1){
			$stmt->bind_result($result);
			$stmt->fetch ();
			return date("l F j, Y", strtotime($result));
		}
  }
  return null;
}
/* Gets the event entry email. */
function get_event_entry_email($event_id, $mysqli) {
	$sql = "SELECT email FROM event JOIN contact ON contact_id = entry_contact_id WHERE event_id = ?";
	if ($stmt = $mysqli->prepare ($sql)) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1){
			$stmt->bind_result($result);
			$stmt->fetch ();
			return $result;
		}
  }
  return null;
}
/* Gets the entry close date of an event with the given id. */
function get_entry_close($event_id, $mysqli) {
	if ($stmt = $mysqli->prepare ( "SELECT entry_close_date FROM event WHERE event_id = ?" )) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1){
			$stmt->bind_result($result);
			$stmt->fetch ();
			return date("l F j, Y, g:i a", strtotime($result));
		}
  }
  return null;
}
/* Attempts to set an event id in the session based on the query information. */
function find_event($mysqli){
    if (isset ($_SESSION['eventId']) and isset ($_GET['eventId']) and $_SESSION['eventId'] != $_GET['eventId']) {
		return false;
	}
	if (! isset ($_SESSION['eventId'])){
		if (isset ($_GET['eventId'])){
			$_SESSION['eventId'] = $_GET['eventId'];
		} else {
			return false;
		}
	}
	if (!is_null(get_description($_SESSION['eventId'], $mysqli))){
		return true;
	}
	return false;
}
/* Verifies that at an event is open for entry. */
function event_open($event_id, $mysqli){
	// Check if the date is after the close date
	if ($stmt = $mysqli->prepare ( "SELECT event_date, entry_start_date, entry_close_date FROM event WHERE event_id = ?" )) {
		$stmt->bind_param ( 'i', $event_id );
		$stmt->execute ();
		$stmt->store_result ();
		if ($stmt->num_rows == 1) {
			$stmt->bind_result($event_date, $start, $close);
			$stmt->fetch ();
			$now_dt = new DateTime();
			$start_dt = new DateTime($start);
			$close_dt = new dateTime($close);
			if ($close_dt < $now_dt || $start_dt > $now_dt) {
				return false;
			}
			return true;
		}
	}
	return false;
}
/* Get the number or extras for the event */
function getNumberOfExtra($event_id, $mysqli) {
  $result = 0;
	if ($stmt = $mysqli->prepare("SELECT count(*) FROM extra WHERE event_id = ?")) {
		$stmt->bind_param('i', $event_id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($result);
		$stmt->fetch();
	}
	return $result;
}
function getDaysInMonth($year, $month) {
	$m = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	if ($month != 2) return $m[$month - 1];
	if ($year % 4 != 0) return $m[1];
	if ($year % 100 == 0 && $year % 400 != 0) return $m[1];
	return $m[1] + 1;
}
function calculateAge($dob){
	if(!empty($dob)){
		$birthdate = new DateTime($dob);
		$today   = new DateTime('today');
		$age = $birthdate->diff($today)->y;
		return $age;
	} else {
		return 0;
	}
}
/*----------------Functions Start----------------*/
function makePaystationSessionID($min=8,$max=8){

  # seed the random number generator - straight from PHP manual
  $seed = (double)microtime()*getrandmax();
  srand($seed);
  $pass = '';

  # make a string of $max characters with ASCII values of 40-122
  $p=0; while ($p < $max):
    $r=123-(rand()%75);
    $pass.=chr($r);
  $p++; endwhile;

  # get rid of all non-alphanumeric characters
  $pass=preg_replace("/[^a-zA-NP-Z1-9+]/","",$pass);

  # if string is too short, remake it
  if (strlen($pass)<$min):
    $pass=makePaystationSessionID($min,$max);
  endif;

  return $pass;

};

function doPostRequest($paystationUrl, $paystationParameters, $hmacMode, $hmacParameters){
  $contextOptions = array (
    'http' => array (
      'method' => 'POST',
      'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
          . "Content-Length: " . strlen($paystationParameters) . "\r\n",
      'content' => $paystationParameters
    )
  );
  $ctx = stream_context_create($contextOptions);
  if($hmacMode && $hmacParameters > ''){
    $hmacUrl = $paystationUrl.$hmacParameters;
    $fp = @fopen($hmacUrl, 'r', false, $ctx);
    $response = @stream_get_contents($fp);
  } else {
    $fp = @fopen($paystationUrl, 'r', false, $ctx);
    $response = @stream_get_contents($fp);
  }

  return $response;

}

function generateHMACParams($body,$key){
  $hmac_webservice_name = 'paystation';
  $hmac_timestamp = time();

  $hmac_body = pack('a*',$hmac_timestamp).pack('a*',$hmac_webservice_name).pack('a*',$body);

  $hmac_hash = hash_hmac('sha512',$hmac_body,$key);
  $hmac_params='?pstn_HMACTimestamp='.$hmac_timestamp.'&pstn_HMAC='.$hmac_hash;

  return $hmac_params;
}
 /*----------------Functions End----------------*/
?> 