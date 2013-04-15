// user.js

$(function()
{
	$('.user-sport-tab-back').click(function()
	{
		var selectedEle = $('.user-sport-selected');
		
		// Hide currently selected tab, turn back to normal
		selectedEle.children('.user-sport-tab-selected-container').hide();
		selectedEle.children('.user-sport-tab-back').show();
		selectedEle.removeClass('user-sport-selected');
		
		// Show newly selected tab
		var index = $(this).parent().index();
		$(this).siblings('.user-sport-tab-selected-container').show();
		$(this).hide();
		$(this).parent().addClass('user-sport-selected');
		
		// Show corresponding sports container
		$('.user-sport-container-selected').hide()
										   .removeClass('user-sport-container-selected');
		$('.user-sport-container:eq(' + index + ')').show()
													.addClass('user-sport-container-selected');
		
	})
	
	$("#user-player-request").click(function()
	{
		var detailsEle = $('#user-details');
		var actingUserID = detailsEle.attr('actingUserID');
		var receivingUserID = detailsEle.attr('receivingUserID');
		var action = 'friend';
		var type   = 'friend';
		var details = ''
		var idType = '';
		var typeID = '';

		createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
		showConfirmationAlert('Request sent.');
	})
	
	
})