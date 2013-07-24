// find.js
var markers = new Array(); 
var newsfeedTimeout;
var zoomChanged;
var page = 1;
var paginationClicked;

/* BUGS:
 * Ajax loaded containers (beyond initial 30 that are loaded) do not work with map, need to handle adding markers for them
 */

$(function()
{
	/* if Tennis is selected, show type section */
	$('#narrow-column-body-sports').children('.selectable-text.narrow-sport').click(function()
	{
		if ($(this).text().toLowerCase() == 'tennis' && $(this).is('.green-bold')) {
			$('#narrow-column-type').show();
		} else if ($(this).text().toLowerCase() == 'tennis' && !$(this).is('.green-bold')){
			$('#narrow-column-type').hide();
		}
	})
	
	$('.pagination-prev').hide();
	
	/* only allow one checkbox per dropdown */
	$('.narrow-column-body').find('input[type=checkbox]').change(function()
	{

		if (!$(this).prop('checked')) {
			// Already checked, return
			$(this).prop('checked', true)
			return false;
		}
		
		if ($(this).is('.find-filter-textInput')) {
			// show hidden
			$(this).parents('.checkbox-container').siblings('.find-filter-hidden').show();
		} else {
			$(this).parents('.checkbox-container').siblings('.find-filter-hidden').hide();
		}
		
		if (isParks() && $(this).is('#courtSpecific')) {
			// show hidden court specific box
			$('#court-specific').show();
		} else if (isParks() && $(this).is('#courtAny')) {
			$('#court-specific').hide();
		}
		
		$(this).parents('.narrow-column-body').find('input[type=checkbox]').each(function()
		{
			$(this).prop('checked', false);
		})
		
		$(this).prop('checked', true);
		
		buildOptions();
	})
	
	/* fire ajax query on keyup */
	$('.narrow-column-body').find('input[type=text]').keyup(function()
	{
		buildOptions()
	});
	
	
	/* change pages on up and down arrows */
	$(document).keydown(function(e)
	{
		if (e.which == 38 || e.which == 40) {
			e.preventDefault();
		}
		switch (e.which) {
			case 37:
				$('.find-pagination-prev').trigger('click')
				 //left arrow key
				break;
			case 38:
				$('.find-pagination-prev').trigger('click') //up arrow key
				break;
			case 39:
				$('.find-pagination-next').trigger('click') //right arrow key
				break;
			case 40:
				$('.find-pagination-next').trigger('click') //bottom arrow key
				break;
		}
	})
	
	/* change pages*/
	$('.find-pagination-next').click(function()
	{
		var totalPages = $('.find-results-inner-container').length;
		if (page == totalPages) {
			return false;
		}
		
		page += 1;
		
		if (((page + 1) % 5 == 0) &&
			(parseInt($('#find-num-results').text(),10) > $('.find-result-container').length)) {
			// Next page is factor of five (ie every 30 results if 6 results per page), append more results
			var offset = (page + 1) * 6;
			buildOptions(offset);
		}
		
		testFirstLastPagination(page);
		animatePage(page);
	})

	$('.find-pagination-prev').click(function()
	{
		if (page == 1) {
			return false;
		}

		page -= 1;
		
		testFirstLastPagination(page);
		animatePage(page);
	})
	
	/* go to last page */
	$('.find-pagination-last').click(function()
	{
		
		page = $('.find-results-inner-container').length;
		
		testFirstLastPagination(page);
		animatePage(page);
	})
	
	/* go to first page */
	$('.find-pagination-first').click(function()
	{
		page = 1;
		
		testFirstLastPagination(page);
		animatePage(page);
	})
	
	
	$('.selectable-text').click(function()
	{
		if (!$(this).is('.selectable-text-one') && $(this).siblings('.green-bold').length < 1 
			&& $(this).parents('#find-type-tennis').length < 0) {
			// Make sure one is always selected
			$(this).addClass('green-bold');
			if ($(this).text().toLowerCase() == 'tennis') {
				$('#narrow-column-type').show();
			}
			return false;
		}
		
		buildOptions();
	})
	
	/* dropdown change looking for */
	$('#dropdown-menu-looking-for').find('.dropdown-menu-option-container').click(function()
	{
		var val = $(this).children('p').text().toLowerCase();

		window.location = '/find/' + val;
	})
	
	/*
	$(document).on('mouseenter','.find-result-container',function()
	{
		$(this).find('.find-join').show();
	})
	.on('mouseleave','.find-result-container',function()
	{
		$(this).find('.find-join').hide();
	})
	*/
	
	$('.find-search-result').mouseenter(function()
	{
		// Change narrow column pic
		var src = $(this).find('img.find-img').attr('src');
		var largeSrc = src.replace('/medium/','/large/');
		
		$('.narrow-column-picture').attr('src',largeSrc)
								   .fadeIn();
	})
	
	/* map */
	$(document).on('mouseenter','.find-result-games,.find-result-parks',function()
	{
		if (isGames()) {
			var index = $(this).attr('gameIndex');
		} else {
			var index = $(this).attr('parkIndex');
		}

		markers[index].setIcon('/images/global/gmap/markers/green_reverse.png');
		markers[index].setZIndex(1200);
		
	})
	$(document).on('mouseleave','.find-result-games,.find-result-parks',function() {
		if (isGames()) {
			var index = $(this).attr('gameIndex');
		} else {
			var index = $(this).attr('parkIndex');
		}
		markers[index].setIcon('/images/global/gmap/markers/green.png');
		markers[index].setZIndex(1);
	})	
	
	
	$(document).on('mouseenter','.find-result-teams,.find-result-users',function()
	{
		var src = $(this).find('.find-result-img-container').children('img').attr('src').replace(/\/medium/,'/large');
		$('.narrow-column-picture').attr('src', src);
		
		$(this).find('.hover').toggle();
	})
	$(document).on('mouseleave','.find-result-teams,.find-result-users',function()
	{		
		$(this).find('.hover').toggle();
	})
	
	preloadImageArray.push('/images/global/gmap/markers/green.png');
	preloadImageArray.push('/images/global/gmap/markers/green_reverse.png');
	

	if (isGames() ||
		isParks()) {
		// Map for games page
		initializeMap(37.98, -122.5, 10, createMarkers);
	}
	
	testFirstLastPagination(page);

});

