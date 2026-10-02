<?php
	$to = 'wayne.millow@nlg.co.nz';
	$subject = 'Rogaine Entry';
	$message = 'Congratulations.' . "\r\n" . 'You have entered the rogaine.' . "\r\n" . 'We look forward to seeing you there and hope that you enjoy it.' . "\r\n" . "\r\n";
	$message .= "\r\n" . 'Please send your cheque payment of $1,000,000 to:' . "\r\n" . 'NZ Rogaine Champs';
	$headers = 'From: webmaster@rogaine.org.nz' . "\r\n" . 'Reply-To: webmaster@rogaine.org.nz' . "\r\n" . 'Bcc: wayne.millow@gmail.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion ();
	mail ( $to, $subject, $message, $headers );
	echo 'Sending Test email';
?>