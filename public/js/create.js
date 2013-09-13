// create.js
var daysOfWeek = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var months = ["Jan",
			  "Feb",
			  "Mar",
			  "Apr",
			  "May",
			  "Jun",
			  "Jul",
			  "Aug",
			  "Sep",
			  "Oct",
			  "Nov",
			  "Dec"];
			  
var selectedDay;
var sportID;
var sport;
var markers = new Array(); 
var markerDetails = new Array();
var infowindow;
var zoomChanged;
var userMarker;
var userContent;
var selectedPark;
var teamName;
var selectedAvatar;

$(function()
{
	$('.create-game-sport').click(function()
	{
		var sport = $(this).attr('sport');
		$('.create-game-sport-selected').removeClass('create-game-sport-selected');
		$(this).addClass('create-game-sport-selected');
		
		$('#create-header-sport').text(sport);
		$('#create-header-sport').attr('clicked', 'true')
		
		$('.create-game-sport-type-container').hide();
		
		if (typeof $(this).attr('missingSport') != 'undefined') {
			// User is missing this sport from profile, show link to account settings
			$('#missingSport').text(sport)
			$('#missingSport-container').show();
			$('.create-section-inner').css('visibility', 'hidden');
			return;
		} else {
			$('.create-section-inner').css('visibility','visible');
			$('#missingSport-container').hide();
		}
		
		selectSport($(this));
		
		testDate();
	})
	.mouseenter(function()
	{
		if (typeof $('#create-header-sport').attr('clicked') == 'undefined') {
			
			var sport = $(this).attr('sport');
			$('#create-header-sport').text(sport);
		}
	})
	
	$('.create-game-sport-type').click(function()
	{
		var sport = $(this).parents('.create-game-sport-type-container').attr('sport');

		selectSport($('#' + sport));
	})
	
	
	$('.calendar-selectable').addClass('animate-darker');
	
	$('.calendar-selectable').click(function()
	{
		$('.calendar-day-selected').stop()
								   .css('background','')
								   .removeClass('calendar-day-selected')
								   .addClass('animate-darker')
								   .attr('color', $('.calendar-day').css('background-color'))
		
		$(this).stop()
			   .css('background','')
			   .addClass('calendar-day-selected')
			   .removeClass('animate-darker')
			   .attr('color', $(this).css('background-color'))
			   
		$('.create-game-time-container').fadeIn();
		
		if (!selectedDay) { 
			animateNextSection($(this).parents('.create-section'))
		}
		
		var monthEle = $(this).siblings('.calendar-month-container').find('.calendar-month-name')
		var day = $(this).children('p').text();
		var month = monthEle.attr('monthID');
		var year  = monthEle.attr('yearID');
		
		selectDay(day, month, year);
		
		testDate();
		
	})
	
	$('.dropdown-menu-options-container').children('.dropdown-menu-option-container').click(function()
	{
		var parentID  = $(this).parents('.dropdown-menu-options-container').attr('dropdown-menu')
		var selectEle = $('#' + parentID).find('p.dropdown-menu-option-text');
		
		var text = $(this).children('p').text();
		selectEle.text(text);
		
		$(document).trigger('click');
		
		testDate();
	})
	
	
	$('#create-visibility,#create-recurring').find('.selectable-text').click(function()
	{
		var value = $(this).text().toLowerCase();
		var id = $(this).parent().attr('id').replace(/create-/g,'');
		var hiddenEle = $('#' + id);
		
		hiddenEle.val(value);
	})
	
	$('#skillLimitCheckbox,#ageLimitCheckbox').change(function()
	{
		var hiddenInputs = $(this).parent().next();
		if ($(this).prop('checked')) {
			hiddenInputs.show();
		} else {
			hiddenInputs.hide();
		}
	})
	
	$('#skillLimitMin,#skillLimitMax,#ageLimitMin,#ageLimitMax').keyup(function()
	{
		if ($(this).is('#ageLimitMin,#ageLimitMax')) {
			// Is age, limit 17 to 100
			$(this).limitVal(17,100);
		} else {
			// Is skill
			$(this).limitVal(65,100);
		}
	})
	
	$('#skillLimitMin,#skillLimitMax,#ageLimitMin,#ageLimitMax,#rosterLimit, #minPlayers').forceNumeric();
			
	
	/* update narrow column park name for custom park */
	$(document).on('keyup','#parkName',function()
	{
		selectPark($(this).val());
	})
	
	/* update narrow column teamname on keyup */
	$('#teamName').keyup(function()
	{
		var value = $(this).val();
		$('#narrow-column-teamName').text(value);
		
		if (typeof teamName == 'undefined') {
			animateNextSection($(this).parents('.create-section'));
		}
		
		teamName = value;
	})
	
	
	/* show alert for create team avatar */
	$('#create-teamInfo-avatar-container').click(function()
	{
		showAlert($('#avatars-alert-container'));
	})
	
	$('.create-team-avatar').hover(function()
	{
		var src = $(this).attr('src').replace('/small/','/large/');
		$('#create-team-avatar-alert-img').attr('src', src);
	},
	function()
	{
		var src = $('.create-team-avatar-selected').attr('src').replace('/small/','/large/');
		$('#create-team-avatar-alert-img').attr('src', src);
	})
	.click(function()
	{
		$('.create-team-avatar-selected').removeClass('create-team-avatar-selected');
		$(this).addClass('create-team-avatar-selected');
		
		var avatar = $(this).attr('avatar');
		selectAvatar(avatar);
	})
		
		
	
	/* validate form pre-submit */
	$('#create-game-submit').click(function()
	{
		if ($('#parkNameHidden').val() == '') {
			$('#parkNameHidden').val('Unnamed Location');
		}
		
		$(this).parents('form').submit();
	})
	
	/* validate form pre-submit */
	$('#create-team-submit').click(function(e)
	{
		
		if ($('#teamName').val() == '') {
			showConfirmationAlert('Please enter a team name.');
			return false;
		}
		
		
		$(this).parents('form').submit();
	})
	
	
	if (isGame()) {
		// Create game, initialize google map
		
		initializeMap(37.98, -122.5, 11, mapLoaded);
	}
	
})


