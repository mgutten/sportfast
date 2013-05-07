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
	
	/* update narrow column park name for custom park */
	$(document).on('keyup','#parkName',function()
	{
		selectPark($(this).val());
	})
	
	
	if (isGame()) {
		// Create game, initialize google map
		initializeMap(37.98, -122.5, 11, mapListeners);
	}
	
})

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
			$('#available-players').text(data);
			$('#available-players-container').css({'opacity': 0,
												   'display': 'block'})
										     .animate({'opacity': 1}, 300);
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
					markerDetails.push([data[2][i][0],data[2][i][1],data[2][i][2]]);
				}
			}
			
			createMarkers();		
			
		}
	})
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
		
		markerClickListeners(userMarker, userContent, $('#parkName').text());
		
		new google.maps.event.trigger(userMarker, 'click');
		

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
		
		markerHoverListeners(marker)
		
		var content =  "<p class='darkest futura heavy'>" + markerDetails[count][0] + "</p><div class='clear'>" + markerDetails[count][1] + "</div>";
		
		if (parseInt(markerDetails[count][2],10) == 1) {
			content += "<div class='clear' tooltip='Stash available'><img src='/images/global/logo/logo/green/tiny.png' class='left indent'/> <p class='left light smaller-text larger-margin-top'>Stash Available</p></div>";
		}
		
		markerClickListeners(marker, content, markerDetails[count][0], markerDetails[count][3]);
}

function markerClickListeners(marker, content, parkName, parkID)
{
	google.maps.event.addListener(marker, "click", function() {
			this.parkName = parkName;
			this.parkID   = parkID;
			
			selectPark(this.parkName, this.parkID);
			
			if (infowindow) {
				infowindow.close();
			}
			infowindow = new google.maps.InfoWindow({
									  content: content,
									  maxWidth: 200
								  });
			
			infowindow.open(gmap, marker)
		})
}


function markerHoverListeners(marker)
{
		google.maps.event.addListener(marker, "mouseover", function(e) {
	
          	this.setIcon('/images/global/gmap/markers/green_reverse.png');
        });
		
		google.maps.event.addListener(marker, "mouseout", function(e) {
			
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
	sport = ele.attr('sport').toLowerCase();
	
	var type = false;
	
	if ($('#create-' + sport + '-type').length > 0) {
		// Type choices exists
		$('#create-' + sport + '-type').show();
		type = true;
	}
	
	sportID = ele.attr('sportID');
	
	var src = $('#narrow-column-pic').attr('src').replace(/\w+.png/,sport + '.png');
	$('#narrow-column-pic').attr('src', src);
	
	if (!type) {
		$('#narrow-column-sport-typeName').text('Pickup');
		$('#narrow-column-sport-typeSuffix').text('');
	} else {
		var typeName = $('.create-game-sport-typeName-container').children('.selectable-text.green-bold').text();
		var typeSuffix = $('.create-game-sport-typeSuffix-container').children('.selectable-text.green-bold').text();
		$('#narrow-column-sport-typeName').text(typeName);
		$('#narrow-column-sport-typeSuffix').text(typeSuffix);
	}
	
	$('#narrow-column-sport').text(capitalize(sport));
	
	mapMoved();
	getSportInfo(sportID);
}

/**
 * day was selected, update narrow column
 */
function selectDay(day, month, year)
{
	selectedDay = new Date(year, (month - 1), day);
	
	var dayOfWeek = daysOfWeek[selectedDay.getDay()];
	
	var monthName = selectedDay.getMonth();

}

/**
 * park was selected
 */
function selectPark(parkName, parkID) 
{
	if (typeof parkID != 'undefined') {
		// parkID is set
		$('#parkID').val(parkID);
	} else {
		$('#parkID').val('');
	}
	
	$('#narrow-column-park').text(parkName);
	$('#parkName-main').text(parkName);
	
	$('#parkName-main-container').show();
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
	return true;
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
		
	

		
	
	
	