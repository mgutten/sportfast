// global.js
var mouseoverDropdown = false; /* fix issue when input is focus, then not focus and mouse is not over */
var dropdownClickDown = false;
var fadeRunning 	  = false;
var tooltipTimer;
var tooltipEle 		  = null;
var sliderSkillValues = [];
sliderSkillValues[0]  = {level:'Beginner',
						description: 'I have rarely (if ever) played.'};
sliderSkillValues[1]  = {level:'Decent',
						description: 'I play infrequently, or have difficulty keeping up when I do play.'};
sliderSkillValues[2]  = {level:'Good',
						description: 'I am an average player.  Nothing fancy, just good fundamentals.'};
sliderSkillValues[3]  = {level:'Skilled',
						description: 'I am skilled.  I am better than the average player.'};
sliderSkillValues[4]  = {level:'Talented',
						description: 'I am very skilled.  I am typically the best player in the game.'};
sliderSkillValues[5]  = {level:'Unstoppable',
						description: 'I played (or should play) on a professional level.'};
						
var mouseoverColor;
var dropdowns = new Array();
var notificationDropdown = false;
var preloadImageArray    = new Array('/images/global/header/notification_shield_reverse.png');
var gmapMarkers;
var userLocation = new Array();


$(function()
{
	/* jquery plugin to limit value of input */
	(function($) {
	  $.fn.limitVal = function(lower, upper) {
		
			if(this.val().length == 0) {
				// Empty string
				return;
			}
			
			if (this.val() > upper) {
				this.val(upper);
			} else if(this.val() < lower) {
				this.val(lower)
			}
			return;
	
	  };


	  /* jquery plugin to verify based on parameters passed in */	  
	  $.fn.isValid = function(options) {
		  // Create some defaults, extending them with any options that were provided
			var settings = $.extend( {
			  'maxLength'     : 500,
			  'minLength'	  : 0,
			  'regex'		  : /.*/g,
			  'number'		  : false
			}, options);
			
			var value 		     = this.val();
			var regexPatterns    = new Array();	

			if (typeof settings.regex == 'string') {
				settings.regex = regexPatterns[settings.regex];
			}
			
			if (value.length > settings.maxLength) {
				// Value is longer than max allowed length
				return false;
			}

			if (value.length < settings.minLength) {
				// Value is shorter than min allowed length
	  			return false;
			}
			
			if (settings.number) {
				// Test if number
				if (!isNumber(value)) {
					return false;
				}
			}
			
			if (!settings.regex.test(value)) {
				// Did not pass regex test
				return false;
			}
			
			return true;
			
	
	  }
	  
	  
	  /* animate element background darker */
	  $.fn.animateDarker = function() {
		 
		 	if(!this.attr('color')) {
				// Color attribute is not saved, save it
				this.attr('color', this.css('background-color'))
			}
			
			mouseoverColor  = this.attr('color');
			var darkerColor = getDarkerColor(mouseoverColor);
			var ele         = this;
			
			this.stop().animate({backgroundColor: darkerColor},200);
			
	  }
	  
	  /* animate element background darker */
	  $.fn.animateLighter = function() {
		 	this.stop().animate({backgroundColor: this.attr('color')},200);		 
	  }
	  
	  /* unset height and width restrictions, let image show naturally */
	  $.fn.maintainRatio = function() {
		  	var containerHeight = this.height();
			var containerWidth  = this.width();
			
			this.css({height: 'auto',
					  width: 'auto'});
			
			var imgHeight = this.height();
			var imgWidth  = this.width();
			
			return this;
		 		 
	  }
			  
	})( jQuery );
	
	
	$(document).on('click','.dropdown-menu-selected',function(e)
	{
		e.stopPropagation();
		if (dropdowns.dropdownMenuDown) {
			if (dropdowns.dropdownMenuDown.attr('id') !== $(this).parent().attr('id')) {
				// Different dropdown is already down
				dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').children('p').removeClass('dropdown-menu-container-reverse-text');
				dropdownMenu(dropdowns.dropdownMenuDown);
			}
		}
		dropdownMenu($(this).parent('.dropdown-menu-container'));
		
		
		if ($(this).is('.dropdown-menu-container-reverse')) {
			$(this).removeClass('dropdown-menu-container-reverse');
			$(this).children('p').removeClass('dropdown-menu-container-reverse-text');
		} else {
			// Remove old class for case when click one dropdown then click another
			$('.dropdown-menu-container-reverse').removeClass('dropdown-menu-container-reverse');
			$(this).addClass('dropdown-menu-container-reverse');
			$(this).children('p').addClass('dropdown-menu-container-reverse-text');
		}
		
	})
	
	$(document).on('mouseenter','.dropdown-menu-option-container',function()
	{
		$(this).animateDarker();
	})
	.on('mouseleave','.dropdown-menu-option-container',function()
	{
		$(this).animateLighter();
	})
	.on('click.swapValue','.dropdown-menu-option-container',function()
	{
		//Option has been clicked
		var value = $(this).children('p').text();
		$(this).parents('.dropdown-menu-container').children('.dropdown-menu-selected').children('p').text(value);
	})
	
	
	/* animate hover effect for navigation */
	$('.nav-back').hover(function() 
	{
		
		if ($(this).is('.nav-dropdown') || $(this).is('#nav-back-signup')){
			return;
		}
			
		
		if ($(this).is('#nav-back-signup')) {
			backgroundColor = '#58bf12';
		}
		
		var backgroundColor = '#555';
		$(this).stop().animate({'background-color': backgroundColor},200);
	},
	function()
	{
		if ($(this).is('.nav-dropdown') || $(this).is('#nav-back-signup')){
			return;
		}
		$(this).stop().animate({'background-color':'transparent'},200);
	});
	
	/* dropdown for change city in header */
	$('#header-city').click(function(e)
	{
		e.stopPropagation();
		var dropdown = $('#city-change-container');
		alignDropdown(dropdown, $(this), 'left');
		dropdown.toggle();
	});
	
	/* change city for user when click city on dropdown */
	$('#city-change-results-container').on('click','.city-change-result',function() {
		setUserLocation($(this).attr('cityID'));
	});
	
	$('#changeCity,#changeZipcode').keyup(function()
	{
		if ($(this).val() == '') {
			// Blank box, do not run check
			return;
		}
		getCity($(this).val(), populateCityResults);
	});
	
	
	/* Search bar ajax call */
	$('#headerSearchBar').keyup(function()
	{
		
		if ($(this).val().length < 3) {
			// Blank box (or too short), do not run check
			$('#header-search-results-container').hide();
			return;
		}
		searchDatabase($(this).val(), populateSearchResults);
		
	})
	.focus(function()
	{
		$(this).trigger('keyup');
	});
	
	$('#city-change-reset').click(function()
	{
		setUserLocation('home');
	});
	
	
	$(window).resize(function()
	{
		$('.dropdown-back-outer').each(function()
		{
			alignDropdownContainer($(this));
		})
		
	})
	
	
	/* change background color of narrow-column onhover */
	$('.narrow-column-header').mouseenter(function()
	{
		$(this).animateDarker();
	}).mouseleave(function()
	{
		$(this).animateLighter();
	});
		
	
	/* animate narrow-column sections onclick */
	$('.narrow-column-header').click(function()
	{
		
		var ele = $(this).siblings('.animate-hidden-container').children('.narrow-column-body');
		var down = false;
		if (ele.css('display') !== 'none') {
			down = true;
		}
		
		animateNotShow(ele,down);
	});
	
	
	
	/* fix .fixed elements onscroll */
	$(window).scroll(function()
	{
		fixElements();
	});
	
	
	/* align absolutely positioned elements to their holder */
	$('.absolute').each(function() 
	{
		alignAbsolute($(this));
	})
			
	
	/* dropdown animation onclick */
	$('.nav-click-dropdown').click(function(e)
	{
		if (notificationDropdown) {
			// Notification dropdown is down, stopPropagation prevents document.click, manual override
			$('#nav-back-notification-reverse').trigger('click');
		}
		if (dropdowns.dropdownMenuDown) {
			// Dropdown menu is down
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').removeClass('dropdown-menu-container-reverse');
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').children('p').removeClass('dropdown-menu-container-reverse-text');

			dropdownMenu(dropdowns.dropdownMenuDown);
		}
		if (!$(e.target).is('.header-search-result') && !$(e.target).parents('#headerSearchForm').length > 0) {
			// Hide results container for search
			$('#header-search-results-container').hide();
		}
		
		e.stopPropagation();
		var dropdown = $(this).children('.dropdown-back-outer');
		var down     = false;
		if (dropdown.attr('down') == 'true') {
			// Dropdown is currently down, bring it up
			down = true;
			// Swap dropdown indicator
			dropdown.attr('down','false');
			dropdownClickDown = false;
		} else {
			// Dropdown is currently up, bring it down
			dropdown.attr('down','true');
			dropdownClickDown = $(this);
		}
		
		animateNavDropdown($(this), down);
		
	})
	
	/* change color of nav-cog onclick */
	$('#nav-back-cog').click(function()
	{
		$(this).find('.nav-cog-background').toggleClass('nav-cog-selected');
	})
	
	/* change background img of nav-notification  onclick */
	$('#nav-back-notification-reverse').click(function(e)
	{
		if (dropdownClickDown) {
			// Nav dropdown is down, stopPropagation prevents document.click, must do manually
			dropdownClickDown.trigger('click');
		}
		if (dropdowns.dropdownMenuDown) {
			// Dropdown menu is down
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').removeClass('dropdown-menu-container-reverse');
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').children('p').removeClass('dropdown-menu-container-reverse-text');

			dropdownMenu(dropdowns.dropdownMenuDown);
		}
		if (!$(e.target).is('.header-search-result') && !$(e.target).parents('#headerSearchForm').length > 0) {
			// Hide results container for search
			$('#header-search-results-container').hide();
		}
		
		e.stopPropagation();
		
		var dropdown = $('#notifications-container');
		
		if ($(this).css('opacity') == 0) {
			// Reverse is hidden, show it and show dropdown
			$(this).stop().animate({opacity: 1}, 200);
			notificationDropdown = true;

			dropdown.show()
			alignDropdown(dropdown, $(this), 'center');
			resetNotifications();
			
		} else {
			// Dropdown is already down, bring it up
			$(this).stop().animate({opacity: 0}, 200);
			notificationDropdown = false;
			
			dropdown.hide();
		}
		
		$('#nav-notification-indicator-container').hide();
		
		
	})
	
	/* cannot nest anchor tags, force redirect of notification-container.click */
	$('.notification-container').click(function()
	{
		window.location.href = $(this).attr('href');
	})
	
	$('.notification-container.light-green-back').mouseenter(function()
	{
		$(this).removeClass('light-green-back');
	});
	
	/* preload any images that require preloading */
	preloadImages(preloadImageArray);		
	
	
	/* dropdown animation onhover */
	$('.nav-dropdown').hover(function()
	{
		mouseoverDropdown = true;
		
		animateNavDropdown($(this), false);
		
	},
	function()
	{
		mouseoverDropdown = false;
		
		animateNavDropdown($(this), true);
		
	})
	
	
	/* animate opaque text lighter */
	$('.animate-opacity').hover(function()
	{
		if (!$(this).attr('opacity')) {
			// Opacity attribute has not been set before, set it
			$(this).attr('opacity', $(this).css('opacity'));
		}
		
		$(this).stop().animate({opacity: 1}, 200);
	},
	function()
	{
		var originalOpacity = $(this).attr('opacity');
		$(this).stop().animate({opacity: originalOpacity}, 200);
	})
	
	
	/* fade input text on focusin and focusout */
	$('input[type=text],input[type=password]').focusin(function()
	{
		$(this).parents('.nav-dropdown').trigger('mouseover');
		fadeOutInputOverlay($(this), true);
		
		// Tooltip
		if($(this).parents('.input-container').attr('tooltip')) {
			startTooltipTimer($(this).parents('.input-container'));
		}
		
	})
	.keyup(function()
	{
		fadeOutInputOverlay($(this), false)
	})
	.focusout(function()
	{
		fadeOutInputOverlay($(this), false)
		if (mouseoverDropdown == false) {
			$(this).parents('.nav-dropdown').trigger('mouseout');
		}
		
		// Tooltip
		if($(this).parents('.input-container').attr('tooltip').length > 0) {
			endTooltipTimer();
			$('#tooltip').stop().animate({opacity:0},{duration:100, complete: function() {
																				$(this).hide()
			}});			
		}
				
	});
	
	
	/* onload perform fade effect for case when input box has value */
	$('input[type=text],input[type=password]').each(function()
	{
		fadeOutInputOverlay($(this), false);
	})
	
	
	/* force focus of textbox when overlay for input is clicked */
	$('.input-container').click(function() 
	{
		$(this).children('input').focus();
	});
	
	
	/* cause sibling checkbox to be selected when click accompanying text */
	$('.checkbox-text').click(function()
	{
		$(this).siblings('input[type=checkbox]').trigger('click');
	})
	
	
	/* change color of selectable text */
	$('.selectable-text').click(function() {
		$(this).toggleClass('green-bold');
	})	
	
	
	/* set behavior for all alert boxes (close them onclick) */
	$('.alert-black-back,.alert-x').click(function()
	{
		$('.alert-container').hide();
		$('.alert').animate({'opacity':0},{duration: 200, complete: function() {
																		$('.alert').hide()
																				   .css('opacity',1)
																	}
		})
	})
	
	/* test all elements for tooltip onhover */
	$('*').bind('mouseenter.tooltip',function()
	{
		
		if($(this).parents('.input-container').attr('tooltip')) {
			return;
		}
		
		
		if ($(this).attr('tooltip')) {
			var ele = $(this);
			startTooltipTimer(ele);
		}
	}).bind('mouseleave.tooltip', function()
	{
		if(tooltipEle.is('.input-container')) {
			return;
		}
		
		endTooltipTimer();
		
		$('#tooltip').stop().animate({opacity:0},{duration:50, complete: function() {
																				$(this).hide()
																		}
									});
		
	});	
	
	
	$(document).click(function(e)
	{
		// ANY IF STATEMENTS MUST BE ADDED TO THE OTHER IF STATEMENT'S HANDLERS TO AVOID STOPPROPAGATION BUG
		if ($('#city-change-container').css('display') !== 'none') {
			// Change city dropdown is down and user clicked on something that isn't it
			if ($(e.target).is('#header-city')) {
				return;
			} else if ($(e.target).is ('#city-change-container') || $(e.target).parents('#city-change-container').length > 0) {
				return;
			}
			$('#header-city').trigger('click');
		}
		
		if (dropdownClickDown) {
			// Nav dropdown is down
			dropdownClickDown.trigger('click');
		}
		
		if (notificationDropdown) {
			// Notification dropdown is down
			
			if ($(e.target).is('#notifications-container') ||
				$(e.target).parents('#notifications-container').length > 0) {
				// Clicked notification box, return
				return;
			}
			$('#nav-back-notification-reverse').trigger('click');
		}
		
		if (dropdowns.dropdownMenuDown) {
			// Dropdown menu is down
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').removeClass('dropdown-menu-container-reverse');
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').children('p').removeClass('dropdown-menu-container-reverse-text');

			dropdownMenu(dropdowns.dropdownMenuDown);
		}
		
		if (!$(e.target).is('.header-search-result') && !$(e.target).parents('#headerSearchForm').length > 0) {
			// Hide results container for search
			$('#header-search-results-container').hide();
		}
		
		

	});
		
})