/**
 * ajax call to find matches based on options
 * @params (options => object with options stored,
 *			type => 'games' or 'teams' or 'tournaments',
 *			offset => limit offset of mysql
 */
function findMatches(options, type, orderBy, offset)
{

	offset = (typeof offset == 'undefined' ? '' : offset);

	$('#loading').show();
	
	if (!offset) {
		$('#find-results-inner-container').html('');
	}
	
	$.ajax({
		url: '/ajax/find-matches',
		type: 'POST',
		data: {options: options,
			   type: type,
			   orderBy: orderBy,
			   offset: offset},
		success: function(data) {
			
			data = JSON.parse(data);

			if (offset.length < 1) {
				// No offset, replace all data
				$('#find-results-inner-container').html(data[0]);
				page = 1;
			} else {
				$('#find-results-inner-container').append(data[0]);
			}
			
			
			$('#find-results-inner-container').css('margin-top',0);
			
			$('#loading').hide();			
			$('#find-num-results').html(data[2]);
			
			testFirstLastPagination(page)
			
			paginationClicked = false;
			
			gmapMarkers = new Array();
			if (typeof data[1] != 'undefined') {
				for (i = 0; i < data[1].length; i++) {
					gmapMarkers.push([data[1][i][0],data[1][i][1]]);
				}
				
				createMarkers();
			}
			
			
			
		}
	})
}

/**
 * initialize googleMap w/ lon, lat, and zoom
 * @params (lon  => longitude,
 *			lat  => latitude,
 *			zoom => zoom (higher is more zoomed))
 *			callback => callback function
 */
