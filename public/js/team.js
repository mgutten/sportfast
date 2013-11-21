// Team profile page js
var clickedDay;
var teamID;
var typing;

$(function()
{
	teamID = $('#team-details').attr('teamID');
	
	
	/* OPTIONS ONCLICK SHOW ALERT/REDIRECT */
	$('#profile-option-reserves').click(function()
	{
		showAlert($('#reserves-alert-container'));
	})
	
	$('#profile-option-schedule').click(function()
	{
		showAlert($('#manage-schedule-alert-container'));
	})
	
	$('#profile-option-edit').click(function()
	{
		showAlert($('#manage-team-info-alert-container'));
	})
	
	$('#profile-option-delete').click(function()
	{
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

						//createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);

						cancelType(idType, typeID);
						$('*').css('cursor', 'progress');
						
						//changedAlert = true;
						//reloadPage();
				}
				
		var detailsEle = getDetailsEle();
		var text = 'delete ' + detailsEle.attr('teamName');
		var postContent = "<p class='clear margin-top light width-100 center'>This action cannot be undone.</p>";

		populateConfirmActionAlert(text, postContent);
	})
	
	$('.schedule-in,.schedule-out').click(function()
	{
		//reloadPage();
	})
	
	
	/* click on edit team's logo */
	$('#team-manage-team-info-img').click(function()
	{
		$('#team-manage-team-info-img-container').show();
		
	})
	
	/* team reserves */
	$('#team-show-reserves').click(function()
	{
		$(this).hide();
		$('#team-hide-reserves').show();
		$('#team-reserves-container').show();
	})
	
	$('#team-hide-reserves').click(function()
	{
		$(this).hide();
		$('#team-show-reserves').show();
		$('#team-reserves-container').hide();
	})
	
	/* highlight/remove highlight of reserve player */
	$('.team-reserve-player').click(function()
	{
		var img = $(this).find('.animate-opacity');
		
		if (img.is('.clicked')) {
			img.removeClass('clicked');
		} else {
			img.addClass('clicked');
		}
		
		testReserveInvites();
	});
	
	$('#team-reserve-invite').click(function()
	{
		
	
		var detailsEle = getDetailsEle();
		var teamID = detailsEle.attr('teamID');
		var teamGameID = detailsEle.attr('nextGameID');
		
		var reserves = getReserveInvites();
		
		$('#team-hide-reserves').trigger('click');
		
		inviteToTeamGame(teamID, teamGameID, reserves);
	});
	
	/* show reserve alert on click of "add" in reserve list */
	$('#team-reserve-add').click(function()
	{
		showAlert($('#reserves-alert-container'));
	})
	
	
	/* remove user from reserves */
	$(document).on('click', '.team-reserve-remove', function()
	{
		var detailsEle = getDetailsEle();
		var teamID = detailsEle.attr('teamID');
		var userID = $(this).attr('userID');
		
		addUserToReserve(teamID, userID, true);
		
		$(this).parents('.team-reserve-player').remove();
		
		changedAlert = true;
	})

	
	/* edit team img avatar */
	$('.create-team-avatar').hover(function()
	{
		var src = $(this).attr('src').replace('/small/','/medium/');
		$('#team-manage-team-info-img').attr('src', src);
	},
	function()
	{
		var src = $('.create-team-avatar-selected').attr('src').replace('/small/','/medium/');
		$('#team-manage-team-info-img').attr('src', src);
	})
	.click(function()
	{
		$('.create-team-avatar-selected').removeClass('create-team-avatar-selected');
		$(this).addClass('create-team-avatar-selected');
		
		changedAvatar = $(this).attr('avatar');
		
		$('#team-manage-team-info-confirm-container').show();
	})
		
	
	/* clear entire team schedule */
	$('#team-manage-calendar-clear').click(function()
	{
		confirmAction = function() {
			
			var detailsEle = getDetailsEle();
			var teamID = detailsEle.attr('teamID');
			
			removeTeamGames(teamID);
		}
		
		populateConfirmActionAlert('delete your entire schedule', "<span class='clear medium width-100 center margin-top'>This action cannot be undone.</span>");
	})
	
	$('#manage-schedule-alert-container').find('.calendar-day').click(function(e)
	{
		e.preventDefault();
		
		if ($(this) == clickedDay) {
			// Same day was clicked
			return;
		}
		
		clickedDay = $(this);
		var hour = 24;
		var date = new Date();
		
		
		if (typeof $(this).attr('time') != 'undefined' &&
			$(this).is('.calendar-today')) {
				// Is today and there was an event, should show win/loss?
				var timeArray = parseTimeAttrib($(this).attr('time'));
				hour = parseInt(timeArray.hour, 10) + (timeArray.ampm == 'p' ? 12 : 0);
			}
				
		
		if (($(this).is('a.calendar-transparent,a.calendar-more-transparent') && !$(this).is('a.calendar-no-select'))
			|| ($(this).is('.calendar-today') && 
				$(this).is('.calendar-dark') &&
				hour < date.getHours())) {
			// Old event (or event that happened earlier in the day today), let change win or loss
			var tooltip = $('#tooltip-team-manage-winOrLoss');
			tooltip.css({left: $(this).position().left,
						 top:  $(this).position().top + $(this).height()});
			tooltip.show();
			tooltip.stop().animate({'opacity': 1}, 300);
			
			// Hide other tooltip
			$('#tooltip-team-manage-addGame').find('.x').trigger('click');
			
			// Populate tooltip with info
			$('#tooltip-team-manage-winOrLoss-opponent').text($(this).attr('opponent'));
			
			$('#tooltip-team-manage-winOrLoss').find('.inner-shadow,.member-schedule-button-selected')
											   .removeClass('inner-shadow member-schedule-button-selected');
											   
			var winOrLoss = $(this).attr('winOrLoss');
			var selectedButton;
			
			if (winOrLoss == 'W') {
				// Highlight win button
				selectedButton = $('.tooltip-team-manage-winOrLoss-button').first();
			} else if (winOrLoss == 'L') {
				// Loss
				selectedButton = $('.tooltip-team-manage-winOrLoss-button:eq(1)');
			}
			selectedButton.addClass('inner-shadow member-schedule-button-selected');

		} else if ($(this).is('.calendar-transparent')) {
			// Click day from next or previous month
			if ($(this).is('.calendar-last-month')) {
				// last month triggered, move back if possible
				$(this).parents('.calendar-container').find('#calendar-left-arrow').trigger('click');
			} else if($(this).is('.calendar-next-month')) {
				// next month triggered, move forward if possible
				$(this).parents('.calendar-container').find('#calendar-right-arrow').trigger('click');
			}
			return;
		} else {
			// Previously scheduled event or no scheduled event, edit details
			var tooltip = $('#tooltip-team-manage-addGame');
			var date = getDateClicked($(this));
			$('#team-manage-schedule-date').text(date.monthName + ' ' + date.day);
			
			$('#teamManageScheduleLocation').attr('locationID', '');
			
			// Hide other tooltip
			$('#tooltip-team-manage-winOrLoss').find('.x').trigger('click');
			
			if (typeof $(this).attr('opponent') !== 'undefined') {
				// Has been previously set, populate details
				var opponent = $(this).attr('opponent');
				var location = $(this).attr('location');
				var address  = $(this).attr('address');
				var time	 = $(this).attr('time');
				var leagueLocationID = (typeof $(this).attr('leagueLocationID') !== 'undefined' ? $(this).attr('leagueLocationID') : '');
				
				populateManageScheduleAddGameOpponent(opponent, time);
				populateManageScheduleLocation(location, address);
				$('#teamManageScheduleLocation').attr('locationID', leagueLocationID);
				
				tooltip.find('input').each(function() {
					fadeOutInputOverlay($(this), false);
				})
				
				tooltip.find('.delete-button').show();
				
			} else {
				tooltip.find('#teamManageScheduleOpponent').val('');
				tooltip.find('#teamManageScheduleLocation').val('');
				tooltip.find('#teamManageScheduleAddress').val('');
				
				tooltip.find('input').each(function() {
					fadeOutInputOverlay($(this), false);
				})
				
				tooltip.find('.delete-button').hide();
				
			}
			
			tooltip.css({left: $(this).position().left,
						 top:  $(this).position().top + $(this).height()})
				   .show()
				   .animate({opacity: 1}, 300);
		}
	});
	
	
	$('#teamManageScheduleLocation,#teamManageScheduleAddress').keyup(function()
	{
		var address = $('#teamManageScheduleAddress').val();
		var name	= $('#teamManageScheduleLocation').val();
		
		if ($(this).val().length < 3) {
			return;
		}
		clearTimeout(typing);
		
		typing = setTimeout(function() {
			searchDbForLeagueLocation(name, address, populateLeagueLocationResults);
		}, 200);
	});
	

	$('.x').click(function()
	{
		var parentEle = $(this).parents('.tooltip-container');
		parentEle.animate({'opacity': 0}, {duration: 300, complete: function() {
																			  parentEle.hide();
																			  }
		})
	})
	
	
	/* result for league location search clicked*/
	$(document).on('click','.team-manage-schedule-result',function() {
		var locationName = $(this).text();
		var typeID		 = $(this).attr('typeID');
		var address		 = $(this).attr('address');
		
		$('#teamManageScheduleLocation').attr('locationID', typeID);
		populateManageScheduleLocation(locationName, address);
	})
	
	/* win or loss button clicked for manage schedule popup */
	$('.tooltip-team-manage-winOrLoss-button').click(function()
	{
		if ($(this).is('.member-schedule-button-selected')) {
			// Already selected
			return false;
		}
		
		$(this).siblings('.member-schedule-button-selected').removeClass('member-schedule-button-selected inner-shadow');
		$(this).addClass('member-schedule-button-selected inner-shadow');
		
		clickedDay.attr('winOrLoss', $(this).text()[0]);
		clickedDay.children('p.calendar-old-event').text($(this).text()[0]);
		
		// Used when asked to save all changes
		changedAlert = $('#manage-schedule-alert-container');
		
		var teamGameID = clickedDay.attr('typeID');
		var winOrLoss = clickedDay.attr('winOrLoss');
		addWinOrLossToDb(teamGameID, winOrLoss);
		
	})
	
	$('.tie').click(function()
	{
		clickedDay.attr('winOrLoss', $(this).text()[0]);
		clickedDay.children('p.calendar-old-event').text($(this).text()[0]);

		var teamGameID = clickedDay.attr('typeID');
		var winOrLoss = clickedDay.attr('winOrLoss');
		
		addWinOrLossToDb(teamGameID, winOrLoss);
	})
	
	$('.remove-game').click(function()
	{

		var teamGameID = clickedDay.attr('typeID');
		
		addWinOrLossToDb(teamGameID, 'delete');
		
		// Used when asked to save all changes
		changedAlert = $('#manage-schedule-alert-container');
		showConfirmationAlert('Game removed.');
	})
	
	
	$('#tooltip-team-manage-addGame').find('.delete-button').click(function()
	{
		$('#tooltip-team-manage-addGame').hide();
		
		clickedDay.removeClass('calendar-dark')
				  .css('background','')
				  .attr('color','');
				  
		var teamGameID = (typeof clickedDay.attr('typeID') !== 'undefined' ? clickedDay.attr('typeID') : '');
		
		removeTeamGame(teamGameID);
		changedAlert = $('#manage-schedule-alert-container');
				 
	})
	
	
	/* save game from manage schedule */
	$('#tooltip-team-manage-addGame').find('.save-button').click(function()
	{
		var fail = false;
		$('#tooltip-team-manage-addGame').find('input').each(function()
		{
			if ($(this).is('#teamManageScheduleOpponent')) {
				return;
			}
			
			if ($(this).val() == '') {
				// Input is empty, do not submit
				$(this).addClass('input-fail');
				fail = true;
			}
		})
		
		if (fail) {
			return false;
		}
		
		var opponent = $('#teamManageScheduleOpponent').val()
		var location = $('#teamManageScheduleLocation').val()
		var address  = $('#teamManageScheduleAddress').val()
		var time     = $('#teamManageScheduleHour').val() + ':' + $('#teamManageScheduleMinute').val() + $('#teamManageScheduleAmPm').val()
		var locationID = (typeof $('#teamManageScheduleLocation').attr('locationID') !== 'undefined' ? $('#teamManageScheduleLocation').attr('locationID') : '');
		var teamGameID = (typeof clickedDay.attr('typeID') !== 'undefined' ? clickedDay.attr('typeID') : '');
		var month	 = clickedDay.parents('.calendar-container').find('.calendar-month-name').attr('monthID');
		var year     = clickedDay.parents('.calendar-container').find('.calendar-month-name').attr('yearID');
		var day		 = clickedDay.children('p').text();
								
		clickedDay.attr('opponent', opponent)
		clickedDay.attr('location', location)
		clickedDay.attr('address', address)
		clickedDay.attr('time', time)
		
		clickedDay.attr('color','');
		clickedDay.css('background-color', '');
		clickedDay.addClass('calendar-dark');
		
		$('#tooltip-team-manage-addGame').hide();
		
		addGameToDb(teamGameID, opponent, time, month, day, year, location, address, locationID);
		showConfirmationAlert('Schedule saved.');
		
		// Used when asked to save all changes
		changedAlert = $('#manage-schedule-alert-container');
	})
	
	/* if want "save changes?" alert to pop up after changes have been made
	$('.alert-black-back,.alert-x').click(function(e)
	{
		e.stopPropagation();
		
		if ($('#changes-alert-container').css('display') == 'block' || !changedAlert) {
			$('.alert-black-back,.alert-x').trigger('default.click');
			return;
		}
		
		$('.alert-container').hide();
		$('#changes-alert-container').show();

	})
	*/
	
	
	/* manage button was clicked */
	$('#dropdown-menu-manage').children('.dropdown-menu-option-container').click(function()
	{
		
		var val = $(this).children('p.dropdown-menu-option-text').text().toLowerCase();
		
		if (val == 'schedule') {
			showAlert($('#manage-schedule-alert-container'));
		} else if (val == 'remove player') {
			showAlert($('#manage-remove-player-alert-container'));
		} else if (val == 'edit team') {
			showAlert($('#manage-team-info-alert-container'));
		} else if (val == 'reserves') {
			showAlert($('#reserves-alert-container'))
		} else if (val == 'delete team') {
			
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
						
						//createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details);
						
						cancelType(idType, typeID);
						
						//changedAlert = true;
						//reloadPage();
				}
				
				var detailsEle = getDetailsEle();
				var text = 'delete ' + detailsEle.attr('teamName');
				var postContent = "<p class='clear margin-top light width-100 center'>This action cannot be undone.</p>";
				
				populateConfirmActionAlert(text, postContent);
				
		}
		

	});
	
	
	
	$('.calendar-captain').click(function()
	{
		showAlert($('#manage-schedule-alert-container'));
	})
	
	/* show manage schedule alert when click narrow-column calendar */
	$('.narrow-column-calendar-container').find('a.calendar-day').click(function(e)
	{
		e.preventDefault();
		var detailsEle = getDetailsEle();
		if (detailsEle.attr('captain') == '1') {
			// User is captain, allow popup
			$('#manage-schedule-alert-container,.alert-black-back').show();
		}
	})
	
	if ($('#canceled-alert-container').length > 0) {
		// Team has been deleted
		showAlert($('#canceled-alert-container'));
		
		$('.alert-black-back,.alert-x').unbind('click.default');
		
		$('.alert-x').hide();
	}
	

	addAnimateDarkerToManageCalendar()
	
	
})