/**
 * Ajax call to get city name from zipcode
 * @params(zipcode => 5 digit zipcode)
 */
function getCity(zipcodeOrCity, callback)
{

	$.ajax({
		url: '/ajax/get-city-state',
		type: 'POST',
		data: {zipcodeOrCity: zipcodeOrCity},
		success: function(data) {
			callback(data);
		}
	})
}

/**
 * Ajax call to search database for username, park, league, game, team, or group
 * @params (search => search term to look for)
 */
function searchDatabase(searchTerm, callback)
{
	$.ajax({
		url: '/ajax/search-db',
		type: 'POST',
		data: {search: searchTerm},
		success: function(data) {
			data = JSON.parse(data);
			callback(data);
		}
	})
}

/**
 * Ajax call to set user's lastRead to current time
 */
function resetNotifications()
{
	$.ajax({
		url: '/ajax/reset-notifications'
	});
}

/**
 * Ajax call to set user's location to new city
 */
function setUserLocation(cityID)
{
	$.ajax({
		url: '/ajax/change-user-city',
		type: 'POST',
		data: {cityID: cityID},
		success: function(data) {
			location.reload();
		}
	});
}


/** 
 * test dropdown menu for animation up or down
 * @params(dropdownEle => outer dropdown container that is being acted upon)
 */
