// JavaScript Document
var markers = new Array(); 
var gmap;

$(function()
{
	
	/* usermap stuff */
	if (gmapMarkers.length > 0) {
		initializeMap(37.98, -122.5, 12, false, createMarkers);
		setZoom();
	}
	
	
	$('.admin-flagged-confirm,.admin-flagged-remove').click(function()
	{
		
		var userRatingID = $(this).attr('userRatingID');
		var remove = ($(this).is('.admin-flagged-confirm') ? 0 : 1);
		
		updateFlaggedRating(userRatingID, remove);
	})
	
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
						icon: '/images/global/gmap/markers/green_square.jpg'
					
				 })
		markers.push(marker);
		
		/*
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
		
		google.maps.event.addListener(marker, "click", function() {
			index = $.inArray(this,markers);
			window.location = $('.member-game:eq(' + index +')').attr('href');
        });
		*/

		
		bounds.extend(latLon);
		//gmap.setCenter(bounds.getCenter());
		//gmap.fitBounds(bounds);
		

	}
	
}

/**
 * either remove or set to true a flagged user rating
 */
function updateFlaggedRating(userRatingID, remove)
{
	var options = {userRatingID: userRatingID,
				   remove: remove};
				   
	$.ajax({
		url: '/ajax/update-flagged-rating',
		type: 'POST',
		data: {options: options},
		success: function()
		{
			reloadPage();
		}
	})
}
