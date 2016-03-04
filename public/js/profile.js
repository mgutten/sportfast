// for profile pages user, team, group
var changedAlert = false;
var changedCaptain = false;
var changedName = false;
var changedAvatar = false;
var changedAdvanced = false;
var confirmAction;
var clickedCaptain;
var rosterLimits = new Object();
rosterLimits.basketball = {upper: 16};
rosterLimits.soccer	    = {upper: 22};
rosterLimits.football   = {upper: 22};
rosterLimits.volleyball = {upper: 16};
rosterLimits.tennis 	= {upper: 4};
rosterLimits.ultimate 	= {upper: 22};

$(function()
{
	
	/* hide buttons container if exists */
	if ($('.profile-options-inner-container').length > 0) {
		var ua = window.navigator.userAgent
		var msie = ua.indexOf ( "MSIE " )
		if (msie) {
			// Force non-cache of last img for IE
			var src = $('.profile-options-img').last().attr('src');
			var d = new Date();
			var n = d.getTime();
			
			$('.profile-options-img').last().attr('src', src + '?' + n); 
		}
		
		$('.profile-options-img').last().load(function() {
			
			var height = parseInt($('.profile-options-inner-container').height(),10) - parseInt($('.profile-options-button').outerHeight(),10);
			
			$('.profile-options-inner-container').css('margin-top', -height + 'px')
		})
	}
	
	/* show minimal signup if exists */
	if ($('#signup1-alert-container').length > 0) {
		// Is not signed up, show alert
		showAlert($('#signup1-alert-container'), .9);
		
		disableCloseAlerts();
		
		
		
		/* update narrow column name value onkeyup */
		$('#firstName,#lastName').keyup(function()
		{
			var firstOrLast = true;
			if ($(this).attr('id') == 'lastName') {
				firstOrLast = false;
			}
			
			var regexp = /^[a-zA-Z]+-*[a-zA-Z]*$/g;
			var isValid = $(this).isValid({regex: regexp})
			
			changeInputBackground($(this),isValid);
			
		})
		
		/* check email validation */
		$('#email').keyup(function()
		{
			
			var value   = $(this).val();
			var regex	= /^\S+@\S+\.\S+$/;
			var isValid = $(this).isValid({regex: regex});
			
			
			changeInputBackground($(this), isValid);
		})
		.focus(function()
		{
			$('#tooltip').children('#tooltip-body').html('<span class="heavy darkest">This email will be used to notify you of upcoming games.</span>');
			startTooltipTimer($(this));
		})
		.blur(function()
		{
			endTooltipTimer();
			$('#tooltip').hide();
		})
		
		/* validate signup password and reenter password */
		$('#signupPassword').keyup(function()
		{
			var value   = $.trim($(this).val());
			var isValid = $(this).isValid({minLength: 8, maxLength: 30});
			
			changeInputBackground($(this),isValid);
			
			//changeInputBackground($('#signupReenterPassword'), isValid);	
			
		})/*
		.focus(function()
		{
			$('#game-password-reqs').show();
		})
		.blur (function()
		{
			$('#game-password-reqs').hide();
		})*/
		
		/* show description for minimal signup on hover */
		$('.game-signup-hover').hover(function()
		{
			if ($(this).find('input').is(':focus')) {
				return;
			}
			$(this).children('.game-signup-hover-target').stop().animate({opacity: 1}, 300);
		}, function()
		{
			if ($(this).find('input').is(':focus')) {
				return;
			}
			$(this).children('.game-signup-hover-target').stop().animate({opacity: 0}, 300);
		})
		
		$('.game-signup-hover').find('input').focus(function()
		{
			$(this).parents('.game-signup-hover').children('.game-signup-hover-target').stop().animate({opacity: 1}, 300);
		})
		.blur(function()
		{
			$(this).children('.game-signup-hover-target').stop().animate({opacity: 0}, 300);
		})
		
		$('.games-signup-option-container').click(function()
		{
			
			var inputs = '#firstName,#lastName,#signupPassword';
			var email = '';
			if ($('#email').length > 0) {
				inputs += ',#email';
				email = $('#email').val();
			} else {
				email = $('#user-email').text();
			}
			
			
			
			var fail = false;
			
			$(inputs).trigger('keyup').each(function()
			{
				
				if ($(this).is('.input-fail')) {
					fail = true;
				}
			});
			
			
			
			if (fail) {
				return false;
			}
			
			if ($(this).is('#game-signup-minimal')) {
				// Minimal signup
				
				$('#user-signup').attr('action', '/signup/minimal').submit();
				//minimalSignup($('#firstName').val(), $('#lastName').val(), email, $('#signupPassword').val());
			} else {
				// Want full package
				$('#user-signup').attr('action', '/signup/basic').submit();
			}
			
			
			
		})
		
		if ($('#email').val().length > 0) {
		
			$('#email').trigger('keyup');
		}
		
	}

	
	
	/* fade in user description on mouseover */
	$(document).on('mouseenter.overlay','.profile-player-overlay-container',function() 
	{
		$(this).stop().animate({opacity: 1}, 300);
	})
	.on('mouseleave.overlay','.profile-player-overlay-container',function() 
	{
		$(this).stop().animate({opacity: 0}, 300);
	})
	
	$('#invite,#manage,#invite-to').click(function()
	{
		$(this).parents('#profile-buttons-container').css('height', '20em');
	});
	
	
	/* request to join button */
	$('#request-join-button').click(function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		var receivingUserID = 'captain';
		var action = 'join';
		var type   = idType.replace(/ID/, '');
		var details = 'request';
		
		
		showConfirmationAlert('Request sent');
		
		$(this).text('Request sent')
		$(this).removeClass('heavy')
		$(this).addClass('transparent default');
		$(this).unbind('click');
		
		createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
	})
	
	/* join */
	$('#join-button').bind('click.join',function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		var type;
		
		if (idType == 'gameID') {
			addUserToGame(idType, typeID, actingUserID);
			type = 'game';
		} else {
			// team
			addUserToTeam(typeID, actingUserID);
			type = 'team';
		}
		showConfirmationAlert('Added to ' + type);
	})
	
	/* post message to wall */
	$('#postMessage').submit(function(e)
	{
		e.preventDefault();
		
		if ($(this).attr('clicked') == 'true') {
			return false;
		}
		
		var inputEle = $(this).find('textarea');

		if (inputEle.val().length <= 0) {
			// No input value
			return false;
		}
		
		var detailsEle = getDetailsEle();
		
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		var message = inputEle.val();
		
		addPost(idType, typeID, actingUserID, message);
		
		$(this).attr('clicked', 'true');

	})
	
	$('.profile-manage-remove-player').click(function()
	{
		
		var imgEle = $(this).children('.box-img-container-medium').children('img');
		
		if ($('.profile-manage-remove-player-selected').length > 0) {
			// A player was already selected, undo it
			var opacity = $('.profile-manage-remove-player-selected').attr('opacity');
			$('.profile-manage-remove-player-selected').stop().animate({opacity: opacity}, 300);			

			$('.profile-manage-remove-player-selected').removeClass('profile-manage-remove-player-selected clicked');
		}
		
		imgEle.stop().animate({opacity: 1}, 200);
		
		imgEle.addClass('profile-manage-remove-player-selected clicked');
		imgEle.attr('userID', $(this).attr('userID'));

		$('#profile-manage-remove-player-name').text($(this).attr('playerName'));
		$('#profile-manage-remove-player-confirm-container').show();
	})
	
	/* remove player from group/team button clicked */
	$('#profile-manage-remove-player-remove').click(function()
	{
		var userID = $('.profile-manage-remove-player-selected').attr('userID');
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = userID;
		var receivingUserID;
		var action = 'leave';
		var type   = idType.replace(/ID/, '');
		var details;		
		
		createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
		removeUserFromType(userID, idType, typeID);
		changedAlert = $('.team-manage-remove-player-container');
		
		reloadPage();
		
	});
	
	/* show legend for color boxes of player section onhover */
	$('#team-players-container').hover(
		function() {
			$('.team-player-going-description-container').stop().animate({opacity: 1}, 300);
		},
		function() {
			$('.team-player-going-description-container').stop().animate({opacity: 0}, 300);
		}
	)
	
	/* show delete button for message if exists */
	$('.newsfeed-notification-container').hover(function()
	{
		var deleteButton = $(this).find('.profile-delete-message');
		if (deleteButton.length > 0) {
			deleteButton.show();
		}
	}, function()
	{
		var deleteButton = $(this).find('.profile-delete-message');
		if (deleteButton.length > 0) {
			deleteButton.hide();
		}
	});
	
	/* delete newsfeed message on click of delete button */
	$('.profile-delete-message').click(function()
	{
		var detailsEle = getDetailsEle();
		var type = getType();
		var messageID = $(this).parents('.profile-message-container').attr('messageID');

		deleteMessage(type, messageID);
	});
	
	
	/* ONCLICK OPTIONS SHOW ALERTS/REDIRECT */
	$('#profile-option-invite').click(function()
	{
		showAlert($('#invite-alert-container'));
	})
		
	$('#profile-option-reminder').click(function()
	{
		showAlert($('#reminders-alert-container'));
	})
	
	$('#profile-option-message').click(function()
	{
		showAlert($('#message-alert-container'));
	})
	
	
	$(document).on('click', '.team-manage-team-info-captain-real',function()
	{
		$('#team-manage-team-info-captain-container').show();
		$('#team-manage-team-info-confirm-container').show();
		clickedCaptain = $(this);
	})
	$(document).on('mouseover', '.team-manage-team-info-captain-real,.remove-captain', function()
	{
		if ($(this).is('.remove-captain')) {
			$(this).show()
		} else {
			$(this).next('.remove-captain').show();
		}
	}).on('mouseleave', '.team-manage-team-info-captain-real,.remove-captain', function()
	{
		if ($(this).is('.remove-captain')) {
			$(this).hide()
		} else {
			$(this).next('.remove-captain').hide();
		}
	})
	
	$(document).on('click', '.remove-captain',function()
	{
		
		var userID = $(this).prev('.team-manage-team-info-captain-real').attr('userID');
		var imgEle = $('#change-captain-' + userID).find('img');


		imgEle.removeClass('clicked full-opacity')
			  .addClass('not-clicked')
			  .css('opacity', '1')
			  .attr('opacity', '.667');
		
		imgEle.trigger('mouseleave.animateOpacity');

		$(this).prev('.team-manage-team-info-captain-real').remove();
		$(this).remove();
		
		$('#team-manage-team-info-confirm-container').show();
		changedCaptain = true;

	})
	
	$('#team-manage-team-info-add-captain').click(function()
	{

		var newEle = document.createElement('p');
		newEle.setAttribute('class', $('#change-captain-name-holder').attr('class'));
		
		var x = document.createElement('span');
		x.setAttribute('class', $('#change-captain-name-holder').attr('xclass'))
		x.innerHTML = 'x';
		
		if ($('.remove-captain').length > 0) {
			$('.remove-captain').last().after(newEle);
		} else {
			$('#change-captain-name-holder').after(newEle);
		}
		$(newEle).after(x);
		
		$('#team-manage-team-info-captain-container').show();
		$('#team-manage-team-info-confirm-container').show();
		clickedCaptain = $(newEle);
	});
		
	
	
	$('#team-manage-team-info-name').click(function()
	{
		$('#team-manage-team-info-confirm-container').show();
		changedName = true;
	})
	
	$('.team-manage-team-info-lower-container:eq(1)').children().children('.selectable-text,.selectable-input').click(function()
	{
		$('#team-manage-team-info-confirm-container').show();
		changedAdvanced = true;
	})
	
	/* roster limit is being changed on team details page */
	$('#team-manage-team-info-roster-limit').forceNumeric();
	
	$('#team-manage-team-info-roster-limit').keyup(function(event)
	{
		var detailsEle = getDetailsEle();
		var sport = detailsEle.attr('sport').toLowerCase();
		var upper = rosterLimits[sport].upper;
		var lower = rosterLimits[sport].lower;
		$(this).limitVal(1, upper);
		
	})
	
	/*  edit team info hover over change team captain 
	$('.team-manage-team-info-captain').hover(function()
	{
		clickedCaptain.text($(this).attr('playerName'));
	}, function()
	{
		clickedCaptain.text(clickedCaptain.attr('defaultName'));
	})*/
	
	/* change team captain 
	$('.team-manage-team-info-captain').click(function()
	{
		if ($('.team-manage-team-info-captain-selected').length > 0) {
			// Captain img was clicked before
			var opacity = $('.team-manage-team-info-captain-selected').attr('opacity');
			$('.team-manage-team-info-captain-selected').stop().animate({opacity: opacity}, 200);
			$('.team-manage-team-info-captain-selected').removeClass('clicked team-manage-team-info-captain-selected');
		}
		
		$(this).find('.animate-opacity').addClass('clicked team-manage-team-info-captain-selected');
		clickedCaptain.attr('defaultName', $(this).attr('playerName'))
					  .attr('userID', $(this).attr('userID'))
					  .attr('id', 'change-captain-name-' + $(this).attr('userID'))
					  .text($(this).attr('playerName'));
		
		changedCaptain = $(this).attr('userID');
		alertChanged = $('.team-manage-team-info-container');
	})*/
	
	$('.team-manage-team-info-captain').on('click','img.not-clicked', function() 
	{
		var parent = $(this).parents('.team-manage-team-info-captain');
		var userID = parent.attr('userID');
		if ($('#change-captain-name-' + userID).length > 0) { 
			// Do not allow same captain twice
			return;
		}
		
		$('#team-manage-team-info-add-captain').trigger('click');
		
		/*
		if ($('.team-manage-team-info-captain-selected').length > 0) {
			// Captain img was clicked before
			var opacity = $('.team-manage-team-info-captain-selected').attr('opacity');
			$('.team-manage-team-info-captain-selected').stop().animate({opacity: opacity}, 200);
			$('.team-manage-team-info-captain-selected').removeClass('clicked team-manage-team-info-captain-selected');
		}
		*/
		
		$(this).addClass('clicked team-manage-team-info-captain-selected full-opacity');
		$(this).removeClass('not-clicked full-opacity team-manage-team-info-captain-selected');
		
		clickedCaptain.attr('defaultName', $(this).attr('playerName'))
					  .attr('userID', userID)
					  .attr('id', 'change-captain-name-' + userID)
					  .text($(this).attr('playerName'));
		
		changedCaptain = true;
		alertChanged = $('.team-manage-team-info-container');
		
		
		
		var ele = $('.team-manage-team-info-captain-real').last();
		var name = $(this).parents('.team-manage-team-info-captain').attr('playername');
		ele.text(name);
	})
	
	
	/* change tab for team-info */
	$('.team-manage-team-info-tab').click(function()
	{
		if ($(this).is('.team-manage-team-info-tab-selected')) {
			// Already selected
			return;
		}
		
		var index = $(this).index();
		
		$('.team-manage-team-info-tab-selected').removeClass('team-manage-team-info-tab-selected');
		$(this).addClass('team-manage-team-info-tab-selected');
		
		$('.team-manage-team-info-lower-container').hide();
		$('.team-manage-team-info-lower-container:eq(' + index + ')').show();
	});	
	
	
	$('#profile-manage-remove-player-cancel,.alert-cancel-button').click(function()
	{
		$('.alert-black-back').trigger('click');
	})
	
	/* reload page if alert has changed present */
	$('.alert-black-back,.alert-x').click(function()
	{
		if (changedAlert !== false) {
			location.reload();
		}
	})
	
	$('#team-manage-team-info-save-changes').click(function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		
		if (changedCaptain) {
			// Captain was changed
			
			var userIDs = new Array();
			
			$('.team-manage-team-info-captain-real').each(function()
			{
				if ($(this).attr('userID')) {
					// Prevent non-attribute div/span from being added
					userIDs.push($(this).attr('userID'));
				}
			})
			
			changeCaptains(userIDs, idType, typeID);
		}
		
		if (changedName) {
			// Team, group name was changed
			var name = $('#team-manage-team-info-name').val();
			var actingUserID = detailsEle.attr('actingUserID');
			
			changeTypeName(name, idType, typeID, actingUserID);
		}
		
		if (changedAvatar) {
			// Avatar has been changed
			updateTeamAvatar(typeID, changedAvatar);
		}
		
		
		if (changedAdvanced) {
			testAdvancedChanges();
		}
		
		reloadPage();
		
		showConfirmationAlert('Changes saved');
	});
	
	
	$('#game-canceled-leave').click(function()
	{
		var detailsEle = getDetailsEle();
		var userID = detailsEle.attr('actingUserID');
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = userID;
		var receivingUserID;
		var action = 'leave';
		var type   = idType.replace(/ID/, '');
		var details;		
		
		createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
		removeUserFromType(userID, idType, typeID);
		changedAlert = $('.team-manage-remove-player-container');
		reloadPage();
	})
	
	/* user willingly leaves team */
	$('#leave-button, #profile-option-leave').click(function()
	{
		
		var detailsEle = getDetailsEle();
		var type = getType();
		var captains = detailsEle.attr('captains');
		
		if ((captains.search(detailsEle.attr('actingUserID')) !== -1) && 
			 captains.length == 1 &&
			 detailsEle.attr('recurring') != 'true') {
			// User is still team captain, do not let leave without passing the torch
			showConfirmationAlert('You must choose someone to be the new ' + type + ' captain (under EDIT ' + type.toUpperCase() + ')');
			return;
		}
		
		confirmAction = function () {
				var detailsEle = getDetailsEle();
				var userID = detailsEle.attr('actingUserID');
				var idType = detailsEle.attr('idType');
				var typeID = detailsEle.attr(idType);
				var actingUserID = userID;
				var receivingUserID;
				var action = 'leave';
				var type   = idType.replace(/ID/, '');
				var details;		
				
				createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
				removeUserFromType(userID, idType, typeID);
				changedAlert = $('.team-manage-remove-player-container');
				reloadPage();
		}
		
		var name = (typeof detailsEle.attr('teamName') == 'undefined' ? 'this game' : detailsEle.attr('teamName'));
		populateConfirmActionAlert('leave ' + name);
		
	});
	
	if ($('#profile-buttons-container-holder').length > 0) {
	/* align top options animate bar */
		var top = $('#profile-buttons-container-holder').position().top;
		$('#profile-buttons-container').css('top', top);
	}
	
	$('#unsubscribe-button, #top-alert-subscribe, #subscribe').click(function()
	{
		
		var detailsEle = getDetailsEle();
		var subscribe = ($(this).is('#top-alert-subscribe') || $(this).is('#subscribe') ? 1 : 0);
		
		confirmAction = function () {
				var detailsEle = getDetailsEle();
				var userID = detailsEle.attr('actingUserID');
				var idType = detailsEle.attr('idType');
				var typeID = detailsEle.attr(idType);
				
				subscribeToType(userID, idType, typeID, subscribe);
				changedAlert = $('.team-manage-remove-player-container');
			
		}
		
		if (subscribe) {
			// Do not show confirmation for subscribing
			showConfirmationAlert('You are now subscribed');
			confirmAction();
			return;
		}
		
		var name = (typeof detailsEle.attr('teamName') == 'undefined' ? 'this game' : detailsEle.attr('teamName'));
		populateConfirmActionAlert('unsubscribe');
	});
	
	/* close subscribe green-alert-box on game page */
	$('#subscribe-x').click(function()
	{
		$(this).parents('.green-alert-box').hide();
	})
	
	
	$('.profile-join-player-container').click(function()
	{
		$('#join-button').trigger('click');
	})
	
	$('#confirm-action-alert-container').on('click.confirm', '#confirm-action', function()
	{

		confirmAction();
		
		// Prevent double fire of confirmAction
		$('#confirm-action-alert-container').off('click.confirm', '#confirm-action');
	})
	
	$('#deny-action').click(function()
	{
		$(this).parents('.alert-container').find('.alert-x').trigger('click');
	})
	
	if ($('#addToGame-alert-container').length > 0) {
		// Show alert
		showAlert($('#addToGame-alert-container'));
	}
	
	/* invite user from invite button */
	$(document).on('click.invite','.invite-search-result,.profile-invite-result',function()
	{
		var classy;
		var searchBar;

		if ($(this).is('.invite-search-result')) {
			classy = $('.invite-search-result');
			searchBar = $('#inviteSearchBar');
		} else {
			// Is new profile-invite-result from invite alert
			classy = $('.profile-invite-result');
			searchBar = $('#inviteSearchAlert');
		}
		
		classy.remove();
		 
		searchBar.val('')
				 .focus();
							
		
		var detailsEle = getDetailsEle();
		var actingUserID = detailsEle.attr('actingUserID');
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var receivingUserID = $(this).attr('userID');
		var action = 'invite';
		var type   = idType.replace(/ID/, '');
		var details;	
		
		inviteUserToType(idType, typeID, receivingUserID);	
		
		//createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
		showConfirmationAlert('Invite sent');
		
		return false; // prevent bug that closes animated div after selecting a name from the results
	})
	

	
	if ($('#success-alert-container').length > 0) {
		// Invites were sent on previous page, show alert
		showAlert($('#success-alert-container'));
	}
	
	$('.profile-animate-buttons, .profile-options-button').click(function()
	{
		animateProfileButtons();
	})
	.mouseenter(function()
	{
		if (parseInt($('.profile-buttons-inner-container').css('margin-left'), 10) > 0) {
			animateProfileButtons();
		}
	})
	
	/* show animatable options container for first time team/game 
	var detailsEle = getDetailsEle();
	if (detailsEle) {
		// Bug fix with failed js on ratings page
		if (detailsEle.attr('firstType') == 'true') {
			animateProfileButtons();
		}
	}
	*/
	
	/* select all pending invites in section */
	$('.profile-pending-all-select').click(function()
	{
		$(this).parents('.profile-pending-container').find('.game-subscribers-container.selectable').addClass('selected');

	})
	
	/* deselect all pending invites in section */
	$('.profile-pending-all-deselect').click(function()
	{
		$(this).parents('.profile-pending-container').find('.game-subscribers-container').removeClass('selected');
	})

	$('.profile-pending-invite').click(function()
	{
	
		var numInvites = 0;
		if ((numInvites = $(this).parents('.profile-pending-container').find('.game-subscribers-container.selected').length) == 0) {
				showConfirmationAlert('Please select players to send invites');
				return;
			}
		
		var curEle = $(this);
		
		confirmAction = function() {
			
			var emails = '';
			var counter = 0;
			
			
			curEle.parents('.profile-pending-container').find('.game-subscribers-container.selected').each(function() {
				if (counter != 0) {
					emails += ',';
				}
				emails += $(this).attr('email');
				
				counter++;
			});	
			
			var detailsEle = getDetailsEle();
			var idType	   = detailsEle.attr('idType');
			var typeID	   = detailsEle.attr(idType);

			inviteUserToType(idType, typeID, '', emails, reloadPage);

		}
		
		populateConfirmActionAlert('send invites to ' + numInvites + ' players');
	})
	
	$('.profile-pending-remove').click(function()
	{
	
		var numSelected = 0;
		if ((numSelected = $(this).parents('.profile-pending-container').find('.game-subscribers-container.selected').length) == 0) {
				showConfirmationAlert('Please select players to remove');
				return;
			}
		
		var curEle = $(this);
		
		confirmAction = function() {
		
			var emails = '';
			var counter = 0;
			
			curEle.parents('.profile-pending-container').find('.game-subscribers-container.selected').each(function() {
				if (counter != 0) {
					emails += ',';
				}
				emails += $(this).attr('email');
				
				counter++;
			});	
			
			
			var detailsEle = getDetailsEle();
			var idType	   = detailsEle.attr('idType');
			var typeID	   = detailsEle.attr(idType);

			deleteInvites(idType, typeID, emails, reloadPage);

		}
		
		populateConfirmActionAlert('remove ' + numSelected + ' players from the list');
	})
	
	/* show action buttons onhover */
	$('.profile-pending-container').hover(function()
	{
		$(this).find('.profile-pending-actions-container').stop().animate({opacity: 1}, 300);
	}, function()
	{
		$(this).find('.profile-pending-actions-container').stop().animate({opacity: 0}, 300);
	})
	
	
	$(document).click(function(e)
	{
		if ((($(e.target).parents('.profile-buttons-innermost-container').length > 0 && !$(e.target).is('a')) ||
			 $(e.target).is('.profile-buttons-innermost-container')) ||
					$(e.target).is('.alert-black-back') || 
					$(e.target).is('.alert-x')) {
				 // For animating div on games page
				 return false;
		} else if (parseInt($('.profile-buttons-inner-container').css('margin-left'),10) == 0) {
				animateProfileButtons();
		} else if ($(e.target).parents('#profile-buttons-container').length > 0) {

				return false;
		}
		
		if (dropdowns.dropdownMenuDown) {
			// Dropdown menu is down
			$(e.target).parents('#profile-buttons-container').animate({height: '7em'},300);
		}
	})
	
	
})