/**
 * find leagues in same city as user
 */
function findLeagues(sports)
{
	var options = {sports: sports,
				   limit: 6};
	
	$.ajax({
		url: '/ajax/find-leagues',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			data = JSON.parse(data);
			populateLeagues(data);
		}
	})
	
}

/**
 * ajax call to sport information
 * @params (sportID)
 */
function getSportInfo(sportID)
{
	
	$.ajax({
		url: '/ajax/get-sport-info',
		type: 'POST',
		data: {sportID: sportID},
		success: function(data) {
			data = JSON.parse(data);
			
			populateSportInfo(data);
		}
	})
}


/**
 * ajax call to retrieve total number of available players
 * @params (dayOfWeek => 0-6,
 *			hour 	  => hour of day (0-23),
 *			sportID   => sportID,
 */
function getAvailableUsers(month, date, year, hour, sportID)
{
	var options = new Object();
	options.month = month;
	options.date = date;
	options.year = year;
	options.hour = hour;
	options.sportID = sportID;
	
	$.ajax({
		url: '/ajax/get-available-users',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			
			var percent = Math.floor(data * .25)
			//$('#available-players-percent').text(percent);
			$('#available-players').text(data);
			
			if ($('#available-players-container').css('display') != 'block') {
				$('#available-players-container').css({'opacity': 0,
													   'display': 'block'})
												 .animate({'opacity': 1}, 300);
			}
		}
	})		
}

/**
 * ajax call to find any games that are on the same day as this one
 */