/**
 * invite user(s) to team game
 */
function inviteToTeamGame(teamID, teamGameID, reserves)
{
	
	var options = {teamID: teamID,
				   teamGameID: teamGameID,
				   userIDs: reserves};
	
	$.ajax({
		url: '/ajax/invite-to-team-game',
		type: 'POST',
		data: {options: options},
		success: function(data) {

			showConfirmationAlert('Invites sent');
		}
	})
}

/**
 * remove team games from db
 */
function removeTeamGames(teamID)
{

	$.ajax({
		url: '/ajax/remove-team-games',
		type: 'POST',
		data: {teamID: teamID},
		success: function(data) {
			reloadPage();
		}
	})
}

/**
 * remove team game from db
 */
function removeTeamGame(teamGameID)
{
	$.ajax({
		url: '/ajax/remove-team-game',
		type: 'POST',
		data: {teamGameID: teamGameID},
		success: function(data) {
		}
	})
}
/**
 * add animate darker to all calendar days (within manage alert)
 */
function addAnimateDarkerToManageCalendar()
{
	$('.team-manage-calendar').children('.calendar-container')
							  .children(':not(.calendar-transparent,.calendar-transparent.calendar-more-transparent,.calendar-month-container), .calendar-dark')
							  .addClass('animate-darker')
}

