/* member.js */
var markers = new Array(); 
var gamesPerPage = 4;
var newsfeedTimeout;
var zoomChanged;
var paginationClicked;

$(function() {
	$(document).on('click','.dropdown-menu-option-container', function(e)
	{
		// Option has been clicked
		e.stopPropagation();
		
		var childText = $(this).children('p');
		var textValue = childText.text();
		var childImg  = $(this).children('img');
		
		if ($(this).parents('#dropdown-menu-member-looking-times').length > 0) {
			// Only allow times dropdown to select one option
			$(this).parents('#dropdown-menu-member-looking-times').find('p').removeClass('green-bold');
			$(this).parents('#dropdown-menu-member-looking-times').find('img').removeClass('green-back');
		}
		
		childText.toggleClass('green-bold');
		
		if (childImg.length > 0) {
			childImg.toggleClass('green-back');
		}
		
		loadFind(false);
		
	})
	
	
	/* overly opacity on hover narrow column pic */
	$('.member-pic-container').hover(function()
	{
		$(this).children('#narrow-column-user-picture').stop().animate({opacity: '.5'}, 300);
		$(this).children('#member-narrow-column-pic-overlay-text').stop().animate({opacity: '1'}, 300);
	}, function()
	{
		$(this).children('#narrow-column-user-picture').stop().animate({opacity: '1'}, 300);
		$(this).children('#member-narrow-column-pic-overlay-text').stop().animate({opacity: '0'}, 300);
	})
	
	$('.dropdown-menu-option-container').each(function()
	{
		var childText = $(this).children('p');
		var textValue = childText.text();
		var childImg  = $(this).children('img');
		
		if ($(this).parents('#dropdown-menu-member-looking-times').length > 0) {
			// Times section only has one selected
			if ($(this).index() > 0) {
				// Only bold first one
				return
			}
		}
			

		childText.addClass('green-bold');
		if (childImg.length > 0) {
			childImg.addClass('green-back');
		}
		
	})
	
	// Do not swap value of dropdown selected when choose option
	$(document).unbind('click.swapValue');
	
	$('.member-schedule-day-container').click(function()
	{
		var oldEle 			= $('.member-schedule-day-container.light-back');
		var oldEleTextChild = oldEle.children('.member-schedule-day');
		oldEle.removeClass('light-back');
		oldEleTextChild.text(oldEleTextChild.attr('shortDay'));
		
		var newEle 			= $(this);
		var newEleTextChild = newEle.children('.member-schedule-day');
		newEle.addClass('light-back');
		newEleTextChild.text(newEleTextChild.attr('fullDay'));
		
		var index = $(this).index();
		$('.member-schedule-day-body-container').hide();
		$('.member-schedule-day-body-container:eq(' + index + ')').show();
		
	})
	
	$(document).on('mouseenter','.member-game',function()
	{
		var gameIndex = $(this).attr('gameIndex');
		markers[gameIndex].setIcon('/images/global/gmap/markers/green_reverse.png');
		
		
	})
	$(document).on('mouseleave','.member-game',function() {
		var gameIndex = $(this).attr('gameIndex');
		markers[gameIndex].setIcon('/images/global/gmap/markers/green.png');
	})
	
	
	$(document).on('click', '.member-find-pagination',function()
	{
		var page = $(this).text();
		paginationClicked = true;
		
		animateFindContainer(page);
		
		setTimeout(function() {paginationClicked = false}, 100);
		
		$('.member-find-pagination.light-back').removeClass('light-back');
		$(this).addClass('light-back');

	});
	
	$(document).on('click', '#notifications-load',function()
	{
		getNewsfeed('old');
	})
	
	$('.member-schedule-pagination').click(function()
	{
		var page = $(this).text();
		animateScheduleContainer(page, $(this).parents('.member-schedule-day-body-container'));
		
		$(this).siblings('.member-schedule-pagination.light-back').removeClass('light-back');
		$(this).addClass('light-back');
	})
	
	/* animate background for scheduled game on mouseover */
	$('.member-schedule-day-body-game-container').hover(
		function() {
			$(this).stop().animate({'background-color': '#E6FFE6'}, 300);
		},
		function() {
			$(this).stop().animate({'background-color': '#ffffff'}, 300);
		}
	)	
	
	/* narrow column ratings click icon */
	$('.member-narrow-rating-icon').click(function()
	{
		var index = $(this).index();
		$('.member-narrow-rating-container').hide();
		$('.member-narrow-rating-container:eq(' + index + ')').show();
		
		// Change color of chosen icon
		$('.member-narrow-rating-icon.green-back').removeClass('green-back');
		$(this).addClass('green-back');
	});
	
	if ($('#first-time-alert-container').length > 0) {
		// First visit
		showAlert($('#first-time-alert-container'));
		
		$('.member-first-time-button').click(function() {
			$('.alert-black-back').trigger('click');
		})
	}
	
	if ($('#more-users-alert-container').length > 0) {
		// First visit
		showAlert($('#more-users-alert-container'));
		
	}
	
	preloadImageArray.push('/images/global/gmap/markers/green.png');
	preloadImageArray.push('/images/global/gmap/markers/green_reverse.png');
	
	initializeMap(37.98, -122.5, 12, createMarkers);
	setZoom();

	// Update newsfeed every 2 minutes (if update, must change ajax call in notificationsMapper
	newsfeedTimeout = setInterval(function() { getNewsfeed('new') }, 120000);

})


