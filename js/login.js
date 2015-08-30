// JavaScript Document
var typing;
var valid;

$(function()
{
	$('#email').keyup(function()
	{
		if (typing) {
			clearTimeout(typing);
		}
		
		typing = setTimeout(function() {
			emailExists($('#email').val(), emailValid)
			}, 200);
	})
	
	$('#forgotForm').submit(function()
	{
		if (typing) {
			setTimeout(function() {
				return testValid();
			}, 600);
			
			return false;
		} else {
			return testValid();
		}
	});
	
	if ($('#forgot-alert-container').length > 0) {
		showAlert($('#forgot-alert-container'))
	}
	
	if ($('#already-alert-container').length > 0) {
		showAlert($('#already-alert-container'))
	}
	
})


/**
 * ajax call to see if email exists in db
 */
function emailExists(email, callback)
{
	$.ajax({
		url: '/ajax/email-exists',
		type: 'POST',
		data: {email: email},
		success: function(data) {
			callback(data);
		}
	})
	
}

/**
 * populate img on forgot password page depending on emailExists
 */
function emailValid(exists)
{
	if (exists) {
		// Email is valid
		$('#valid').show();
		$('#invalid').hide();
		valid = true;
	} else {
		$('#invalid').show()
		$('#valid').hide();
		valid = false;
	}
	
	typing = false;
}

/**
 * test if valid
 */
function testValid()
{
	if (valid) {
		return true;
	} else {
		return false;
	}
}