function initializeMap(lat, lon, zoom, callback) 
{
	
        var mapOptions = {
          //center: new google.maps.LatLng(lat, lon),
          zoom: zoom,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
		  disableDefaultUI: true,
		  mapTypeControl: false // Disable ability to change to satellite etc
		  
        };
        gmap = new google.maps.Map(document.getElementById("gmap"),
            mapOptions);
			
			
		if (userLocation.length > 0) {
			// User location is set, center on it initially
			lat = userLocation[0];
			lon = userLocation[1];
		}
		
		var latLon = new google.maps.LatLng(lat, lon);
		gmap.setCenter(latLon);
		
		callback();
		
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

	// Zoom constraint
	/*
	google.maps.event.addListener(gmap, 'zoom_changed', function() {
			zoomChangeBoundsListener = 
				google.maps.event.addListener(gmap, 'bounds_changed', function(event) {
					if (this.getZoom() > 12 && this.initialZoom == true) {
						// Change max/min zoom here
						this.setZoom(12);
						this.initialZoom = false;
					}
				google.maps.event.removeListener(zoomChangeBoundsListener);
			});
	});
	
	
	gmap.initialZoom = true;
	*/

	
	//google.maps.event.removeListener(dragListener);
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


		addMarkerListeners(marker);
		
		var findGames = $('.find-results-inner-container').first().children('.find-result-games').length;
		var findParks = $('.find-results-inner-container').first().children('.find-result-parks').length;
		
		if (isGames() && (i > (findGames) - 1)) {
			marker.setVisible(false);
		} else if (isParks() && (i > (findParks) - 1)) {
			marker.setVisible(false);
		}
			/*bounds.extend(latLon);
			gmap.setCenter(bounds.getCenter());
			gmap.fitBounds(bounds);
			*/
			

	}
	
}

function addMarkerListeners(marker)
{
		google.maps.event.addListener(marker, "mouseover", function(e) {
			var lat = roundLatLng(this.getPosition().lat());
			var lon = roundLatLng(this.getPosition().lng());
	
          	this.setIcon('/images/global/gmap/markers/green_reverse.png');
			var index = new Array();

			for (i = 0; i < markers.length; i++) {
				
				var markerLat = roundLatLng(markers[i].getPosition().lat());
				var markerLon = roundLatLng(markers[i].getPosition().lng());
				var visible   = markers[i].getVisible();
				
				if((markerLat == lat && markerLon == lon) && visible) {
					   // Marker is at same location and visible
					   index.push(i);
				   }
			}
			
			if (isGames()) {
				var classy = 'find-result-games';
			} else {
				// Is parks
				var classy = 'find-result-parks';
			}
			$.each(index,function(key, value) {
				
				$('.' + classy + ':eq(' + value +')').css('background','');
				$('.' + classy + ':eq(' + value +')').addClass('light-back');
			})
        });
		
		google.maps.event.addListener(marker, "mouseout", function(e) {
			var lat = roundLatLng(this.getPosition().lat());
			var lon = roundLatLng(this.getPosition().lng());
			
          	this.setIcon('/images/global/gmap/markers/green.png');
			var index = new Array();
			
			for (i = 0; i < markers.length; i++) {

				var markerLat = roundLatLng(markers[i].getPosition().lat());
				var markerLon = roundLatLng(markers[i].getPosition().lng());
				var visible   = markers[i].getVisible();
				
				if((markerLat == lat && markerLon == lon) && visible) {
					   // Marker is at same location and visible
					   
					   index.push(i);
				   }
			}
		
			if (isGames()) {
				var classy = 'find-result-games';
			} else {
				// Is parks
				var classy = 'find-result-parks';
			}
			$.each(index,function(key, value) {
				$('.' + classy + ':eq(' + value +')').removeClass('light-back');
			})
        });
}

function isGames()
{
	if ($('#looking-for').find('.dropdown-menu-selected').children('p').text().toLowerCase() == 'games') {
		return true;
	} else {
		return false;
	}
}