/** 
 * delete game/team invites
 */
function deleteInvites(idType, typeID, emails, callback)
{
	var options = {idType: idType,
				   typeID: typeID,
				   emails: emails};
				   
	$.ajax({
		url: '/ajax/delete-invites',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			if (typeof callback != 'undefined') {
				callback();
			}
		}
	})
}


/** 
 * minimal signup on game/team page
 */
function minimalSignup(firstName, lastName, email, password)
{
	var options = {firstName: firstName,
				   lastName: lastName,
				   email: email,
				   password: password};
				   
	$.ajax({
		url: '/ajax/minimal-signup',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			reloadPage();
		}
	})
}


/**
 * update game_subscribers doNotEmail for game
 * @params (onOrOff => 1 = no emails, 0 = emails)
 */
function updateEmailAlert(gameID, onOrOff)
{
	var options = {gameID: gameID,
				   onOrOff: onOrOff};
				   
	$.ajax({
		url: '/ajax/update-email-alert-subscribed-game',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			showConfirmationAlert('Updated');
		}
	})
}

/**
 * ajax function to delete user message
 */
function deleteMessage(type, messageID)
{
	var options = {type: type,
				   messageID: messageID};
				   
	$.ajax({
		url:'/ajax/delete-message',
		type: 'POST',
		data: {options:options},
		success: function(data) {
			reloadPage();
		}
	})
}


