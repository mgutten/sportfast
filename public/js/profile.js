// for profile pages user, team, group
var changedAlert = false;
var changedCaptain = false;
var changedName = false;
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
	/* fade in user description on mouseover */
	$(document).on('mouseenter.overlay','.profile-player-overlay-container',function() 
	{
		$(this).stop().animate({opacity: 1}, 300);
	})
	.on('mouseleave.overlay','.profile-player-overlay-container',function() 
	{
		$(this).stop().animate({opacity: 0}, 300);
	})
	
	
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
	$('#join-button').click(function()
	{
		var detailsEle = getDetailsEle();
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var actingUserID = detailsEle.attr('actingUserID');
		
		addUserToGame(idType, typeID, actingUserID);
		showConfirmationAlert('Added to game');
	})
	
	/* post message to wall */
	$('#postMessage').submit(function(e)
	{
		e.preventDefault();
		
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
		$(this).prev('.team-manage-team-info-captain-real').remove();
		$(this).remove();
		
		$('#team-manage-team-info-confirm-container').show();
		changedCaptain = true;

	})
	
	$('#team-manage-team-info-add-captain').click(function()
	{
		"<p class='clear largest-text darkest heavy team-manage-team-info-name pointer team-manage-team-info-captain-real' >\
									</p><span class='left header red hidden largest-text remove-captain pointer'>x</span>";
		var newEle = document.createElement('p');
		newEle.setAttribute('class', $('.team-manage-team-info-captain-real').attr('class'));
		
		var x = document.createElement('span');
		x.setAttribute('class', $('.remove-captain').attr('class'))
		x.innerHTML = 'x';
		
		$('.remove-captain').last().after(newEle);
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
	
	/*  edit team info hover over change team captain */
	$('.team-manage-team-info-captain').hover(function()
	{
		clickedCaptain.text($(this).attr('playerName'));
	}, function()
	{
		clickedCaptain.text(clickedCaptain.attr('defaultName'));
	})
	
	/* change team captain */
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
					  .text($(this).attr('playerName'));
		
		changedCaptain = $(this).attr('userID');
		alertChanged = $('.team-manage-team-info-container');
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
				userIDs.push($(this).attr('userID'));
			})

			changeCaptains(userIDs, idType, typeID);
		}
		
		if (changedName) {
			// Team, group name was changed
			var name = $('#team-manage-team-info-name').val();
			var actingUserID = detailsEle.attr('actingUserID');
			
			changeTypeName(name, idType, typeID, actingUserID);
		}
		
		if (changedAdvanced) {
			testAdvancedChanges();
		}
		
		reloadPage();
		
		showConfirmationAlert('Changes saved');
	});
	
	/* user willingly leaves team */
	$('#leave-button').click(function()
	{
		
		var detailsEle = getDetailsEle();
		var captains = detailsEle.attr('captains');
		
		if ((captains.search(detailsEle.attr('actingUserID')) !== false) && 
			 captains.length == 1) {
			// User is still team captain, do not let leave without passing the torch
			showConfirmationAlert('You must choose someone to be the new team captain (under Team Info)');
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
		$('#confirm-action-alert-container').show();
		
		var opacity = $('.alert-black-back').css('opacity');
		$('.alert-black-back').css({display: 'block',
								   opacity: 0})
							  .animate({opacity: opacity}, 200);
	});
	
	
	$('.profile-join-player-container').click(function()
	{
		$('#join-button').trigger('click');
	})
	
	$('#confirm-action').click(function()
	{
		confirmAction();
	})
	
	$(document).on('click','.invite-search-result',function()
	{
		var detailsEle = getDetailsEle();
		var actingUserID = detailsEle.attr('actingUserID');
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var receivingUserID = $(this).attr('userID');
		var action = 'invite';
		var type   = idType.replace(/ID/, '');
		var details;		
		
		createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
		showConfirmationAlert('Invite sent');
	})
	
	
})

/**
 * get details container which should hold all necessary info for team/group info
 */
function getDetailsEle()
{
	return ($('#team-details').length > 0 ? $('#team-details') : $('#game-details'))
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
				
			
			
			
			