function getSimilarGames(month, date, year, sportID)
{
	$('*').css('cursor', 'progress');
	
	var options = new Object();
	options.month = month;
	options.date = date;
	options.year = year;
	options.sportID = sportID;

	$.ajax({
		url: '/ajax/get-similar-games',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			data = JSON.parse(data);
			populateSimilarGames(data);
			
			$('*').css('cursor', '');
		}
	})		
}

/**
 * ajax to find all parks within bounds
 */
function findParks(options)
{

	$.ajax({
		url: '/ajax/find-parks',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			
			data = JSON.parse(data);
			
			gmapMarkers  = new Array();
			markerDetails = new Array();
			if (typeof data[1] != 'undefined') {
				
				for (i = 0; i < data[1].length; i++) {
					gmapMarkers.push([data[1][i][0],data[1][i][1]]);
					markerDetails.push([data[2][i][0],data[2][i][1],data[2][i][2],data[2][i][3], data[2][i][4]]);
				}
			}
			
			createMarkers();	
			
		}
	})
}

	
/**
 * first time map is loaded
 */
function mapLoaded()
{
	// Hide map until it is time to view it
	google.maps.event.addListenerOnce(gmap, 'idle', function(){
		$('.create-section-inner-gmap').css({display:'none'});
	});
	
	mapListeners();
}

/**
 * create map listeners
 */
function mapListeners()
{
	google.maps.event.addListener(gmap, "rightclick", function(event)
    {
		if (userMarker) {
			userMarker.setMap(null);
		}
		userMarker = createMarker(event.latLng, gmap);
		
		markerHoverListeners(userMarker);
		
		userMarker.setIcon('/images/global/gmap/markers/green_reverse.png');
		
		markerClickListeners(userMarker, userContent, $('#parkName').text(), 0);
		
		//new google.maps.event.trigger(userMarker, 'click');
		
		if (infowindow) {
			infowindow.close();
		}
		
		infowindow = new google.maps.InfoWindow({
									  content: userContent,
									  position: event.latLng,
									  maxWidth: 200
								  });
			
		infowindow.open(gmap, userMarker)
		
		var parkLocation = 'POINT' + event.latLng;
		
		$('#parkLocation').val(parkLocation);
		

	})
	
	
	createMarkers();
}

/**
 * create gmap markers
 */
function createMarkers()
{	
	
	// Clear prior markers
	clearMarkers();
	
	var marker, i, latLon, index;
	var bounds  = new google.maps.LatLngBounds();
	
	
	google.maps.event.addListener(gmap, 'zoom_changed', function(event) {
		// Set timeout to prevent event from triggering more than once
			clearTimeout(zoomChanged);
			zoomChanged = setTimeout(mapMoved, 50);

	})
	
	google.maps.event.addListener(gmap, 'dragend', function(event) {
		// Set timeout to prevent event from triggering more than once
			clearTimeout(zoomChanged);
			zoomChanged = setTimeout(mapMoved, 50);
	})
	
	
	
	if (gmapMarkers.length == 0) {
		return false;
	}
	
	
	for (i = 0; i < gmapMarkers.length; i++) {
		// Create markers here
		
		latLng = new google.maps.LatLng(gmapMarkers[i][0], gmapMarkers[i][1]);
		marker = createMarker(latLng, gmap);
		
		markers.push(marker);

		addMarkerListeners(marker, i);
			

	}
	
}


function addMarkerListeners(marker, count)
{
		
		markerHoverListeners(marker, markerDetails[count][0])
		
		var content =  "<p class='darkest futura heavy'>" + markerDetails[count][0] + "</p><div class='clear'>" + markerDetails[count][1] + "</div>";
		
		if (parseInt(markerDetails[count][2],10) == 1) {
			content += "<div class='clear' tooltip='Stash available'><img src='/images/global/logo/logo/green/tiny.png' class='left indent'/> <p class='left light smaller-text larger-margin-top'>Stash Available</p></div>";
		}
		if (parseInt(markerDetails[count][4],10) > 0) {
			// membershipRequired
			content += "<p class='clear red' tooltip='This location requires a membership'>Membership Required</p>";
		}
		
		markerClickListeners(marker, content, markerDetails[count][0], markerDetails[count][3]);
}