/**
 * get details container which should hold all necessary info for team/group info
 */
function getDetailsEle()
{
	var ele;
	
	if ($('#team-details').length > 0) {
		// Team page
		ele = $('#team-details');
	} else if ($('#game-details').length > 0) {
		// Game page
		ele = $('#game-details');
	} else if ($('#user-details').length > 0) {
		// User page
		ele = $('#user-details')
	} else {
		return false;
	}
	
	return ele;
}

/**
 * get type ("team", "game")
 */
function getType()
{
	var detailsEle = getDetailsEle();
	var type = detailsEle.attr('idType').replace('ID','');
	
	return type;
}


/**
 * invite user to type (game or team)
 * @params (idType => 'gameID' or 'teamID',
 *			typeID => gameID or typeID,
 *			receivingUserID => user who is being invited,
 *			emails => str of comma separated emails
 */
function inviteUserToType(idType, typeID, receivingUserID, emails, callback)
{
	if (idType == 'gameID') {
		var options = {gameID : typeID,
					   userIDs: receivingUserID};
	} else {
		var options = {teamID : typeID,
					   userIDs: receivingUserID};
	}
	
	if (typeof emails != 'undefined') {
		options.emails = emails;
	}
		   
	$.ajax({
		url:'/mail/invite-type',
		type: 'POST',
		data: options,
		success: function(data) {
			if (typeof callback != 'undefined') {
				callback();
			}
		}
	})
}