/**
 * create gmap markers
 */
function createMarkers()
{	

	// Clear prior markers
	clearMarkers();
	
	var marker, i, latLon, index;
	var bounds  = new google.maps.LatLngBounds();
	

	
	//google.maps.event.removeListener(dragListener);
	google.maps.event.addListenerOnce(gmap, 'zoom_changed', function(event) {
		// Set timeout to prevent event from triggering more than once
			clearTimeout(zoomChanged);
			zoomChanged = setTimeout(mapMoved, 50);

	})
	
	google.maps.event.addListenerOnce(gmap, 'dragend', function(event) {
		// Set timeout to prevent event from triggering more than once
			clearTimeout(zoomChanged);
			zoomChanged = setTimeout(mapMoved, 50);
	})
	
	
	if (gmapMarkers.length == 0) {
		return false;
	}
	
	
	
	for (i = 0; i < gmapMarkers.length; i++) {
		// Create markers here
		
		latLon = new google.maps.LatLng(gmapMarkers[i][0], gmapMarkers[i][1]);
		marker = new google.maps.Marker({
						position: latLon,
						map: gmap,
						icon: '/images/global/gmap/markers/green.png',
						shadow: {
									url: 'https://maps.gstatic.com/mapfiles/ms2/micons/msmarker.shadow.png',
									size: new google.maps.Size(59, 32),
									origin: new google.maps.Point(0,0),
									anchor: new google.maps.Point(15, 34)
								},
					
				 })
		markers.push(marker);
		
		google.maps.event.addListener(marker, "mouseover", function() {
          	this.setIcon('/images/global/gmap/markers/green_reverse.png');
			index = $.inArray(this,markers);
			$('.member-game:eq(' + index +')').addClass('light-green-back');
        });
		
		google.maps.event.addListener(marker, "mouseout", function() {
          	this.setIcon('/images/global/gmap/markers/green.png');
			index = $.inArray(this,markers);
			$('.member-game:eq(' + index +')').removeClass('light-green-back');
        });
		
		if (i > ($('.member-find-lower-inner-container').first().children('.member-game').length) - 1) {
			marker.setVisible(false);
		} else {
			/*bounds.extend(latLon);
			gmap.setCenter(bounds.getCenter());
			gmap.fitBounds(bounds);
			*/
		}
		

	}
	
}

/**
 * load matches given new map boundaries
 */
function mapMoved()
{
	if (paginationClicked) {
		// Prevent trigger of gmap event listeners on page change
		return false;
	}
	var points = new Array();
	var bounds = gmap.getBounds();
	points[0] = 'POINT(' + bounds.getNorthEast().lat() + ',' + bounds.getNorthEast().lng() + ')';
	points[1] = 'POINT(' + bounds.getSouthWest().lat() + ',' + bounds.getSouthWest().lng() + ')';
	loadFind(points);
}