function markerClickListeners(marker, content, parkName, parkID)
{

	google.maps.event.addListener(marker, "click", function(e) {
		
			this.parkName = parkName;
			this.parkID   = parkID;
			
			
			selectPark(this.parkName, this.parkID);
			
			if (infowindow) {
				infowindow.close();
			}
			
			infowindow = new google.maps.InfoWindow({
									  content: content,
									  position: e.latLng,
									  maxWidth: 200
								  });
			
			infowindow.open(gmap, marker)
		})
}


function markerHoverListeners(marker, parkName)
{
	google.maps.event.addListener(marker, "mouseover", function(e) {
		
		$('#parkName-main').text(parkName);
		$('#parkName-main-container').show();
		
		this.setIcon('/images/global/gmap/markers/green_reverse.png');
	});
	
	google.maps.event.addListener(marker, "mouseout", function(e) {
		
		if (selectedPark) {
			$('#parkName-main').text(selectedPark);
		} else {
			$('#parkName-main').text('');
		}
		this.setIcon('/images/global/gmap/markers/green.png');
	});
}



function mapMoved()
{
	
	var points = new Array();
	var bounds = gmap.getBounds();
	points[0] = 'POINT(' + bounds.getNorthEast().lat() + ',' + bounds.getNorthEast().lng() + ')';
	points[1] = 'POINT(' + bounds.getSouthWest().lat() + ',' + bounds.getSouthWest().lng() + ')';
	
	
	// what courts are needed?
	var courts;
	if (sport == 'basketball' || sport == 'tennis') {
		// Specific court required
		courts = [sport];
	} else if (!sport) {
		// Is blank, no restriction
		courts = '';
	} else {
		// Require field
		courts = ['field']
	}
	
	var options = {points: points,
				   courts: courts};
	
	
	findParks(options);
}



/**
 * test whether all data necessary to search db for available players is inputted
 */
function testDate()
{
	var success = true;
	
	if ($('.calendar-day-selected').length < 1) {
		success = false;
	}
	
	if (!sportID) {
		// No sport is selected
		success = false;
	}
		
	if (success) {
		// Success, perform AJAX
		var hour = roundHour();
		var date  = selectedDay.getDate();
		var month = selectedDay.getMonth() + 1; // add one to account for javascript date array offset (jan = 0)
		var year  = selectedDay.getFullYear();
		
		getAvailableUsers(month, date, year, hour, sportID);
		getSimilarGames(month, date, year, sportID);
		
		populateNarrowColumnTime();
		
	} else {
		return success;
	}

}

/**
 * sport was selected, update narrow column
 */
function selectSport(ele) 
{
	if (!sport) {
		animateNextSection(ele.parents('.create-section'));
	}
	
	sport = ele.attr('sport').toLowerCase();
	
	// Set hidden ele
	$('#sport').val(sport);
	
	var type = false;
	
	if ($('#create-' + sport + '-type').length > 0) {
		// Type choices exists
		$('#create-' + sport + '-type').show();
		type = true;
	}
	
	sportID = ele.attr('sportID');
	
	var src = $('#narrow-column-pic').attr('src').replace(/\w+.png/,sport + '.png');
	$('#narrow-column-pic').attr('src', src);
	
	$('#sportID').val(sportID);
	
	if (!type) {
		$('#narrow-column-sport-typeName').text('Pickup');
		$('#narrow-column-sport-typeSuffix').text('');
		
		$('#typeName').val('pickup');
		$('#typeSuffix').val('');
	} else {
		var typeName = $('.create-game-sport-typeName-container').children('.selectable-text.green-bold').text();
		var typeSuffix = $('.create-game-sport-typeSuffix-container').children('.selectable-text.green-bold').text();
		$('#narrow-column-sport-typeName').text(typeName);
		$('#narrow-column-sport-typeSuffix').text(typeSuffix);
		
		$('#typeName').val(typeName.toLowerCase());
		$('#typeSuffix').val(typeSuffix.toLowerCase());
	}
	
	$('#narrow-column-sport').text(capitalize(sport));
	$('#narrow-column-team').show();
	
	if (isGame()) {
		mapMoved();
	} else {
		// Is team, would perform search for leagues
		/*
		var sports = new Array(sport);
		findLeagues(sports);
		*/
	}
	getSportInfo(sportID);
}