function updateTeamAvatar(teamID, changedAvatar)
{
	var options = {teamID: teamID,
				   avatar: changedAvatar};
	$.ajax({
		url:'/ajax/update-team-avatar',
		type: 'POST',
		data: {options: options},
		complete: function(data) {
		}
	})
}

/**
 * cancel/delete game or team
 * @params (onceOrAlways => for recurring games, can cancel game just this week or all weeks)
 */
function cancelType(idType, typeID, onceOrAlways)
{
	onceOrAlways = (typeof onceOrAlways == 'undefined' ? '' : onceOrAlways);
	var reason   = ($('#cancel-reason').length > 0 ? $('#cancel-reason').val() : '');
	
	var options = {idType: idType,
				   typeID: typeID,
				   onceOrAlways: onceOrAlways,
				   cancelReason: reason};

	$('*').css('cursor', 'progress');
	   
	$.ajax({
		url:'/ajax/cancel-type',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			reloadPage();
		}
	})
}


/**
 * unsubscribe/subscribe user from recurring game
 * @params(userID => userID,
 *		   idType => 'gameID',
 *		   typeID => gameID,
 *		   subscribe => 1 for subscribing or 0 for unsubscribing)
 */
function subscribeToType(userID, idType, typeID, subscribe)
{
	var options = new Object();
	options.userID = userID;
	options.idType = idType;
	options.typeID = typeID;
	options.subscribe = subscribe;
	
	
	$.ajax({
		url: '/ajax/subscribe-to-type',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			reloadPage();
		}
	})
}