/**
 * Ajax call to edit winOrLoss for teamGameID in db
 * @params (teamGameID => teamGameID,
 *			winOrLoss => "W", "L", "T", or "delete")
 */
function addWinOrLossToDb(teamGameID, winOrLoss)
{

	$.ajax({
		url: '/ajax/add-team-game',
		type: 'POST',
		data: {teamGameID: teamGameID,
			   winOrLoss: winOrLoss}
	})
}

/**
 * Ajax call to add/edit teamGame in db
 * @params (teamGameID 	=> teamGameID,
 *			opponent    => name of opponent,
 *			time 		=> time of event (eg 6:00pm) string,
 *			month 		=> month (eg 01 = january),
 *			day 		=> day of month (eg 1,2,15),
 *			location 	=> name of location,
 *			address 	=> street address of location
 *			locationID	=> if location was already found in db, locationID is set otherwise '')
 */
function addGameToDb(teamGameID, opponent, time, month, day, year, location, address, locationID)
{

	$.ajax({
		url: '/ajax/add-team-game',
		type: 'POST',
		data: {teamGameID: teamGameID,
			   opponent: opponent,
			   time: time,
			   month: month,
			   day: day,
			   year: year,
			   location: location,
			   address: address,
			   locationID: locationID,
			   teamID: teamID},
		success: function(data) {
			}
	})
}
/**
 * Ajax call to search db for league location by name and address
 * @params (name => name of location,
 *			address => address of location,
 *			callback => function to call on success)
 */
