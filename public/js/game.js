// game.js

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