function dropdownMenu(dropdownEle)
{
	
		var dropdown = dropdownEle;
		var selected = dropdown.children('.dropdown-menu-selected');
		var holder   = dropdown.prev('.dropdown-menu-holder');
		var options  = dropdown.children('.dropdown-menu-hidden-container');
		
		if (dropdowns.dropdownMenuDown) {
			if (dropdowns.dropdownMenuDown.attr('id') == dropdown.attr('id')) {
				// Dropdown is already down
				options.animate({opacity: 0}, {duration: 200, complete: function() {
																			options.hide();
																			dropdown.css({position: 'static',
																						  top     : 0,
																						  left    : 0});
																			holder.hide()
																			}
											  }
								);
								
				dropdowns.dropdownMenuDown = false;
				return;
			}
		}
			
		dropdown.css({top: 		selected.position().top,
					  left: 	selected.position().left,
					  position: 'absolute'});
					  					  
					  
		holder.css({width:  dropdown.outerWidth(true),
					height: selected.outerHeight(true)})
			  .show();
			  
		options.css('display', 'block')
			   .animate({opacity: 1}, 200);
			      
		
		dropdowns.dropdownMenuDown = dropdownEle;

}



/** 
 * callback function from smart slider plugin to handle slider's values
 * @params(sliderEle => slider element that is being acted upon,
 		   value	 => numeric value returned from slider plugin)
 */