function searchDbForLeagueLocation(name, address, callback)
{
	$.ajax({
		url: '/ajax/search-db-for-league-location',
		type: 'POST',
		data: {locationName: name,
			   address: address},
		success: function(locations) {
					locations = JSON.parse(locations);
					
					callback(locations);
				}
	})
}

/**
 * Ajax call to add user to reserve list of team
 */
function addUserToReserve(teamID, userID, remove)
{
	if (typeof remove == 'undefined') {
		remove = '';
	}
	
	$.ajax({
		url: '/ajax/add-user-to-reserve',
		type: 'POST',
		data: {teamID: teamID,
			   userID: userID,
			   remove: remove},
		success: function(data) {
				
				}
	})
}

/**
 * result clicked from search function in reserve alert
 */
function addUserToList(userID, userName)
{
	changedAlert = true;
	
	$('#team-reserve-none').hide();
	
	var detailsEle = getDetailsEle();
	var teamID = detailsEle.attr('teamID');
	
	addUserToReserve(teamID, userID);
	
	//var copiedEle = $('#team-reserve-alert-container').children().first();
	//var copiedHTML = copiedEle.html();
	
	//var div = "<div class='" + copiedEle.attr('class') + "'></div>";
	//$('#team-reserve-alert-container').append(div);
	
	var space = userName.indexOf(" ")
	var lastLetter = userName[space + 1];
	var firstName = userName.substr(0,space);
	userName = firstName + ' ' + lastLetter

	
	var newEle = $('#team-reserve-alert-container').children('.team-reserve-player').last().clone()
												   .removeClass('hidden')
												   .appendTo('#team-reserve-alert-container');
	
	newEle.find('.team-reserve-remove').attr('userID', userID);
	
	newEle.find('.team-reserve-name').html(userName);
	
	newEle.find('img').attr('onError', "this.src='/images/users/profile/pic/medium/default.jpg'")
					  .attr('src', '/images/users/profile/pic/medium/' + userID + '.jpg');
}
	

