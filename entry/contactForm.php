<?php
$your_name_error = $your_email_error = $contact_who_error = $subject_error = $message_error = "";
$your_name = $your_email = $contact_who = $subject = $message = $success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (empty($_POST["your_name"])) {
        $your_name_error = "Your name is required";
    } else {
        $your_name = test_input($_POST["your_name"]);
        if (!preg_match("/^[a-zA-Z ]*$/",$your_name)) {
            $your_name_error = "Only letters and white space allowed";
        }
    }
    
    if (empty($_POST["your_email"])) {
        $your_email_error = "Your email is required";
    } else {
        $your_email = test_input($_POST["your_email"]);
        if (!filter_var($your_email, FILTER_VALIDATE_EMAIL)) {
            $your_email_error = "Invalid email format";
        }
    }

    if (empty($_POST["contact_who"])) {
        $contact_who = "";
    } else {
        $contact_who = test_input($_POST["contact_who"]);
        if (!preg_match("/^[a-zA-Z ]*$/",$contact_who)) {
            $contact_who_error = "Only letters and white space allowed";
        }
    }
    
    if (empty($_POST["subject"])) {
        $subject_error = "Subject is required";
    } else {
        $subject = test_input($_POST["subject"]);
    }
    
    if (empty($_POST["message"])) {
        $message_error = "Message is required";
    } else {
        $message = test_input($_POST["message"]);
    }
    
    if ($your_name_error == '' and $your_email_error == '' and $contact_who_error == '' and $subject_error == '' and $message_error == '') {
        $message_body = '';
        unset($_POST["submit"]);
        foreach ($_POST as $key => $value) {
            $message_body .= "$key: $value\n";
        }
        $mailTo = "contact.us@rogaine.org.nz";
        $headers = "From: ".$your_email."\r\nX-Mailer: PHP/".phpversion();
        if (mail($mailTo, $subject, $message_body, $headers)) {
            $success = "Message sent, thank you for contacting us";
            $your_name = $your_email = $contact_who = $subject = $message = "";
        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8">
<meta name="description" content="New Zealand Rogaining Association Event Entry">
<meta name="keywords" content="rogaining, rogaine, NZRA, new zealand, nz, mapsport, orienteering, outdoors, navigation, bushwalking, tramping, backpacking, hiking, camping, sport, compass, adventure sport">
<link rel="shortcut icon" href="http://rogaine.org.nz/favicon.ico">
<link rel="stylesheet" href="./css/contactForm.css" type="text/css">
<title>NZRA | Contact us</title>
</head>
<body>
<header>
</header>
<main>
<div class="container">  
<form id="contact" action="" method="post">
<h3>Quick Contact</h3>
<h4>Contact us today, and get a reply within 24 hours!</h4>
<fieldset>
<label>Your Name:<input name="your_name" placeholder="Your name" type="text" tabindex="1" value="<?= $your_name ?>" autofocus></label>
<span class="error"><?= $your_name_error ?></span>
</fieldset>
<fieldset>
<label>Yor Email Address:<input name="your_email" placeholder="Your Email Address" type="text" tabindex="2" value="<?= $your_email ?>"></label>
<span class="error"><?= $your_email_error ?></span>
</fieldset>
<fieldset>
<label>Name of Person you want to Contact:<input name="contact_who" placeholder="Who to contact" type="text" tabindex="3" value="<?= $contact_who ?>"></label>
<span class="error"><?= $contact_who_error ?></span>
</fieldset>
<fieldset>
<label>Subject:<input name="subject" placeholder="Subject" type="text" tabindex="4" value="<?= $subject ?>"></label>
<span class="error"><?= $subject_error ?></span>
</fieldset>
<fieldset>
<label>Message:<textarea name="message" placeholder="Type your Message Here...."  tabindex="5"><?= $message ?></textarea></label>
<span class="error"><?= $message_error ?></span>
</fieldset>
<fieldset>
<button name="submit" type="submit" data-submit="...Sending">Submit</button>
</fieldset>
<div class="success"><?= $success ?></div>
<p>This form just sends an email to contact.us@rogaine.org.nz - if you feel more comfortable you may do this manually.</p>
</form>
</div>
</main>
<footer>
</footer>
</body>
</html>