function populateSliderText(sliderEle, value)
{
	
	var containerEle   = sliderEle.parents('.slider-container');
	var valueEle 	   = containerEle.children('.slider-text-value');
	var descriptionEle = containerEle.children('.slider-text-description');
	var textValue	   = sliderSkillValues[value]['level'];
	
	valueEle.html(textValue);
	
	if (descriptionEle.length > 0) {
		// Description ele exists, populate
		var descriptionValue = sliderSkillValues[value]['description'];
		descriptionEle.html(descriptionValue);
	}
	
	return;
}


/**
 * populate change city container with results from ajax call
 * @params (cities => returned cities)
 */
function populateCityResults(cities) 
{
	cities = JSON.parse(cities);
	var output = '';
	
	for (i = 0; i < cities.length; i++) {
		output += "<p class='city-change-result lighter bold smaller-text pointer' cityID='" + cities[i]['cityID'] + "'>" + cities[i]['city'] + ", " + cities[i]['state'] + "</p>";
	}
	
	$('#city-change-results-container').html(output);
}


/**
 * populate header search results
 * @params (results => returned results from ajax)
 */
function populateSearchResults(results) 
{
	// Align search results with search input
	var searchBar = $('.header-search-bar');
	var left	  = searchBar.offset().left;
	var top	  	  = searchBar.offset().top + searchBar.innerHeight();
	var width	  = searchBar.outerWidth();
	var searchVal = searchBar.val();
		
	$('#header-search-results-container').css({'left':  left,
											   'top':   top,
											   'width': width});
	
	var output = '';
	
	if (results.length < 1) {
		// No results
		output += "<div class='header-search-result dark-back medium'>No results found</div>";
	} else {
		// Results found
	
		for (i = 0; i < results.length; i++) {
			
			if (i >= 2) {
				// Limit to 5 results, display more results afterwards
				output += "<a href='/find/search/" + encodeURIComponent(searchVal).replace(/%20/g, '+').toLowerCase() + "' class='header-search-result dark-back medium pointer'>" + (results.length - i) + " more results</a>";
				break;
			}
			if (results[i]['name'].length > 21) {
				// Limit any name to 20 characters
				results[i]['name'] = results[i]['name'].substring(0,20) + '..';
			}
			
			output += "<a href='/" + results[i]['prefix'] + "/" + results[i]['id'] + "' class='header-search-result dark-back lighter pointer'>\
						<p class='clear header-search-result-name'> " + results[i]['name'] + "</p>";
						
			if (results[i]['prefix'] !== 'users') {
				// Not users, show what "type" result is
				output += "<p class='clear-right medium smaller-text header-search-result-subtext'>" + capitalize(results[i]['prefix'].slice(0,-1)) + "</p>";
			}
			
			output += "</a>";
		}
	}
	
	$('#header-search-results-container').html(output);
	
	if ($('#headerSearchBar').is(':focus') && $('#headerSearchBar').val().length >= 3) {
		// Search bar has focus and val is greater than 2 (protect against accidently overfire due to ajax delay
		$('#header-search-results-container') .show();
	}
	
	$('.header-search-result').highlight(searchVal);
	
}