function animateScheduleContainer(page, parentEle)
{
	/* Animate left/right (must change member-find-lower-outer-inner-container css)*/
	var width = parentEle.find('.member-schedule-day-body-game-container').outerWidth(true)
	var marginLeft = -1 * (width * (page - 1));

	
	parentEle.find('.member-schedule-day-body-inner-container').stop().animate({marginLeft: marginLeft}, 400);
}
	


/**
 * animate find game, team, tourney container to reflect which page was clicked
 * @params (page => page # that was clicked)
 */
function animateFindContainer(page)
{
	
	/* Animate left/right (must change member-find-lower-outer-inner-container css)
	var marginLeft = -1 * ($('.member-find-lower-inner-container').width() * (page - 1));
	
	$('.member-find-lower-outer-inner-container').animate({marginLeft: marginLeft}, 200);
	*/
	var marginTop = -1 * ($('.member-find-lower-inner-container').height() * (page - 1));
	
	if (marginTop == parseInt($('.member-find-lower-outer-inner-container').css('margin-top'),10)) {
		// Same page as is currently selected, return
		return;
	}
	
	$('.member-find-lower-outer-inner-container').stop().animate({marginTop: marginTop}, 400);
	
	hideShowMarkers(page);
}

/**
 * hide/show markers based on page being shown
 * @params (page => page # that was clicked)
 */
function hideShowMarkers(page)
{
	page -= 1;
	var start = 0; // Where to start marker count from
	for (i = 0; i < page; i++) {
		// Add number of games in each page from previous pages
		start += $('.member-find-lower-inner-container:eq(' + i + ')').children('.member-game').length;
	}

	var end   = start + $('.member-find-lower-inner-container:eq(' + page + ')').children('.member-game').length;
	var latLon;
	var bounds  = new google.maps.LatLngBounds();
	gmap.initialZoom = true; // Set initial zoom to force zoom constraint

	for (i = 0; i < markers.length; i++) {
		if (i >= start && i < end) {
			// Marker is on current page
			markers[i].setVisible(true);
			latLon = markers[i].getPosition();
			bounds.extend(latLon);
			if ((i == (end - 1)) || (i == (markers.length - 1))) {
				// Last marker of page
				gmap.fitBounds(bounds);
				gmap.panToBounds(bounds);
				
			}
		} else {
			// Marker is on other pages			
			markers[i].setVisible(false);
		}
		
	}
	
}


/**
 * Ajax call to retrieve games/teams onchange of "looking for"
 */
function loadFind(points)
{

	var sports = new Array();
	var types  = new Array();
	var time   = '';
	
	$('#dropdown-menu-member-looking-sports,#dropdown-menu-member-looking-types,#dropdown-menu-member-looking-times').children('.dropdown-menu-option-container').each(function()
	{
		if ($(this).children('p').is('.green-bold')) {
			// Is selected
			if ($(this).parent().is('#dropdown-menu-member-looking-sports')) {
				// Sports dropdown
				sports.push($(this).children('p').text())
			} else if ($(this).parent().is('#dropdown-menu-member-looking-types')) {
				// Types dropdown
				types.push($(this).children('p').text().toLowerCase())
			} else {
				// Times dropdown
				time = $(this).children('p').text().toLowerCase();
			}
				
		}
	})


	$('.member-find-loading').show();
	$('#member-find-body').hide();

	$.ajax({
		url: '/ajax/get-matches',
		type: 'POST',
		data: {sports: sports, 
			   types: types,
			   points: points,
			   time: time},
		success: function(matches) {
			matches = JSON.parse(matches);
			$('#member-find-body').html(matches[0]);
			$('#member-find-body').show();
			
			$('.member-find-loading').hide();
			
			// Clear any prior markers
			gmapMarkers = new Array();
			if (typeof matches[1] != 'undefined') {
				for (i = 0; i < matches[1].length; i++) {
					gmapMarkers.push([matches[1][i][0],matches[1][i][1]]);
				}
			}

			createMarkers();
			
			//populateFindBody(matches);
		}
	})
}


/**
 * Ajax call to get new newsfeed notifications
 * @params (oldOrNew => retrieve old or new notifications (str 'old' or 'new'))
 */