/**
 * Add post message to team or group wall
 * @params (idType => "teamID", "groupID",
 *			typeID => actual teamID or groupID,
 *			actingUserID => id of user who posted,
 *			message => what was written by the user (limited to 300 characters)
 */
function addPost(idType, typeID, actingUserID, message)
{
	var options = new Object();
	options.idType = idType;
	options.typeID = typeID;
	options.actingUserID = actingUserID;
	options.message = message;
	
	$.ajax({
		url: '/ajax/add-post',
		type: 'POST',
		data: {options: options},
		success: function(data) {

			var receivingUserID;
			var action = 'post';
			var type   = idType.replace(/ID/, '');
			var details;
			
			createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
			showConfirmationAlert('Post submitted');
			reloadPage();

		}
	})
}

/**
 * change team or group's location (city)
 * @params (city => name of new city,
 *			idType => 'teamID' or 'groupID',
 *			typeID => actual id for teamID or groupID
 */
function changeTypeLocation(city, idType, typeID)
{
	var options = new Object();
	options.city = city;
	options.typeID = typeID;
	options.idType = idType;

	changeTypeAttribs(options);
}

/**
 * change team or group's visibility (public or private)
 * @params (public => "public" or "private",
 *			idType => 'teamID' or 'groupID',
 *			typeID => actual id for teamID or groupID
 */
