$(document).foundation();
function validatePassword() {
	coded = "ocMPoMmCOPmvvP";
	key = "nfmNOuMdJArXBQvsipWEkhK6oUG84eCZqRH0Djc1FI5P7zSt9bayl2g3TxwLVY";
	shift=coded.length;
	link="";
	for (i=0; i<coded.length; i++) {
		if (key.indexOf(coded.charAt(i))==-1) {
			ltr = coded.charAt(i)
		    link += (ltr)
		} else {     
		    ltr = (key.indexOf(coded.charAt(i))-shift+key.length) % key.length
		    link += (key.charAt(ltr))
		}
	}
	if (link == $('#password').val()) {
		$('#organisers').removeClass('hide');
		$('#password').val('');
	} else {
		if (!$('#organisers').hasClass('hide')) {
			$('#organisers').addClass('hide');
		}
	}
}