/**
 * day was selected, update narrow column
 */
function selectDay(day, month, year)
{
	
	selectedDay = new Date(year, (month - 1), day);
	
}

/**
 * park was selected
 */
function selectPark(parkName, parkID) 
{
	if (!selectedPark) {
		// Park has not been selected before
		animateNextSection($('#parkName-main-container').parents('.create-section'));
	}
	selectedPark = parkName;
	
	if (typeof parkID != 'undefined') {
		// parkID is set
		$('#parkID').val(parkID);
		$('#parkLocation').val('');
	} else {
		// Not set, should be a user-added park
		$('#parkID').val('');
	}
	
	$('#parkNameHidden').val(parkName);
		
	$('#narrow-column-park').text(parkName);
	$('#parkName-main').text(parkName);
	
	$('#parkName-main-container').show();
}

/**
 * select avatar
 * @ parameters (avatar => name of avatar (eg "spartan" or "trident")
 */
function selectAvatar(avatar)
{
	var largeSrc = '/images/teams/avatars/large/' + avatar + '.jpg';
	var medSrc   = largeSrc.replace('/large/','/medium/');
	
	$('#narrow-column-pic').attr('src',largeSrc);
	$('#create-teamInfo-avatar').attr('src', medSrc);
	
	$('#avatar').val(avatar + '.jpg');
}


/**
 * convert hour to 0-23 scale
 */
function getHour()
{
	var hour = parseInt($('#hour').find('p.dropdown-menu-option-text').text(),10);
	var min	 = parseInt($('#min').find('p.dropdown-menu-option-text').text(),10);
	var ampm = $('#ampm').find('p.dropdown-menu-option-text').text().toLowerCase();
	
	if (ampm == 'pm' && hour != 12) {
		// Add 12 if not 12 o clock
		hour += 12;
	} else if(ampm == 'am' && hour == 12) {
		hour = 0;
	}
	
	return hour;
}


function roundHour()
{
	
	var hour = getHour();
	var min  = parseInt($('#min').find('p.dropdown-menu-option-text').text(),10);
	
	if (min > 15) {
		// minute is greater than 15 (:30 or :45), check availabilities for next hour
		hour += 1;
	}	
	
	return hour;
}

/**
 * test if is Create Game page
 */
function isGame()
{

	if (($('#create-type').text().toLowerCase() == 'game') || pageType == 'game') {
		return true;
	} else {
		// Is team
		return false;
	}
}

function populateNarrowColumnTime()
{
	if (selectedDay) {
		// day has been selected
		var hour = parseInt($('#hour').find('p.dropdown-menu-option-text').text(),10);
		var min	 = parseInt($('#min').find('p.dropdown-menu-option-text').text(),10);
		var ampm = $('#ampm').find('p.dropdown-menu-option-text').text().toLowerCase();
		var minutePart = ':' + min;
		
		if (min == 0) {
			minutePart = '';
		}
		
		var text = hour + minutePart + ampm;
		
		$('#narrow-column-time').text(text);
		
		$('#narrow-column-date').text(months[selectedDay.getMonth()] + ' ' + selectedDay.getDate())
		
		
		var curMonth = parseInt(selectedDay.getMonth(),10) + 1; //add one to deal with array offset of function
		var curDay   = parseInt(selectedDay.getDate(),10);
		var curHour  = getHour();
		var curMin   = $('#min').find('.dropdown-menu-selected').find('p').text();

		curDay   = (curDay > 9 ? curDay : '0' + curDay);
		curMonth = (curMonth > 9 ? curMonth : '0' + curMonth);
		curHour  = (curHour > 9 ? curHour : '0' + curHour);
		
		var datetime = selectedDay.getFullYear() + '-' + curMonth + '-' + curDay + ' ' + curHour + ':' + curMin + ':00';

		// Hidden input ele
		$('#datetime').val(datetime);
	}
}

