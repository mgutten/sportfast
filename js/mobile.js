// JavaScript Document
var mapTapped = false; 
$(function() 
{
	if (!isMobile()) {
		// Is not mobile, prevent document.onready calls
		return false;
	}
	$('.notification,.dropdown').click(function()
	{

		$(this).toggleClass('selected');

		if ($(this).is('.selected')) {
			
			$($(this).attr('data-target')).show();
			
			if ($(this).is('.notification')) {
				resetNotifications();
				
				$('.nav-notification-indicator').html('0');
				$('.nav-notification-indicator-container').hide();
			}
			
			if ($(this).is('.notification') &&
				$('.dropdown').is('.selected')) {
					// Clicked on notification and other dropdown is down, hide it
					$('.dropdown').trigger('click');
				}
			if ($(this).is('.dropdown') &&
				$('.notification').is('.selected')) {
					// Clicked on dropdown and other dropdown is down, hide it
					$('.notification').trigger('click');
				}
		} else {
			$($(this).attr('data-target')).hide();
			
		}
		
	})
	
	$(document).on('tap',function(e) {
		var ele = false;
		if ($('#notifications-container').css('display') != 'none') {
			ele = $('.notification');
		} else if ($('#dropdown-container').css('display') != 'none') {
			ele = $('.dropdown');
		}
		

		// Either of navigation are showing, do not register click on rest of page
		if ($(e.target).parents('#body-white-back').length > 0 ||
			$(e.target).is('#body-white-back')) {
				
				if (ele) {
					ele.trigger('click');
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
					
			}

		if (!$(e.target).is('#gmap') && $(e.target).parents('#gmap').length > 0) {
			// Target has tapped on anything except gmap, set mapTapped var to prevent accidental panning of map
			mapTapped = false;
		}
	})

	if (gmap) {
		// Turn off panning onload
		togglePanning();
    }

	$('#gmap').on('tap', function() {
		// If tap #gmap container, allow panning
		mapTapped = true;
		togglePanning();
	});


	$(document).on('scrollstart', function(e) {
		if ($(e.target).is('#gmap') && scrolling) {
			return false;
		}
	})
	
})

/* toggle ability to pan gmap */
function togglePanning() {

	var options = {
		draggable: mapTapped,
		panControl: mapTapped,
		scrollwheel: mapTapped
	}

	gmap.setOptions(options);

}

function deactivateMap(e) {
	if (!$(e.target).is('#gmap')) {
		// Has scrolled not on gmap, deactive map scrolling temporarily
		scrolling = true;
		holdZoomTimeout = setTimeout(function() {
			scrolling = false;
		}, 1000);
	}
}