/**
* change background of element to green or grey
* @params (ele  	 => element to change,
		   removeOld => should we remove old selected values? (boolean))
*/
function toggleGreenBackground(ele, removeOld)
{
	if (removeOld) {
		// Remove old green value from other ele
		$('.selected-green').removeClass('selected-green');
	}
	
	
	ele.toggleClass('selected-green');
	/*
	if (ele.is('.selected-green')) {
		// Already green, revert to original color
		ele.removeClass('selected-green');
	} else {
		// Not green, make it green!
		ele.addClass('selected-green');
	}
	*/
}



/**
* function to animate div down or up, not display
* @params (ele    => element to animate,
		   down   => is ele already down? (boolean),
		   fadeIn => should we fade animation in? (boolean))
*/
function animateNotShow(ele, down, fadeIn)
{
	
var height = ele.outerHeight();
	if (down) {	
		// Hidden element is down, animate it up
		ele.stop().animate({marginTop: -height}, {duration:400, complete: function() {
																			$(this).hide()
																			}
		});
	} else {
		// Hidden element is up, animate it down
		ele.css({marginTop: -height + 'px',
				 opacity: 0})
		   .show();
		
		if (!fadeIn) {
			// Do not fade in
			ele.stop().css('opacity', 1)
					  .animate({marginTop: 0}, 400);
		} else {
			// Fade in after animation is complete
			ele.stop().animate({marginTop: 0}, {duration:400, complete: function() {
																			$(this).animate({opacity: 1}, 400)
																		}
			});
		}
		
		ele.addClass('animate-hidden-selected');
	}
	

}