function changeTypePublic(public, idType, typeID)
{
	var options = new Object();
	options.public = public;
	options.typeID = typeID;
	options.idType = idType;
	
	changeTypeAttribs(options);
}

/**
 * change team or group's sport
 * @params (sport  => "basketball" etc
 *			idType => 'teamID' or 'groupID',
 *			typeID => actual id for teamID or groupID
 */
function changeTypeSport(sport, idType, typeID)
{
	var options = new Object();
	options.sport = sport;
	options.typeID = typeID;
	options.idType = idType;
	
	changeTypeAttribs(options);
}

/**
 * change team or group's roster limit
 * @params (rosterLimit  => int,
 *			idType => 'teamID' or 'groupID',
 *			typeID => actual id for teamID or groupID
 */
function changeTypeRosterLimit(rosterLimit, idType, typeID)
{
	var options = new Object();
	options.rosterLimit = rosterLimit;
	options.typeID = typeID;
	options.idType = idType;
	
	changeTypeAttribs(options);
}

/**
 * Ajax request to submit changes to team or group
 * @params (options => object with values set)
 */
function changeTypeAttribs(options)
{
	$.ajax({
		url: '/ajax/change-type-attribs',
		type: 'POST',
		data: {options: options},
		success: function(data) {
		}
	})
}