function getNewsfeed(oldOrNew)
{
	var numNewsfeeds;
	if (oldOrNew == 'old') {
		// Old notifications, find where to count from
		numNewsfeeds = $('.newsfeed-notification-container').length
	}
	$.ajax({
		url: '/ajax/get-new-newsfeed',
		type: 'POST',
		data: {oldOrNew: oldOrNew,
			   numNewsfeeds: numNewsfeeds},
		success: function(data) {
			data = JSON.parse(data);
			if (data.length < 1) {
				// No results left, end of the road, hide load button
				$('#notifications-load').hide();
				$('#notifications-none').show();
			}
			
			if (oldOrNew == 'new') {
				// Prepend data
				populateNewsfeed(data, 'prepend');
			} else {
				// Append data (old)
				populateNewsfeed(data, 'append');
			}
		}
	})
}

/**
 * populate newsfeed with ajax data for new/old notifications
 * @params (data => returned array of html for notifications,
 *			position => 'append' or 'prepend')
 */
function populateNewsfeed(data, position)
{
	var output = '';
	
	for (i = 0; i < data.length; i++) {
		output += data[i];
	}
	
	if (position == 'prepend') {
		// Prepend data to newsfeed
		$('.notifications-container').prepend(output);
	} else {
		// Append data
		$('.notifications-container').append(output);
	}
}

/**
 * populate the lower body of member find section to reflect changes user made to selections
 * @params (matches => json decoded results from loadFind of all matches based on user preferences)

function populateFindBody(matches) 
{
	// Clear html
	$('.member-find-lower-outer-inner-container').html('');
	
	var output = '';
	var counter    = 0;
	var totalMatches = 1;
	var totalPages = 1;
	var totalGames = 0;
	var matchesPerPage = 4;
	var numberOfPages  = 3;	
	var type,newDate,day,hour,dateDesc,id,location,gameIndex;
	
	for (i = 0; i < matches.length; i++) {
		// Loop through matches and create appropriate html
			if (totalMatches > (matchesPerPage * numberOfPages)) {
				// Met limit of number of pages
				break;
			}
			if (counter == 0) {
				// Counter was reset/first round, create inner container
				output += "<div class='member-find-lower-inner-container'>";
			} 
			if (counter == matchesPerPage) {
				// Number of games/teams per "page" is met, start new
				output += "</div><div class='member-find-lower-inner-container'>";
				counter = 0;
				totalPages++;
			}
			
			if (matches[i].gameID.length > 0) {
				// Match is a game
				type     = 'Game';
				dateTime = new Date(matches[i]['date']);
				newDate  = dateTime->format('m n');
				day      = match->getDay();
				hour	 = match->getHour();
				dateDesc = date('M j', strtotime(match->date));
				id		 = match->gameID;
				location = match->getLimitedParkName(25);
				gameIndex= totalGames;
				totalGames++;
			} elseif (get_class(match) == 'Application_Model_Team') {
				// Match is a team
				type	  = 'Team';
				day       = '';
				hour	  = '';
				location  = match->getLimitedName('teamName',25);
				id		  = match->teamID;
				dateDesc  = '';
				gameIndex = '';
			}
				
			output += "<a class='member-find-game-container member-" + strtolower(type) + "' href='/" + strtolower(type) + "s/" + id + "' gameIndex='" + gameIndex + "'>";
			output += "<p class='member-find-game-number green-back white arial bold'>" + totalMatches + "</p>";
			output += "<p class='member-find-game-sport darkest bold'>" + match->sport + "</p>";
			output += "<p class='member-find-game-type darkest bold'>" + type + "</p>";
			output += "<div class='member-find-game-date medium' tooltip='" + dateDesc + "'>
								<div class='member-find-game-date-day'>" + day + "</div>&nbsp; 
								<div class='member-find-game-date-hour'>" + hour + "</div>
							</div>";
			output += "<p class='member-find-game-players darkest bold'>" + match->totalPlayers + "/" + match->rosterLimit + "</p>";
			output += "<img src='" + match->getMatchImage() + "' class='member-find-game-match' tooltip='" + match->getMatchDescription() + "'/>";
			output += "<p class='member-find-game-park medium'>" + location + "</p>";
			output += "<img src='/images/global/body/double_arrows.png' class='member-find-game-arrow'/>";
			
			output += "</a>";
						
			counter++;
			totalMatches++;
			
			
		}
		
		// End game section
		output += "</div></div></div>";

	}
*/