/**
 * test if invites are available to be sent to reserve players
 */
function testReserveInvites()
{
	var shown = getReserveInvites();
	
	if (shown) {
		// Show invites button
		$('#team-reserve-invite').show();
	} else {
		$('#team-reserve-invite').hide();
	}
}

/**
 * get all the invited reserves
 */
function getReserveInvites()
{
	var returnArray = new Array();
	$('.team-reserve-player').each(function()
	{
		if ($(this).find('img').is('.clicked')) {
			var userID = $(this).attr('userID');
			returnArray.push(userID);
		}
	})
	
	if (returnArray.length < 1) {
		return false;
	}
	
	return returnArray;
}

function populateLeagueLocationResults(locations)
{
	var output = '';

	if (locations.length == 0) {
		// No locations
		output += "<p class='left medium'>No locations found</p>";
	} else {
	
		for (i = 0; i < locations.length; i++) {
			if (locations[i]['name'].length > 24) {
				locations[i]['name'] = locations[i]['name'].slice(0,21) + '..';
			}
			
			output += "<p class='team-manage-schedule-result clear heavy animate-darker pointer' typeID='" + locations[i]['id'] + "' address='" + locations[i]['address'] + "'>";
			output += locations[i]['name'];
			output += "</p>";
		}
	}
	
	$('#team-manage-schedule-location-results').html(output);
}

