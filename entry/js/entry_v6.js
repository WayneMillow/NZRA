/**
 * 
 */
window.onload = function() {
	displayEntrants();
	displayPaymentValue();
	checkForFamily();
	checkForSchool();
}
var httpRequest;
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
function adminPaymentValue() {
	var paymentValue = parseInt(document.getElementById("entryFees").value);
	var numberOfExtra = document.getElementById("numberOfExtra").value;
	for (var idx=0; idx < numberOfExtra; idx++) {
		var extraQuantity = document.getElementsByName("extraQuantity["+idx+"]")[0];
		var extraValue = document.getElementsByName("extraValue["+idx+"]")[0];
		var quantity = parseInt(extraQuantity.value);
		var value = parseInt(extraValue.value);
		paymentValue += (quantity * value);
	}
	document.getElementById("payment_value").value = paymentValue;
}
function owedPaymentValue() {
	var entryFees = parseInt(document.getElementById("entry_fees").value);
	var paymentReceived = parseInt(document.getElementById("payment_received").value);
	//var totalOwed = entryFees;
	var totalOwed = 0;
	var numberOfExtra = document.getElementById("numberOfExtra").value;
	for (var idx=0; idx < numberOfExtra; idx++) {
		var extraQuantity = document.getElementsByName("extraQuantity["+idx+"]")[0];
		var extraValue = document.getElementsByName("extraValue["+idx+"]")[0];
		var quantity = parseInt(extraQuantity.value);
		var value = parseInt(extraValue.value);
		totalOwed += (quantity * value);
	}
	//var paymentOwed = totalOwed - paymentReceived;
	var paymentOwed = totalOwed;
	document.getElementById("payment_owed").value = paymentOwed;
	if (paymentOwed == 0) {
		document.getElementById("showSubmit").style.display = "none";
	} else {
		document.getElementById("showSubmit").style.display = "block";
	}
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
	var genders = document.getElementsByName("gender");
	var gender = null;
	for (var idx=0; idx < genders.length; idx++) {
		if (genders[idx].checked) {
			gender = genders[idx].value;
			break;
		}
	}
	if (gender != null && gender == 'F' && event != null && event == '1.5 hour') {
   		for (var idx=0; idx < 5; idx++) {
      	  var entry_fee = document.getElementById("entry_fee["+idx+"]");
      	  if (idx == 0) {
      		entry_fee.innerHTML = '<option value="">Please Select</option><option value="35">$35</option>';  
      	  } else {
       		entry_fee.innerHTML = '<option value="">Please Select</option><option value="0">$0</option>';;  
      	  }
     	}
		return;
	}
	if (event != null) {
		makeRequest("getEntryFees.php", eventId, event);
	}
}
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
function OnMonthYearChange(name, index) {
    var yyyy = document.getElementById(name + '_yyyy' + '['+index+']');
    var mm  = document.getElementById(name + '_mm' + '['+index+']');
    var dd = document.getElementById(name + '_dd' + '['+index+']');
    if ((mm.value  == '') || (yyyy.value == '')) {
        return;
    }
    var date = new Date();
    date.setYear(yyyy.value);
    date.setMonth(mm.value-1);
    var l = getDaysInMonth(yyyy.value, mm.value);
    var day = dd.value;
    clearSelect(dd);
    var opt = document.createElement('option');
    opt.value = '';
    opt.text = 'Day';
    dd.options.add(opt);
    for (var i = 0; i < l; i++) {
        date.setFullYear(yyyy.value, mm.value, i + 1);
        var opt = document.createElement('option');
        opt.value = getLeadingZero(i + 1);
        opt.text = opt.value;
        if (i == (day - 1)) {
        	opt.selected = true;
        }
        dd.options.add(opt);
    }
    if (dd.value > 0) {
    	OnDayChange('dob', index);
    }
}
function getDaysInMonth(year, month) {
    var m = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (month != 2) return m[month - 1];
    if (year % 4 != 0) return m[1];
    if (year % 100 == 0 && year % 400 != 0) return m[1];
    return m[1] + 1;
}
function clearSelect(e) {
	if (e.options.length == 0) return;
    var first = e.options[0].innerHTML;
    if (first) {
        // Form does not allow selection of a blank date. Drop all existing options.
        e.options.length = 0;
    } else {
        // Form allows selection of a blank date. First option is blank. Keep it.
        e.options.length = 1;
    }
}
function getLeadingZero(n) {
    if (n < 10) n = '0' + n;
    return n;
}
function formatDate(date) {
	var yyyy = date.getFullYear();
	var mm = date.getMonth();
	var dd = date.getDate();
	var ww = date.getDay();
	return weekDays[ww] + ' ' + getLeadingZero(dd) + ' ' + monthNames[mm] + ' ' + yyyy;
}
function OnDayChange(name, index) {
    var yyyy = document.getElementById(name + '_yyyy' + '['+index+']');
    var mm  = document.getElementById(name + '_mm' + '['+index+']');
    var dd = document.getElementById(name + '_dd' + '['+index+']');
    if ((yyyy.value  == '') || (mm.value  == '') || (dd.value == '')) {
        return;
    }
    var dob = new Date(yyyy.value, parseInt(mm.value)-1, dd.value, 0, 0, 0);
    var eventDate = new Date(document.getElementById('eventDate').value);
    document.getElementById('age_at_event' + '['+index+']').value = calculateAge(dob, eventDate);
}
function calculateAge (dateOfBirth, dateToCalculate) {
    var calculateYear = dateToCalculate.getFullYear();
    var calculateMonth = dateToCalculate.getMonth();
    var calculateDay = dateToCalculate.getDate();
    var birthYear = dateOfBirth.getFullYear();
    var birthMonth = dateOfBirth.getMonth();
    var birthDay = dateOfBirth.getDate();
    var age = calculateYear - birthYear;
    var ageMonth = calculateMonth - birthMonth;
    var ageDay = calculateDay - birthDay;
    if (ageMonth < 0 || (ageMonth == 0 && ageDay < 0)) {
        age = parseInt(age) - 1;
    }
    return age;
}
function checkForFamily() {
	var genders = document.getElementsByName('gender');
	var familyText = document.getElementById('familyText');
	for (var idx=0; idx < genders.length; idx++) {
		var gender = genders[idx];
		if (gender.value == 'F') {
			var ageGroups = document.getElementById('ageGroups');
			if (gender.checked) {
				ageGroups.className = 'hide';
				setAgeGroupToOpen();
				familyText.className = '';
			} else {
				ageGroups.className = '';
				familyText.className = 'hide';
			}
			break;
		}
	}
}
function setAgeGroupToOpen() {
	var ageGroups = document.getElementsByName('age_group');
	for (var idx=0; idx < ageGroups.length; idx++) {
		var ageGroup = ageGroups[idx];
		if (ageGroup.value == 'O') {
			ageGroup.checked = true;
		} else {
			ageGroup.checked = false;
		}
	}
}
function checkForSchool() {
	var ageGroups = document.getElementsByName('age_group');
	for (var idx=0; idx < ageGroups.length; idx++) {
		var ageGroup = ageGroups[idx];
		if (ageGroup.value.startsWith('Y')) {
			var genders = document.getElementById('genders');
			if (ageGroup.checked) {
				genders.className = 'hide';
				setGenderToMixed();
				break;
			} else {
				genders.className = '';
			}
		} 
	}
}
function setGenderToMixed() {
	var genders = document.getElementsByName('gender');
	for (var idx=0; idx < genders.length; idx++) {
		var gender = genders[idx];
		if (gender.value == 'X') {
			gender.checked = true;
		} else {
			gender.checked = false;
		}
	}
}