/**
 * add user as member (game or team)
 * @params (gameID => gameID,
 *			userID => userID of member being added
 */
function addMemberToGame(gameID, userID, callback)
{

	var options = {gameID: gameID,
				   userID: userID};
	   
	$.ajax({
		url:'/ajax/add-member-to-game',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			if (typeof callback != 'undefined') {
				callback(data);
			}
		}
	})
}

/**
 * populate invite alert with results from db search

function populateSearchResultsInviteAlert(results)
{
	var output = '';

	if (results.length < 1) {
		// No results
		output += "<div class='header-search-result dark-back medium'>No results found</div>";
	} else {
		// Results found
		var limit = (results.length > 7 ? 7 : results.length);
		for (i = 0; i < limit; i++) {
			
			var tooltip = '';
			if (results[i]['name'].length > 22) {
				// Limit any name to 20 characters
				tooltip = "tooltip='" + results[i]['name'] + "'";
				results[i]['name'] = results[i]['name'].substring(0,21) + '..';
				
			}
			
			output += "<div class='invite-search-result clear medium pointer animate-darker' userID='" + results[i]['id'] + "'>\
							<p class='medium clear invite-search-result-name' " + tooltip + ">" + results[i]['name'] + "</p>";
			
			if (results[i]['prefix'] !== 'users') {
				// Not users, show what "type" result is
				output += "<p class='clear-right medium smaller-text header-search-result-subtext'>" + capitalize(results[i]['prefix'].slice(0,-1)) + "</p>";
			}
			
			output += "</div>";
						

		}
	}
	
	$('.dropdown-menu-option-default').hide();
	
	$('#invite-alert-results').html(output);
	
	if ($('#inviteSearchAlert').is(':focus') && $('#inviteSearchResult').val().length >= 3) {
		// Search bar has focus and val is greater than 2 (protect against accidently overfire due to ajax delay
		$('#invite-alert-results').show();
	}
	
	
	var searchBar = $('#inviteSearchAlert');
	var searchVal = searchBar.val();
	$('.invite-search-result').highlight(searchVal);
}
	 */


