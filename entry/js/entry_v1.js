/**
 * 
 */
window.onload = function() {
	displayEntrants();
	displayPaymentValue();
}
function displayEntrants() {
	var elem = document.getElementById("entrant_count");
	var entrantCount = elem.options[elem.selectedIndex].text;
	var entrant3 = document.getElementById("entrant3");
	var entrant4 = document.getElementById("entrant4");
	var entrant5 = document.getElementById("entrant5");
	entrant3.className = "row hide";
	entrant4.className = "row hide";
	entrant5.className = "row hide";
	if (entrantCount == "3") {
		entrant3.className = "row";
	} else if (entrantCount == "4") {
		entrant3.className = "row";
		entrant4.className = "row";
	} else if (entrantCount == "5") {
		entrant3.className = "row";
		entrant4.className = "row";
		entrant5.className = "row";
	}
}
function displayPaymentValue() {
	var elem = document.getElementById("entrant_count");
	var entrantCount = elem.options[elem.selectedIndex].text;
	var paymentValue = 0;
	for (var idx=0; idx < entrantCount; idx++) {
		var entryFee = document.getElementsByName("entry_fee["+idx+"]")[0];
		var value = parseInt(entryFee.options[entryFee.selectedIndex].value);
		if (value) {
			paymentValue += value;
		}
	}
	var numberOfExtra = document.getElementById("numberOfExtra").value;
	for (var idx=0; idx < numberOfExtra; idx++) {
		var extraQuantity = document.getElementsByName("extraQuantity["+idx+"]")[0];
		var extraValue = document.getElementsByName("extraValue["+idx+"]")[0];
		var quantity = parseInt(extraQuantity.value);
		var value = parseInt(extraValue.value);
		paymentValue += (quantity * value);
	}
	document.getElementById("payment_value").value = "$" + paymentValue;
}
function setEntryFees() {
	var eventId = document.getElementById("eventId").value;
	var events = document.getElementsByName("event");
	var event = null;
	for (var idx=0; idx < events.length; idx++) {
		if (events[idx].checked) {
			event = events[idx].value;
			break;
		}
	}
	if (event != null) {
		makeRequest("getEntryFees.php", eventId, event);
	}
}
var httpRequest;
function makeRequest(url, eventId, event) {
    httpRequest = new XMLHttpRequest();

    if (!httpRequest) {
      alert('Giving up :( Cannot create an XMLHTTP instance');
      return false;
    }
    httpRequest.onreadystatechange = alertContents;
    httpRequest.open('POST', url, true);
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    httpRequest.send('eventId=' + eventId + '&event=' + event);  
}
function alertContents() {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
      if (httpRequest.status === 200) {
   		for (var idx=0; idx < 5; idx++) {
    	  var entry_fee = document.getElementById("entry_fee["+idx+"]");
    	  entry_fee.innerHTML = httpRequest.responseText;
   		}
      } else {
        alert('There was a problem with the request.');
      }
    }	
}