function isParks()
{
	if ($('#looking-for').find('.dropdown-menu-selected').children('p').text().toLowerCase() == 'parks') {
		return true;
	} else {
		return false;
	}
}

function roundLatLng(val) 
{
	return Math.round(1000 * val)/1000;
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
	
	buildOptions();
}

/**
 * animate pagination page
 * @params(page => page that was clicked (int))
 */
function animatePage(page)
{
	paginationClicked = true;
	
	var page = page - 1;
	var firstEle  = $('.find-results-inner-container').first();
	var marginTop = (-1) * page * firstEle.height();
	
	$('.find-results-outer-inner-container').stop().animate({marginTop: marginTop}, 500);
	hideShowMarkers(page);
}

/**
 * hide/show markers based on page being shown
 * @params (page => page # that was clicked)
 */
function hideShowMarkers(page)
{
	var numChildren = $('.find-results-inner-container').first().children('.find-result-container').length;
	var start = numChildren * page;
	var end   = start + numChildren;
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
				// Last marker of page, move map to fit markers
				//gmap.fitBounds(bounds);
				//gmap.panToBounds(bounds);
				
			}
		} else {
			// Marker is on other pages			
			markers[i].setVisible(false);
		}
		
	}

	setTimeout(function() {
			paginationClicked = false;
	}, 400);
	
	
}


/**
 * test whether to show first or last indicators
 */
function testFirstLastPagination(page)
{

		var totalPages = $('.find-results-inner-container').length;
		
		if (page != 1) {
			$('.pagination-prev').show();
		} else {
			$('.pagination-prev').hide();
		}
		
		if (page != totalPages) {
			$('.pagination-next').show();
		} else {
			$('.pagination-next').hide();
		}
		
}

/**
 * more pages than are shown, increment page numbers when change page
 */
function incrementPages(difference)
{
	$('.pagination-page').each(function()
	{
		var curVal = parseInt($(this).text(), 10);
		var newVal = curVal + difference;
		
		$(this).text(newVal);
	})
}

/**
 * build options array for findMatches function
 * @params (offset => if set (int), append results to current divs starting at offset)
 */
function buildOptions(offset)
{

	offset = (typeof offset == 'undefined' ? '' : offset);

	
	if (gmap) {
		var points = new Array();
		var bounds = gmap.getBounds();
		points[0] = 'POINT(' + bounds.getNorthEast().lat() + ',' + bounds.getNorthEast().lng() + ')';
		points[1] = 'POINT(' + bounds.getSouthWest().lat() + ',' + bounds.getSouthWest().lng() + ')';
	}
	
	if (isParks()) {
		// Is parks page
		var courts = getCheckedValue($('#narrow-column-body-courts-and-fields'));
		
		if (courts) {
			// Specific courts are chosen
			courts = loopSelectable($('#court-specific'));
		}
		
		var stash  = getCheckedValue($('#narrow-column-body-stash'));
		var type   = getCheckedValue($('#narrow-column-body-type'));
		
		var options = {courts: courts,
					   stash: stash,
					   type: type,
					   points: points};
	} else {
		var sports = loopSelectable($('#narrow-column-body-sports'))
		sports  = buildTypesOption(sports);
		
		var skill = getSkillOption();
		var age   = getAgeOption();
		var time;
		var looking = getLookingOption();
		
		
		if ($('#timeUser').prop('checked')) {
			time = 'user';
		} else if ($('#timeAny').prop('checked')) {
			time = 'any';
		}
		
		var options = {sports:sports,
					   skill:skill,
					   age:age,
					   time:time,
					   points: points,
					   looking: looking};
	}
	
			   
	var type  = $.trim($('#looking-for').find('.dropdown-menu-selected').children('p').text().toLowerCase());

	var orderBy = $('#find-order-by').children('.selectable-text.green-bold').text().toLowerCase();
	
	findMatches(options, type, orderBy, offset);
	
}

/**
 * loop through selectable text and determine what is selected
 * @params (narrowColumnBody => $('.narrow-column-body') element)
 */
