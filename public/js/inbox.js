// inbox.js

$(function()
{
	$('html, body').animate({scrollTop: $(document).height()}, 'slow')
	
	$('#submitPostMessage').parents('form').submit(function()
	{
		if ($(this).find('textarea').val() == '') {
			showConfirmationAlert('You must write something');
			return false;
		} else {
			return true;
		}
	})
})