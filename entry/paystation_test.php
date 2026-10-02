<?php
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../foundation-5.5.3/css/normalize.css" />
<link rel="stylesheet" href="../foundation-5.5.3/css/foundation.min.css" />
</head>
<body>
<div class="row">
<div class="small-12 columns">
<h1>Form</h1>
<form action="paystation_return.php" method="get">
	<label>merchantSession:<input name="ms" type="text" value="12346790"></label>
	<label>transactionId:<input name="ti" type="text" value="0079529609-01"></label>
	<label>errorCode:<input name="ec" type="text" value="0"></label>
	<label>errorMessage:<input name="em" type="text" value="Transaction successful"></label>
	<label>amount:<input name="am" type="number" value="20500"></label>
	<label>cardNumber:<input name="cardno" type="text" value="498765XXXXXXX769"></label>
	<label>cardExpiry:<input name="cardexp" type="text" value="1705"></label>
	<input type="submit" value="Submit">
</form>
</div>
</div>
</body>
</html>