/**
* function to cause fade effect for series of imgs
* @params
*/
function fadeImgToNext()
{
	var currentEle = $('.fade-current');
	var nextEle = $('.fade-next');
	var duration = 1000;
	var eleClass = $.trim(currentEle.attr('class').replace('fade-current',''));
	var nextNextEle = nextEle.next('.' + eleClass);
	
	if (nextNextEle.length == 0) {
		nextNextEle = $('.' + eleClass).first();
	}
	
	fadeRunning = true;
	
	currentEle.animate({'opacity': 0}, {duration: duration,
										queue	: true,
										complete: function()
												{
													resetFadeOpacityAndClass();
													setNextImgFade(nextNextEle);
													fadeRunning = false;
												}
	});
}

/**
* function to set next image fade
* @params (nextEle => img or div ele to be set as next)
*/
function setNextImgFade(nextEle)
{
	nextEle.addClass('fade-next');
}

/**
* reset fading img opacity and class definition for reuse
* @params (ele => .fade-current to be reset
*/
function resetFadeOpacityAndClass()
{
	$('.fade-current').removeClass('fade-current')
	   	   			  .css({'opacity': 1});
	$('.fade-next').removeClass('fade-next')
		  		   .addClass('fade-current');
}
	

/**
* function to fade out or hide overlay text for input
* @params (inputEle => input type element
*		   focusIn  => true/false if input is focusin or focusout)
*/
function fadeOutInputOverlay(inputEle, focusIn)
{
	var overlayEle = inputEle.next('.input-overlay');
	var inputVal = $.trim(inputEle.val());
	if (inputVal !== '') {
		overlayEle.hide();
	} else {
		overlayEle.show();
		if (focusIn || inputEle.is(':focus')) {
			overlayEle.animate({'opacity':'.4'},200);
		} else {
			overlayEle.animate({'opacity':'1'},200);
		}
	}
}


/**
 * Ajax upload and retrieve picture from input[type=file]
 * @params (navEle => container element of dropdown (ie nav-back),
 			down   => is it down? (boolean))
 */
