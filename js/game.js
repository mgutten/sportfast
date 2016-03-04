// game.js
var postContent = "<p class='dark clear larger-margin-top game-cancel-reason'>Reason <span class='light'>optional</span></p><textarea class='darkest clear game-cancel-reason' id='cancel-reason'></textarea>";
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
	
	
	/* OPTIONS ONCLICK SHOW ALERT/REDIRECT */
	$('#profile-option-cancel').click(function()
	{
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
	})
	
	/* become member of similar game */
	$('#game-become-member').click(function(e) 
	{
		e.preventDefault();
		var detailsEle = getDetailsEle();
		var userID = detailsEle.attr('actingUserID');
		var gameID = $(this).parents('.find-result-container').attr('gameID');

		addMemberToGame(gameID, userID, memberAdded);
	});
		
	
	/* change reminder time */
	$('#dropdown-menu-hidden-container-reminder-hour, #dropdown-menu-hidden-container-reminder-ampm').find('.dropdown-menu-option-container').click(function()
	{
		// Delay getHour because selected text needs to change before we can access it
		setTimeout(function() {
			var hour = getReminderHour();
			var detailsEle = getDetailsEle();
			var gameID = detailsEle.attr('gameID');
			
			updateSendReminder(gameID, hour);
			showConfirmationAlert('Updated');
		}, 100)
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
	
	$('.profile-join-player-container').click(function()
	{
		$('.schedule-in').trigger('click');
	})
	
	/*
	$('.schedule-in').bind('click.in',function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		var confirmed = '1';
		var type;

		addUserToGame(idType, typeID, actingUserID, confirmed);
		type = 'game';
		
		//showConfirmationAlert('Added to ' + type);
	})
	
	$('.schedule-out').bind('click.out',function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		var confirmed = '0';
		var type;

		addUserToGame(idType, typeID, actingUserID, confirmed);
		type = 'game';
		
		//showConfirmationAlert('Added to ' + type);
	})
	
	$('.schedule-maybe').bind('click.maybe',function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		var confirmed = '2';
		var type;

		addUserToGame(idType, typeID, actingUserID, confirmed);
		type = 'game';
		
		//showConfirmationAlert('Added to ' + type);
	})
	*/
	
	/* receive reminder emails? */
	$('#reminders-alert-yesno-container').children('.selectable-text').click(function()
	{
		
		var doNotEmail = ($(this).text().toLowerCase() == 'yes' ? '0' : '1');
		var detailsEle = getDetailsEle();
		var gameID = detailsEle.attr('gameID');
		
		updateEmailAlert(gameID, doNotEmail, 'doNotEmail');
		
	})
	
	/* send gameon emails? */
	$('#reminders-alert-gameon-admin-yesno-container,#reminders-alert-gameon-admin-who-container').children('.selectable-text').click(function()
	{
		var gameOn;
		if ($('#reminders-alert-gameon-admin-yesno-container').find('.selectable-text.green-bold').text().toLowerCase() == 'yes') {
			// Game on to be sent, 1 or 2?
			if ($('#reminders-alert-gameon-admin-who-container').find('.selectable-text.green-bold').text().toLowerCase() == 'all members') {
				// send to everyone, value 1
				gameOn = '1';
			} else {
				gameOn = '2';
			}
			
			$('#reminders-alert-gameon-container').show();
		} else {
			// Game on not to be sent
			gameOn = '0';
			$('#reminders-alert-gameon-container').hide();
		}
		
		var detailsEle = getDetailsEle();
		var gameID = detailsEle.attr('gameID');
		
		updateEmailAlert(gameID, gameOn, 'gameOn');
		
	})
	
	/* receive gameon emails? */
	$('#reminders-alert-gameon-yesno-container').children('.selectable-text').click(function()
	{	

		var emailGameOn = ($(this).text().toLowerCase() == 'yes' ? '1' : '0');
		var detailsEle = getDetailsEle();
		var gameID = detailsEle.attr('gameID');
		
		updateEmailAlert(gameID, emailGameOn, 'emailGameOn');
		
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
		
		//$('.alert-black-back,.alert-x').unbind('click.default');
		
		//$('.alert-x').hide();
	}
		
	if ($('#stash-available-alert-container').length > 0) {
		// User just joined game and there is a stash available
		showAlert($('#stash-available-alert-container'));
		
		$('.game-stash-button').click(function() {
			$('.alert-black-back').trigger('click');
		})
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

/**
 * Ajax call to update sendReminder of game
 */
function updateSendReminder(gameID, hour)
{
	var options = {gameID: gameID,
				   hour: hour};
	
	$.ajax({
		url: '/ajax/update-send-reminder',
		type: 'POST',
		data: {options: options},
		success: function(data) {
		}
	})
}

/**
 * callback from addMemberToGame for similarGame
 */
function memberAdded()
{
	showConfirmationAlert('Added as member');
	
	reloadPage();
}

/**
 * convert time to military time from reminder alert
 */
function getReminderHour()
{
	var hour = parseInt($('#reminder-hour').find('.dropdown-menu-option-text').text(), 10);
	var ampm = $('#reminder-ampm').find('.dropdown-menu-option-text').text().toLowerCase();
	
	if (ampm == 'pm') {
		hour += 12;
	}
	
	if (hour == 24) {
		// is noon
		hour = 12;
	} else if (hour == 12) {
		hour = 0;
	}
	
	return hour;
}