// game.js
var postContent = "<p class='dark clear larger-margin-top game-cancel-reason'>Reason <span class='light'>optional</span></p><textarea class='darkest clear game-cancel-reason' id='cancel-reason'></textarea><p class='clear width-100 center margin-top light'>This action cannot be undone.</p>";
var confirmClicked;

$(function() {

	$('#game-plus').change(function()
	{
		var value = $(this).val();
		var detailsEle = getDetailsEle();
		var gameID = detailsEle.attr('gameID');
		var userID = detailsEle.attr('actingUserID');
		
		updateUserGamePlus(value, gameID, userID);
		showConfirmationAlert('Updated');
	})
	
	/* manage button was clicked */
	$('#dropdown-menu-manage').children('.dropdown-menu-option-container').click(function()
	{
		
		var val = $(this).children('p.dropdown-menu-option-text').text().toLowerCase();
		
		if (val == 'cancel game') {
			
			if ($('#cancel-game').length > 0) {
				// is recurring game, show special alert
				$('#game-cancel-subscribe-container').html(postContent);
				showAlert($('#manage-cancel-alert-container'));
			} else {
				// non recurring, use basic confirm alert
				confirmAction = function () {
						var detailsEle = getDetailsEle();
						var userID = detailsEle.attr('actingUserID');
						var idType = detailsEle.attr('idType');
						var typeID = detailsEle.attr(idType);
						var actingUserID = userID;
						var receivingUserID;
						var action = 'delete';
						var type   = idType.replace(/ID/, '');
						var details;		
						
						var onceOrAlways = true;
						
						//createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
						
						cancelType(idType, typeID, onceOrAlways);
						changedAlert = true;
						//reloadPage();
				}
				
				var detailsEle = getDetailsEle();
				var text = (typeof detailsEle.attr('teamName') == 'undefined' ? 'cancel this game' : 'delete' + detailsEle.attr('teamName'));
				
				populateConfirmActionAlert(text, postContent);
				
			}
		} else if (val == 'remove player') {
			
			showAlert($('#manage-remove-player-alert-container'));
		}

	});
	
	$('#confirm-cancel').click(function()
	{
		
		if (confirmClicked) {
			// Prevent multiple firing
			return false;
		}
		confirmClicked = true;
		
		var value = $('#cancel-game').val();
		var onceOrAlways;
		var detailsEle = getDetailsEle();
		var userID = detailsEle.attr('actingUserID');
		var idType = detailsEle.attr('idType');
		
		if (value == 'this week') {
			// Cancel for this week only
			onceOrAlways = true;
		}
		

		var detailsEle = getDetailsEle();
		var userID = detailsEle.attr('actingUserID');
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = userID;
		var receivingUserID;
		var action = 'delete';
		var type   = idType.replace(/ID/, '');
		var details;		
	
		
		//createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);

		cancelType(idType, typeID, onceOrAlways);
		changedAlert = true;
		
		//reloadPage();
	})
	
	/* override join capability */
	if (getDetailsEle().attr('picture') != '') {
		$('#join-button').unbind('click.join')
		.click(function()
		{
			showAlert($('#upload-alert-container'));
		})
	}
	
	if ($('#canceled-alert-container').length > 0) {
		// Game has been canceled
		showAlert($('#canceled-alert-container'));
		
		$('.alert-black-back,.alert-x').unbind('click.default');
		
		$('.alert-x').hide();
	}
			

})

/**
 * Ajax call to update user's "plus" category for a game (ie how many people they are bringing)
 */
function updateUserGamePlus(plus, gameID, userID)
{
	var options = new Object();
	options.plus = plus;
	options.gameID = gameID;
	options.userID = userID;
	
	$.ajax({
		url: '/ajax/update-user-game-plus',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			reloadPage();
		}
	})
}