function animateNavDropdown(navEle, down)
{
		var outerEle = navEle.children('.dropdown-back-outer');
		var innerEle = outerEle.children();
		
		// Align dropdown with navigation
		if (navEle.attr('aligned') !== 'true') {
			alignDropdownContainer(outerEle);
		}

		
		if (!down) {
			// Dropdown is up, animate it down
			outerEle.show()
					.css('z-index', '100');
			innerEle.stop().animate({marginTop : 0}, 300);
		} else {
			// Dropdown is down, animate it up
			var height = innerEle.height();
		
			//if input boxes within this dropdown are focused, do not animate		
			if (innerEle.find('input[type=text],input[type=password]').is(':focus')) {
				return;
			}
		
			innerEle.stop().animate({marginTop : -height},{duration : 300,
													complete : function()
														{
															outerEle.hide();
														}
													});
		}

			
}


/**
* align dropdown containers with corresponding nav button
* @params (ele => dropdown-back-outer element)
*/
function alignDropdownContainer(ele)
{
		var parent = ele.parent();

		//var top  = parent.offset().top + parent.height();
		var windowWidth = $(window).width();
		var bodyWidth   = $('.centered-body').width();
		var parentWidth = parseInt(parent.offset().left,10) - (parent.outerWidth(true) - parent.innerWidth()) - parseInt(ele.css('padding-left'),10);
		var top    		= $('.header-bar').height();
		var difference  = (windowWidth - bodyWidth)/2;
		if (windowWidth < bodyWidth) {
			difference  = 0;
		}
		var left   		= parentWidth - difference;
		
		if (parent.css('float') == 'right' && parent.attr('id') !== 'nav-back-cog') {
			var parentWidth = parent.outerWidth(true);
			var dropdownWidth = ele.width();
			var widthDiff = parentWidth - dropdownWidth;
			left = left + widthDiff;
		}
		
		ele.css({top : top,
				 left: left});
				 
		parent.attr('aligned', 'true');
}


/**
 * align any dropdown to alignEle on left, center, or right
 * @params (alignEle  => element to align to,
 *			moveEle   => element to align,
 *			alignment => how to align (center, right, left)
 */
 function alignDropdown(moveEle, alignEle, alignment)
 {
	 var alignedWidth = alignEle.outerWidth(true);
	 var alignedPos   = alignEle.offset().left;
	
	 var windowWidth  = $(window).width();
	 var bodyWidth    = $('.centered-body').width();
	  
	 var bodyWindDiff = (bodyWidth > windowWidth) ? 0 : (windowWidth - bodyWidth)/2;

	 var left = alignedPos;
	
	 if (alignment == 'right') {
		 left -= alignedWidth;
	 } else if (alignment == 'center') {
		 var moveWidth = moveEle.width();
		 left -= moveWidth/2;
	 }
	 
	 moveEle.css('left',left);
	 
	 return;
 }
		 




/**
* align absolutely positioned elements with holder
* @params (ele => .absolute element)
*/
function alignAbsolute(ele)
{
	var holder = $('#' + ele.attr('holder'));
	if (holder.length < 0) {
		// No holder element existing
		return;
	}
	var top = holder.offset().top;
	
	ele.css('top',top);
}

/**
* fix ".fixed" elements on scroll
* @params (ele => .fixed element)
*/
function fixElements()
{
	var scrollTop = $(window).scrollTop();
	
		$('.fixed').each(function()
		{
			if (scrollTop < $(this).parent().offset().top) {
				$(this).css({position: 'static'})
			} else if ((scrollTop >= ($(this).offset().top - 10))) {
				
				var width = $(this).width();
				var height = $(this).height();
				$(this).css({position: 'fixed',
							 width	 : width,
							 height  : height,
							 top	 : 10});
			} 
		});
	
}


/**
* fix ".fixed" elements on scroll
* @params (ele => .fixed element)
*/
function startTooltipTimer(ele)
{
	if(tooltipTimer) {
		clearTimeout(tooltipTimer)
	}
	
	tooltipTimer = setTimeout(function()
					{
						showTooltip(ele);
					}, 500);
	
}

function endTooltipTimer()
{
	clearTimeout(tooltipTimer);
}