/**
 * test which inputs have been changed in the advanced section of team-info alert
 */
function testAdvancedChanges()
{
	
	var detailsEle = getDetailsEle();
	var idType	   = detailsEle.attr('idType');
	var typeID	   = detailsEle.attr(idType);
	$('.team-manage-team-info-lower-container:eq(1)').each(function()
	{
		$(this).children('.selectable-container').each(function()
		{
			var section = $(this).attr('section');
			var initialVal = detailsEle.attr(section);
			var newVal = '';
			
			$(this).children('.selectable-text.green-bold,.selectable-input').each(function() {
				if ($(this).is('input[type=text]')) {
					// Input element
					newVal += $(this).val();
					return;
				}
				newVal += $(this).text();
			})
			
			newVal = newVal.toLowerCase();
			
			if (initialVal.toLowerCase() !== newVal) {
				// Section was changed
				if (section == 'city') {
					// City was changed
					changeTypeLocation(newVal, idType, typeID);
				} else if (section == 'sport') {
					// Sport was changed
					changeTypeSport(newVal, idType, typeID);
				} else if (section == 'public') {
					// Visibility was changed
					changeTypePublic(newVal, idType, typeID)
				} else if (section == 'rosterLimit') {
					changeTypeRosterLimit(newVal, idType, typeID);
				}
				
			}
		})
	})
}


/**
 * animate profile buttons container
 */
function animateProfileButtons()
{
	//$('.profile-options-outer-container').animate({'height': '6em'}, 300);
	var downMargin = -5;
	
	var marginTop = parseInt($('.profile-options-inner-container').css('margin-top'),10) - downMargin;
	
	if (marginTop < 0) {
		// Is up, move down
		$('.profile-options-outer-container').animate({'height': parseInt($('.profile-options-inner-container').height(),10) + 'px'}, 300);
		
		$('.profile-options-button').text('hide options')
		
		$('.profile-options-inner-container').animate({marginTop: downMargin + 'px'}, 300);
	} else {
		$('.profile-options-outer-container').animate({'height': '24px'}, 300);
		
		$('.profile-options-button').text('show options');
		
		var upMargin = parseInt($('.profile-options-inner-container').height(),10) - parseInt($('.profile-options-button').outerHeight(),10);
		
		$('.profile-options-inner-container').animate({marginTop: -upMargin + 'px'}, 300);
	}
	/*
	var width = parseInt($('.profile-buttons-innermost-container').innerWidth(),10) + parseInt($('.profile-animate-buttons').innerWidth(), 10);
	var marginLeft = '14em';
	
	if ($('#profile-buttons-container').width() < width) {
		$('#profile-buttons-container').css('width', width);
		$('.profile-buttons-inner-container').css('margin-left', marginLeft);
		
		$('.profile-buttons-inner-container').animate({marginLeft: 0}, 300);
		//$('#profile-buttons-container').animate({'width': width}, 300);
	} else {
		//$('#profile-buttons-container').animate({'width': $('.profile-animate-buttons').width()}, 300);
		
		$('.profile-buttons-inner-container').animate({marginLeft: marginLeft}, {duration: 300, complete: function() {
																													$(this).css('margin-left', 0);
																													$('#profile-buttons-container').css('width', '1em')
		}});
	}
	*/
	
	return;
	
	var innerEle = $('.profile-buttons-inner-container');
	var marginLeft = parseInt(innerEle.css('margin-left'), 10);
	
	if (marginLeft > 0) {
		innerEle.animate({marginLeft: 0}, 300);
	} else {
		var newMargin = innerEle.width() - $('.profile-animate-buttons').width();
		innerEle.animate({marginLeft: newMargin}, 300);
	}
}
			
			
			
			