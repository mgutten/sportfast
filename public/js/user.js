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
	
	
	/* show user availabilty on click */
	$('.user-availability-show').click(function()
	{
		$(this).parent().find('.availabilty-calendar-container').show();
		$(this).hide();
		$(this).parent().find('.user-availability-hide').show();
	})
	
	$('.user-availability-hide').click(function()
	{
		$(this).next('.availabilty-calendar-container').hide();
		$(this).hide();
		$(this).parent().find('.user-availability-show').show();
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
	
	$("#user-player-cancel-request").click(function()
	{
		confirmAction = function() 
		{
			var detailsEle = $('#user-details');
			var actingUserID = detailsEle.attr('actingUserID');
			var receivingUserID = detailsEle.attr('receivingUserID');
			
			
			showConfirmationAlert('Player removed');
			removeFriend(actingUserID, receivingUserID);
		}
		
		populateConfirmActionAlert('remove this player');
	})
	
	$('#dropdown-menu-hidden-container-invite-to').find('.dropdown-menu-option-container').click(function()
	{
		var detailsEle = getDetailsEle();
		var idType = $(this).attr('idType');
		var typeID = $(this).attr(idType);
		var receivingUserID = detailsEle.attr('receivingUserID');
		var actingUserID = detailsEle.attr('actingUserID');
		var action = 'invite';
		var type   = idType.replace('ID', '');
		var details;
		
		createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
		showConfirmationAlert('Invite sent');
	})
	
	
})


/**
 * remove friendship (connected players)
 */
function removeFriend(userID1, userID2)
{
	$.ajax({
		url: '/ajax/remove-friend',
		type: 'POST',
		data: {userID1: userID1,
			   userID2: userID2},
		success: function(data) {
			reloadPage()
		}
	})
}