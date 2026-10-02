<?php
include_once 'includes/functions.php';
sec_session_start ();
?>
<!DOCTYPE html>
<html>
<head>
<script src="../js/sha512.js"></script>
<script src="../js/forms.js"></script>
</head>
<body>
<p>Hello World</p>
<form action="includes/test_process_login.php" method="post" name="login_form">
  <label>Email <input type="text" name="email" id="email" /></label>
  <label>Password <input type="password" name="password" id="password" /></label> 
  <input id="loginButton" type="button" value="Login" onclick="formhash(this.form, this.form.password);" />
</form>
</body>
</html>