/**
 * populate inputs with returned data from getSportInfo
 * @params (data => json object with "gameRosterLimit", "teamRosterLimit", "minPlayers")
 */
function populateSportInfo(data)
{
	
	var rosterLimit;
	
	if (isGame()) {
		// Is game page
		rosterLimit = data.gameRosterLimit;
	} else {
		rosterLimit = data.teamRosterLimit;
	}
	
	
	
	$('#rosterLimit').val(rosterLimit);
	$('#minPlayers').val(data.minPlayers);
	
}

/**
 * populate similar games from ajax call
 */
function populateSimilarGames(results)
{
	var output = '';
	
	if (results.length > 0) {
		// Results!
		
		for (i = 0; i < results.length; i++) {
			if (i > 1) {
				// Only allow 2 results
				break;
			}
			
			var result = results[i];
			output += "<a href='/games/" + result['gameID'] + "' class='clear create-similar-game-container animate-darker' target='_blank'>";
			output += 	"<img src='/images/parks/profile/pic/medium/" + result['parkID'] + ".jpg' onerror=\"this.src='/images/parks/profile/pic/medium/default.jpg'\" class='left'/>";
			output +=	"<div class='left larger-indent'>";
			output +=		"<p class='left largest-text heavy darkest'>" + result['gameTitle'] + "</p>";
			output +=		"<p class='clear darkest'>" + result['date'] + "</p>";
			output +=		"<p class='clear darkest'>" + result['hour'] + "</p>";
			output +=		"<p class='clear darkest'>" + result['parkName'] + "</p>";
			output +=	"</div>";
			output +=	"<div class='right'>";
			output +=		"<p class='left largest-text heavy darkest width-100 center'>" + result['totalPlayers'] + "/" + result['rosterLimit'] + "</p>";
			output +=		"<p class='clear darkest heavy larger-text width-100 center create-similar-game-players'>players</p>";
			output +=	"</div>";
			output += "</a>";
		}
		
		$('#create-similar-games-outer-container').show();
		$('#create-similar-games-container').html(output);
		//$('#create-similar-games-header').show();
		
	} else {
		output  = "<p class='width-100 center light larger-margin-top'>There are no " + sport + " games scheduled for this day.</p>";	
		$('#create-similar-games-outer-container').hide();
	}
	
	
}
	

/** 
 * populate leagues section with returned ajax results
 */
function populateLeagues(results)
{

	var output = '';
	if (results.length > 0) {
		// Results!
		
		for (i = 0; i < results.length; i++) {
			var result = results[i];
			output += "<div class='clear create-team-league-container'>";
			output +=	"<img src='/images/global/sports/icons/small/solid/medium/" + sport + ".png' class='left'/>";
			output += 	"<p class='darkest largest-text left indent'>" + result['leagueName'] + "</p>";
			output += "</div>";
		}
		
	}
	
	$('#create-team-leagues-container').html(output);	
	
}


/**
 * animate next section
 */
function animateNextSection(curSection)
{
	var nextSection = curSection.next('.create-section');
	
	if (nextSection.is('.create-section-gmap')) {
		// Is gmap section, undo special hiding of map
		nextSection.children().css('z-index','1');
	}
	
	animateNotShow(nextSection.children(), false, false);
}
		
	