/**
 * populate manage-schedule-addGame location and address
 */
function populateManageScheduleLocation(locationName, address)
{
	$('#teamManageScheduleLocation').val(locationName);
	$('#teamManageScheduleAddress').val(address);
	
	fadeOutInputOverlay($('#teamManageScheduleLocation'), false);
	fadeOutInputOverlay($('#teamManageScheduleAddress'), false);
}


function populateManageScheduleAddGameOpponent(opponent, time)
{
	var timeArray = parseTimeAttrib(time);
	$('#teamManageScheduleHour').val(timeArray.hour);
	$('#teamManageScheduleMinute').val(timeArray.min);
	$('#teamManageScheduleAmPm').val(timeArray.ampm);
	
	$('#teamManageScheduleOpponent').val(opponent);
	
}

/**
 * parse time from (eg 6:00pm) to 'hour' => 6, 'min' => 00, 'ampm' => pm
 */
function parseTimeAttrib(time) {
	var timeArray = new Object();
	
	timeArray.hour = time.match(/(\d+):/)[1];
	timeArray.min  = time.match(/:(\d+)/)[1];
	timeArray.ampm = time.match(/\d+([am|pm])/)[1];
	
	return timeArray;
	
}

function getDateClicked(calendarEle) {
	var date = new Object();
	
	date.day = calendarEle.attr('id').replace(/calendar-/,'');
	var monthEle = calendarEle.parents('.calendar-container').find('.calendar-month-name');
	date.monthName = monthEle.text();
	date.month = monthEle.attr('monthID');
	
	return date;
}
	