/**
* fix ".fixed" elements on scroll
* @params (ele => .fixed element)
*/
function showTooltip(ele)
{
	tooltipEle = ele;
	
	var value = ele.attr('tooltip');
	var top   = ele.offset().top + ele.innerHeight() + 3;
	var left  = ele.offset().left;
	
	$('#tooltip-body').html(value);
	
	var tooltipWidth = $('#tooltip').innerWidth();
	var endOfBody    = $('#body-white-back').innerWidth() + $('#body-white-back').offset().left;
	
	if ((tooltipWidth + left) > endOfBody) {
		$('.tooltip-tip').css('float','right');
		left = left - (tooltipWidth - ele.innerWidth());
	} else {
		$('.tooltip-tip').css('float','left');
	}
		
	$('#tooltip').show()
				 .stop()
				 .css({top: top,
					   left:left})
				 .animate({opacity: 1},200);
	
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
          mapTypeId: google.maps.MapTypeId.ROADMAP
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
 * Initialize google geocode for address 
 * @params(address => street address + city + zipcode,
 *		   callbackSuccess => callback for successful retrieval
 *		   callbackFailure => callback for no address found)
 */
function getCoordinatesFromAddress(address, callbackSuccess, callbackFailure)
{
		//calling initial within success function fails to produce proper
		//address[count] and so second run through fails
		var tempArray = new Array();
		var gcReq = {address: address};
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode(gcReq, function(results,status){
		if (status == google.maps.GeocoderStatus.OK) {
			// Results were found
			userLocation[0]  = results[0].geometry.location.lat();
			userLocation[1]  = results[0].geometry.location.lng();
			callbackSuccess();
		} else {
			// No results found
			callbackFailure();
		}
			
	   });
	   		   
				
		   

}



/** capitalize first letter of text
 * @params(text => text to capitalize)
 */
function capitalize(text) 
{
	 
		  text = text.replace(/(\b)([a-zA-Z])/, function(firstLetter) {
												return firstLetter.toUpperCase()
																}
					  		  );
		  return text;
}


function getRGB(color) 
{

	var matchColors = /rgb\((\d{1,3}), (\d{1,3}), (\d{1,3})\)/;
	var match = matchColors.exec(color);
	
	var r = match[1];
	var g = match[2];
	var b = match[3];
	
	var colors = new Array();
	colors[0] = r;
	colors[1] = g;
	colors[2] = b;
	
	return colors;
}

function componentToHex(c) 
{
    var hex = c.toString(16);
    return hex.length == 1 ? "0" + hex : hex;
}

function rgbToHex(r, g, b) 
{
    return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
}

function getDarkerColor(color)
{
	var rgb = getRGB(color);
	
	for (i = 0; i < 3; i++) {
		rgb[i] -= 25;
	}
	
	var hex = rgbToHex(rgb[0], rgb[1], rgb[2]);
	
	return hex;
}

function feetToInches(feet, inches) 
{
	return ((feet * 12) + parseFloat(inches));
}

function inchesToFeet(inches) 
{
	var feet = Math.floor(parseFloat(inches)/12);
	var inchesNew = parseFloat(inches) - (feet * 12);
	return {feet: feet, inches: inchesNew};
}

function isNumber(n) 
{
  return !isNaN(parseFloat(n)) && isFinite(n);
}

/**
 * makes all things on page selectable or unselectable
 * @params(selectable => should text be selectable? (boolean))
 */
function makeTextSelectable(selectable)
{
	var css;
	if (!selectable) {
		css = {'-webkit-touch-callout': 'none',
				'-webkit-user-select': 'none',
				'-khtml-user-select': 'none',
				'-moz-user-select': 'none',
				'-ms-user-select': 'none',
				'user-select': 'none'};
	} else {
		css = {'-webkit-touch-callout': 'text',
				'-webkit-user-select': 'text',
				'-khtml-user-select': 'text',
				'-moz-user-select': 'text',
				'-ms-user-select': 'text',
				'user-select': 'text'};
	}
	
	$('*').css(css);
}

/**
 * preload images in array (useful for changed background image and image src)
 * @params (imageArray => array of image src)
 */
function preloadImages(imageArray)
{
	$(imageArray).each(function () {
        $('<img />').attr('src',this).appendTo('body').hide();
    });
}


function clearMarkers() {
    for(var i=0; i < markers.length; i++){
		
        markers[i].setMap(null);
    }
    markers = new Array();
};
	