function loopSelectable(narrowColumnBody)
{
	var selected = new Array();
	narrowColumnBody.children('.selectable-text').each(function()
	{
		if ($(this).is('.green-bold')) {
			// Is selected 
			selected.push($(this).text().toLowerCase());
		}
	})

	return selected;
}
	
/**
 * loop through types and build types option
 */
function buildTypesOption(sports)
{
	var returnArray = new Object();

	$.each(sports, function(index, value)
	{
		if ($('#find-type-' + value).length > 0) {
			// Sport types exist
			returnArray[value] = new Object();
			
			$('#find-type-' + value).find('.find-type-container').each(function()
			{
				
				var typeName = new Object();
				var name = $(this).children().first().text().toLowerCase();
				var success = false;
				
				$(this).children('.selectable-text').each(function()
				{
					if ($(this).is('.green-bold')) {
						// Is selected
						success = true;
						typeName[$(this).text().toLowerCase()] = true;
					}
				})

				
				if (success) {
					returnArray[value][name] = new Object();
					returnArray[value][name] = typeName;
				}
			
			})
		} else {
			// Does not exist
			returnArray[value] = '';
		}

	})
	
	return returnArray;
						
}

/**
 * get value of checked box within container ele
 */
function getCheckedValue(containerEle)
{
	var checked = containerEle.find('input[type=checkbox]:checked');
	var returnValue = checked.attr('text').toLowerCase().replace(/ /g,'_');
	
	if (returnValue == 'any') {
		// Do not let sql call include this parameter in search, set to blank
		return '';
	}
	
	return returnValue;
}



function getSkillOption()
{
	var selectedEle = $('#narrow-column-body-skill').find('input[type=checkbox]:checked');
	var returnValue;
	
	if (selectedEle.is('#skillAny')) {
		returnValue = '';
	} else if(selectedEle.is('#skillSpecific')) {
		var parent = selectedEle.parents('.checkbox-container').siblings('.find-filter-hidden');
		var min = parent.find('#skillMin');
		var max = parent.find('#skillMax');
		
		returnValue = testInputs(min, max, 63, 100);
	}
	
	return returnValue;
}

function getLookingOption()
{
	var selectedEle = $('#narrow-column-body-looking-for').find('input[type=checkbox]:checked');
	var returnValue;
	
	if (selectedEle.is('#lookingAny')) {
		returnValue = '';
	} else if(selectedEle.is('#lookingForTeam')) {
		returnValue = 'team';
	}
	
	return returnValue;
}

function getAgeOption()
{
	var selectedEle = $('#narrow-column-body-age').find('input[type=checkbox]:checked');
	var returnValue;
	
	if (selectedEle.is('#ageAny')) {
		returnValue = '';
	} else if(selectedEle.is('#ageSpecific')) {
		var parent = selectedEle.parents('.checkbox-container').siblings('.find-filter-hidden');
		var min = parent.find('#ageMin');
		var max = parent.find('#ageMax');
		
		returnValue = testInputs(min, max, 17, 90);
	}
	
	return returnValue;
}

function testInputs(min, max, minVal, maxVal)
{
	min.limitVal(minVal,maxVal);
	max.limitVal(minVal,maxVal);
	
	var returnArray;
	
	if (min.val().length < 1 || max.val().length < 1) {
		// One is empty
		return {lower: minVal,
				upper: maxVal};
	} 
	
	if ((parseInt(min.val(),10) > parseInt(max.val(),10)) &&
		(max.val().length >= min.val().length) &&
		(max.val() != 10)) {
		// Min is bigger than max, make smaller
		min.val(parseInt(max.val(),10) - 1);
	}
	

	if (parseInt(min.val(), 10) >= minVal && parseInt(max.val(), 10) <= maxVal) {
		// Success!
		returnArray = {lower: min.val(),
					   upper: max.val()};
	} else {
		returnArray = {lower:minVal,
					   upper:maxVal};
	}
	return returnArray;
}
		