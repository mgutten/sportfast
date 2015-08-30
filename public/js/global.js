// global.js
var mouseoverDropdown = false; /* fix issue when input is focus, then not focus and mouse is not over */
var dropdownClickDown = false;
var fadeRunning 	  = false;
var tooltipTimer;
var tooltipEle 		  = null;
var confirmAlertTimer;
var sliderSkillValues = [];
sliderSkillValues[0]  = {level:'Beginner',
						description: 'I have rarely (if ever) played.'};
sliderSkillValues[1]  = {level:'Decent',
						description: 'I play infrequently, or have difficulty keeping up when I do play.'};
sliderSkillValues[2]  = {level:'Good',
						description: 'I am an average player.  Nothing fancy, just good fundamentals.'};
sliderSkillValues[3]  = {level:'Better',
						description: 'I am skilled.  I am better than the average player.'};
sliderSkillValues[4]  = {level:'Talented',
						description: 'I am very skilled.  I am a big step above the average player.'};
sliderSkillValues[5]  = {level:'Unstoppable',
						description: 'I played (or should play) on a higher level.'};

var sliderSportsmanshipValues = [];
sliderSportsmanshipValues[0] = {level:'Bad',
								description: 'Poor sportsmanship.  Not fun to play with.'};
sliderSportsmanshipValues[1] = {level:'Good',
								description: 'Fine sportsmanship.  A typical player.'};
sliderSportsmanshipValues[2] = {level:'Excellent',
								description: 'Made the game more enjoyable with excellent attitude.'};
						
var mouseoverColor;
var dropdowns = new Array();
var notificationDropdown = false;
var preloadImageArray    = new Array('/images/global/header/notification_shield_reverse.png');
var gmapMarkers;
var gmap;
var userLocation = new Array();
var curDate = new Date();
var notificationScrolling;
var loggedIn; // set to curtime of page load if user is logged in
var notificationPoll; // used as setInterval for notificationPoll
var pageTitle;
var notificationTitle;
var titleInterval;
var confirmAction;


$(function()
{

	/* jquery plugin to limit value of input */
	(function($) {
		
	  $.fn.limitVal = function(lower, upper) {
			var val = this.val();
			var lowerLength = lower + '';

			if((val.length == 0) ||
			   (val.length < lowerLength.length) ||
			   (parseInt(val + '0',10) <= upper)) {
				// Empty string
				return this;
			}
			
			if (val > upper) {
				this.val(upper);
			} else if(val < lower) {
				this.val(lower)
			}
			return this;
	
	  };
	  
	  /* force text box to only allow numbers */
	   $.fn.forceNumeric = function() {
		
			return this.each(function()
			{
			$(this).keydown(function(e)
				{
				var key = e.charCode || e.keyCode || 0;
				// allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
				return (
					key == 8 || 
					key == 9 ||
					key == 46 ||
					(key >= 37 && key <= 40) ||
					(key >= 48 && key <= 57) ||
					(key >= 96 && key <= 105));
			});
		});
	
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
			
			this.stop().animate({backgroundColor: darkerColor},300);
			
	  }
	  
	  /* animate element background darker */
	  $.fn.animateLighter = function() {
		  
		 	this.stop().animate({backgroundColor: this.attr('color')},300);		 
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
	
	
	$(document).on('click','.dropdown-menu-option-text',function()
	{
		
		if ($(this).parent().is('a')) {
			// Option is nested in an anchor tag, redirect to anchor tag (fixes bug where would not fire anchor href onclick)
			window.location = $(this).parent().attr('href');
		}
	})
	
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
	
	$(document).on('click.swapValue','.dropdown-menu-option-container',function()
	{
		//Option has been clicked		
		var value = $(this).children('p').text();
		$(this).parents('.dropdown-menu-hidden-container').prev('.dropdown-menu-container').children('.dropdown-menu-selected').children('p').text(value);

	})
	
	
	/* animate darker background */
	$(document).on('mouseenter','.animate-darker',function()
	{
		$(this).animateDarker();
	})
	.on('mouseleave','.animate-darker',function()
	{
		$(this).animateLighter();
	})
	
	/* show rating percent on mouseover */
	$(document).on('mouseenter','.user-sport-rating-other-outer', function()
 		{
			$(this).find('.user-sport-rating-percent').show();
		}
	)
	.on('mouseleave','.user-sport-rating-other-outer', function()
		{
			$(this).find('.user-sport-rating-percent').hide();
		}
	)
	
	/* schedule "in" and "out" button on click */
	$('.schedule-in,.schedule-out,.schedule-maybe').click(function(e)
	{
		e.preventDefault();
		
		if ($(this).is('.member-schedule-button-selected')) {
			// Is already selected 
			return;
		}
		
		$(this).parent().children('.member-schedule-button-selected').removeClass('member-schedule-button-selected inner-shadow');
		
		$(this).addClass('member-schedule-button-selected inner-shadow');
		
		
		
		var inOrOut = $(this).text().toLowerCase();
		var idType	= $(this).parent().attr('idType');
		var id		= $(this).parent().attr('typeID');
		
		//var insertOrUpdate = 'insert';
		//var teamID  = $(this).parent().attr('teamID');
	
		/*
		if (typeof $(this).parent().attr('existingID') !== 'undefined') {
			// Row exists in db, update
			insertOrUpdate = $(this).parent().attr('existingID');
		}
		*/
		
		//confirmUserToGame(inOrOut, type, id, insertOrUpdate, teamID);
		confirmUserToGame(inOrOut, idType, id);
		/*
		var confirmed = $(this).parents('.schedule-container').find('.confirmed');
		var confirmedNum = parseInt(confirmed.text(), 10);
		if (inOrOut == 'in') {
			// In clicked, add 1 to confirmed list
			confirmedNum += 1;
		} else {
			confirmedNum -= 1;
		}
		confirmed.text(confirmedNum);
		*/
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
		$(this).stop().animate({'background-color': backgroundColor},300);
	},
	function()
	{
		if ($(this).is('.nav-dropdown') || $(this).is('#nav-back-signup')){
			return;
		}
		$(this).stop().animate({'background-color':'transparent'},300);
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
	
	
	/* any "selectable" classes toggle selected class onclick */
	$('.selectable').click(function()
	{
		$(this).toggleClass('selected');
	})
	
	
	/* Search bar ajax call */
	$('#headerSearchBar').keyup(function(e)
	{
		if (e.keyCode == 40 ||
			e.keyCode == 39 ||
			e.keyCode == 38 ||
			e.keyCode == 37) {
				// Hit an arrow key, return false
				return;
			}
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
	
	
	/* Search function for "invite" button */
	$('#inviteSearchBar').keyup(function()
	{

		if ($(this).val().length < 3) {
			// Blank box (or too short), do not run check
			$(this).parent().next('.dropdown-menu-option-results').hide();
			$(this).parent().parent().children('.dropdown-menu-option-default').show();
			$(this).parent().next('.dropdown-menu-option-default').show();
			return;
		}

		var limit = new Array('users');
		searchDatabase($(this).val(), populateSearchResultsInviteButton, limit);
	})
	
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
			$('#header-search-results-container').html('');
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
			$('#header-search-results-container').html('');
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
		
		clearInterval(titleInterval);
		setTitle(pageTitle);
		$('#nav-notification-indicator').html('0');
		$('#nav-notification-indicator-container').hide();
		
		
	})
	
	$('#notifications-container').bind('mousewheel', function(e,delta){
		e.preventDefault();
		
		if (notificationScrolling) {
			return false;
		}
		notificationScrolling = true;
        if(e.originalEvent.wheelDelta /120 > 0) {
			$(this).stop().animate({scrollTop: $(this).scrollTop() - 80}, {duration:200, complete: function() {
																											notificationScrolling = false;
			}});
        }
        else{
			// Down
			$(this).stop().animate({scrollTop: $(this).scrollTop() + 80}, {duration:200, complete: function() {
																											notificationScrolling = false;
			}});
        }
    });
	
	/* show remove notification onhover */
	$('.notification-container').hover(function()
	{
		$(this).find('.notification-remove').show();
	},
	function()
	{
		$(this).find('.notification-remove').hide();
	})
	
	/* delete notification on click x */
	$(document).on('click', '.notification-remove', function(e)
	{
		e.stopPropagation();
		e.preventDefault();
		
		var notificationLogID = $(this).parents('.notification-container').attr('notificationLogID');
		
		deleteNotification(notificationLogID);
		
		$(this).parents('.notification-container').hide();
		
	})
	
	/* notification Confirm or Decline button was clicked */
	$(document).on('click','.notification-action-button',function(e)
	{
		e.preventDefault();
		e.stopPropagation(); // To prevent $('.notification-container').click from firing
		
		if (typeof $(this).attr('clicked') != 'undefined') {
			
			return false;
		}
		
		
		$(this).attr('clicked', true);
		
		var notificationLogID = $(this).parent().attr('notificationLogID');
		var type = $(this).parent().attr('type');
		var action = $(this).parent().attr('action');
		var confirmOrDeny 	  = $(this).text().toLowerCase();
		var optionalID = '';
		
		
		notificationConfirmDeny(notificationLogID, confirmOrDeny, type, action, optionalID);
		
	})
	
	/* notification Join button was clicked */
	$(document).on('click', '.notification-join', function(e)
	{
		e.preventDefault();
		e.stopPropagation(); // To prevent $('.notification-container').click from firing
		
		if (typeof $(this).attr('clicked') != 'undefined') {
			// Prevent 2 notifications
			return false;
		}
		
		$(this).attr('clicked', true);
		
		var notificationLogID = $(this).parent().attr('notificationLogID');
		var type = $(this).parent().attr('type');
		
		var url = $(this).parents('a').attr('href');
		
		notificationJoin(notificationLogID, type, url);
		
	})
	
	if (loggedIn) {
		// User is logged in, check for notifications
		notificationPoll = setInterval(function() {
				getNewNotifications()
		}, 45000);
		
		pageTitle = getTitle();
	}
	
	/* cannot nest anchor tags, force redirect of notification-container.click (could now be changed to simple a tag) 
	$('.notification-container').click(function()
	{
		if ($(this).attr('href')) {
			window.location.href = $(this).attr('href');
		}
	})*/
	
	$('.notification-container.light-back').mouseleave(function()
	{
		$(this).removeClass('light-back');
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
	$('.animate-opacity').bind('mouseenter.animateOpacity',function()
	{
		if ($(this).is('.clicked')) {
			// Has clicked class, do nothing
			return;
		}
		
		if (!$(this).attr('opacity')) {
			// Opacity attribute has not been set before, set it
			$(this).attr('opacity', $(this).css('opacity'));
		}
		
		$(this).stop().animate({opacity: 1}, 200);
	})
	.bind('mouseleave.animateOpacity',function()
	{
		
		if ($(this).is('.clicked')) {
			// Has clicked class, do nothing
			
			$(this).trigger('mouseleave.tooltip');
			return false;
		}
		
		var originalOpacity = $(this).attr('opacity');
		$(this).stop().animate({opacity: originalOpacity}, 200);
		
	})
	
	
	/* Safari bug fix where hover over parent a tag causes all children to get text-underline*/
	$(document).on('mouseenter','a',function()
	{
		if ($(this).find('p').length > 0) {
			// There are paragraph eles
			$(this).css('text-decoration', 'none');
		}
	});
	
		
	
	/* fade input text on focusin and focusout */
	$(document).on('focusin', 'input[type=text],input[type=password],textarea',function()
	{
		$(this).parents('.nav-dropdown').trigger('mouseover');
		
		fadeOutInputOverlay($(this), true);
		
		if ($(this).is('.input-fail')) {
			$(this).removeClass('input-fail');
		}
		
		// Tooltip
		if($(this).parents('.input-container').attr('tooltip')) {
			startTooltipTimer($(this).parents('.input-container'));
		}
				
		
	})
	.on('keyup', 'input[type=text],input[type=password],textarea', function(e)
	{

		fadeOutInputOverlay($(this), false);
		
		
	})
	.on('focusout', 'input[type=text],input[type=password],textarea', function()
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
	$(document).on('click', '.input-container', function(e) 
	{

		$(this).children('input,textarea').focus();
	});
	
	/* force tooltip to show on focus of input */
	$('input, textarea').focus(function()
	{
		if ($(this).attr('tooltip') != '') {
			$(this).trigger('mouseenter')
		}
	})	
	.blur(function()
	{
		if ($(this).attr('tooltip') != '') {
			$(this).trigger('mouseleave')
		}
	})
	
	
	/* cause sibling checkbox to be selected when click accompanying text */
	$('.checkbox-text').click(function()
	{
		$(this).siblings('input[type=checkbox]').trigger('click');
	})
	
	
	/* change color of selectable text */
	$('.selectable-text,.selectable-text-one').click(function() {
		if ($(this).is('.selectable-text-one')) {
			// Only one can be chosen at a time
			if ($(this).is('.green-bold')) {
				// Clicked currently selected text
				return false;
			}
			$(this).siblings('.green-bold').removeClass('green-bold')
		}
		
		$(this).toggleClass('green-bold');
	})
	
	/* change calendar to new month */
	$('#calendar-right-arrow,#calendar-left-arrow').click(function()
	{
		var curMonthEle = $(this).parents('.calendar-container');
		var newMonthEle;
		var curMonth = parseInt(curMonthEle.find('.calendar-month-name').attr('monthID'),10);
		var newMonth;
		
		if ($(this).is('#calendar-right-arrow')) {
			// Right arrow, move forward in time
			newMonth = (curMonth + 1 > 12 ? 1 : curMonth + 1);
			newMonthEle = curMonthEle.siblings('#calendar-container-' + newMonth)
			
			if (newMonthEle.length < 1) {
				// Does not exist
				return false;
			}
			
			if (newMonth > (curDate.getMonth() + 1)) {
				// Show arrows if currently in past month and move to current month 
				newMonthEle.find('#calendar-right-arrow').hide();
			} else {
				newMonthEle.find('#calendar-right-arrow').show();
			}
			
		} else {
			// Left arrow
			newMonth = (curMonth - 1 < 1 ? 12 : curMonth - 1);
			newMonthEle = curMonthEle.siblings('#calendar-container-' + newMonth)
			
			if (newMonthEle.length < 1) {
				// Does not exist
				return false;
			}
			
			if (newMonth < (curDate.getMonth() + 1)) {
				// Show arrows if currently in past month and move to current month 
				newMonthEle.find('#calendar-left-arrow').hide();
			} else {
				newMonthEle.find('#calendar-left-arrow').show();
			}
		}
		
		curMonthEle.hide();
		newMonthEle.show();
		
	})
	
	/* set narrow column to height of body*/
	if ($('.body-column-narrow').length > 0) {
		$('.body-column-narrow').css('height', $('#body-white-back').innerHeight());
	}
	
	/* animate overlay (with dark back) on mouseover */
	$('.overlay-container').hover(function()
	{
		$(this).children('.overlay-pic').first().stop().animate({opacity: .5}, 300); // animate black back to mostly opaque
		$(this).children('.overlay-pic').last().stop().animate({opacity: 1}, 300);
	}, function()
	{
		$(this).children('.overlay-pic').stop().animate({opacity: 0}, 300);
	})
	
	
	/* set behavior for all alert boxes (close them onclick) */
	enableCloseAlerts();
	
	/* show explanation of profile pics onclick of special class */
	$('.why-profile').click(function()
	{
		window.open('http://sportfast.com/about/picture', 'Why a profile picture?', 'width=600,height=230');
	})
	
	/* show explanation of profile pics onclick of special class */
	$('.why-ratings').click(function()
	{
		window.open('http://sportfast.com/about/ratings', 'Ratings', 'width=600,height=300');
	})
	
	
	$('.what-ratings').click(function()
	{
		window.open('http://sportfast.com/about/signup-ratings', 'What are ratings?', 'width=600,height=200');
	})
	
	
	/* allow users to use arrow keys to move between ajax return results */
	$(document).keydown(function(e){
		
		if (($('#headerSearchBar').is(':focus') && $('.header-search-result').length > 0) ) {
				
				var ele;
				if ($('.header-search-result.selected').length > 0) {
					// Already result selected
					if (e.keyCode == 40) {
						// down 
						if ($('.header-search-result.selected').next('.header-search-result').length > 0) {
							ele = $('.header-search-result.selected').next('.header-search-result')
						} else {
							// There is no next ele, return
							return;
						}
					}
					if (e.keyCode == 38) {
						// up
						ele = $('.header-search-result.selected').prev('.header-search-result');
					}
					if (e.keyCode == 13) {
						// enter key, redirect to this
						window.location = $('.header-search-result.selected').attr('href');
						return false;
					}
					
				} else if (e.keyCode == 40 && $('.header-search-result').length > 0) {
					ele = $('.header-search-result').first();
				} else {
					return;
				}
				
				$('.header-search-result').removeClass('selected');
				ele.addClass('selected');
				 
			 }
		
	});
	
	$(document).on('mouseover', '.header-search-result', function() {
		$('.header-search-result.selected').removeClass('selected');
		$(this).addClass('selected');
	})
	.on('mouseout', '.header-search-result', function() {
		$(this).removeClass('selected');
	})
	
	/* test all elements for tooltip onhover */
	$(document).on('mouseenter.tooltip','*',function()
	{
		if(!$(this).parents('.input-container').attr('tooltip')) {
			if ($(this).attr('tooltip')) {
				var ele = $(this);
				startTooltipTimer(ele);
			}
		}
	}).on('mouseleave.tooltip','*', function()
	{
		if ($(this).attr('tooltip')) {
					
			endTooltipTimer();
			
			$('#tooltip').stop().animate({opacity:0},{duration:50, complete: function() {
																					$(this).hide()
																			}
										});
		}
		
	});	
	
	/* animate top alert down if exists */
	if ($('.top-alert-container').length > 0) {
		$('.top-alert-container').each(function()
		{
			var height = $(this).outerHeight(true);
			$(this).css('margin-top',-height);
			
			$(this).click(function() {
				$(this).animate({'margin-top':-height}, 300);
			})
		})
		
		
		setTimeout(function() {
			$('.top-alert-container').first().animate({'margin-top': 0}, 300);
		}, 900);
	}
	
	
	
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
			
			if (($(e.target).parents('.dropdown-menu-options-container').length > 0 || 
				$(e.target).is('.dropdown-menu-options-container')) && 
				(typeof $(e.target).attr('change') == 'undefined')) {
					// Clicked on search bar for dropdown-menu
					return;
				}
			
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').removeClass('dropdown-menu-container-reverse');
			dropdowns.dropdownMenuDown.children('.dropdown-menu-selected').children('p').removeClass('dropdown-menu-container-reverse-text');

			dropdownMenu(dropdowns.dropdownMenuDown);
		}
		
		if (!$(e.target).is('.header-search-result') && !$(e.target).parents('#headerSearchForm').length > 0) {
			// Hide results container for search
			$('#header-search-results-container').hide();
			$('#header-search-results-container').html('');
		}
	});
	
	
	/* FIX FOR PADDING ISSUE WITH ALL MAC BROWSERS */
	if (navigator.userAgent.indexOf('Mac OS X') != -1) {
		
		$("p,a,input[type=text],input[type=submit],span,textarea,button").each(function()
		{
			if ($(this).is('#nav-notification-indicator') ||
				$(this).is('.calendar-day') ||
				$(this).is('.header-dropdown-option,.header-dropdown-option-cog') ||
				$(this).is('.cog-dropdown-account-text') ||
				$(this).is('.header-dropdown-option-cog') ||
				$(this).is('.member-find-game-container')) {
				// Do not give padding to notification indicator
				return;
			}
			var padding = parseInt($(this).css('padding-top'), 10);
			padding += 2;
			$(this).css('padding-top', padding + 'px');
		})
	}
	
	
		
})


/**
 * Ajax call to edit user's details
 * @params (attribs => obj with attrib => val)
 */
function editUser(attribs, callback)
{
	$.ajax({
		url: '/ajax/edit-user',
		type: 'POST',
		data: {attribs: attribs},
		success: function(data) {
			if (typeof callback != 'undefined') {
				callback();
			}
		}
	})
}

/**
 * Ajax call to get new notifications for user
 */
function getNewNotifications()
{
	$.ajax({
		url: '/ajax/get-new-notifications',
		type: 'POST',
		data: {timestamp: loggedIn},
		success: function(data) {
			var date = new Date();
			var year = date.getFullYear();
			var month = date.getMonth() + 1;
			var day = date.getDate();
			var hours = date.getHours();
			var minutes = date.getMinutes();
			var seconds = date.getSeconds();
			loggedIn = year + "-" + (month < 10 ? '0' + month : month) + "-" + (day < 10 ? '0' + day : day) + " " + hours + ":" + minutes + ":" + seconds;

			data = JSON.parse(data);
			
			populateNewNotifications(data);
		}
	})
}

/**
 * Ajax call to delete specific notification
 */
function deleteNotification(notificationLogID)
{

	$.ajax({
		url: '/ajax/delete-notification',
		type: 'POST',
		data: {notificationLogID: notificationLogID},
		success: function(data) {
		}
	})
	
}
		

/**
 * Ajax call to change team/group's name
 * @params(name	  => userid of new captain,
 *		   idType => "teamID" or "groupID",
 *		   typeID => actual teamID or groupID,
 *		   actingUserID => who did it)
 */
function changeTypeName(name, idType, typeID, actingUserID)
{
	var options = new Object();
	options.name = name;
	options.idType = idType;
	options.typeID = typeID;
	
	$.ajax({
		url: '/ajax/change-type-name',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			createNotification(idType, typeID, actingUserID, '', 'edit', getType(idType), 'name');
		}
	})
}

/**
 * Ajax call to change team/group's captain
 * @params(userIDs => userids of new captains (object),
 *		   idType => "teamID" or "groupID",
 *		   typeID => actual teamID or groupID)
 */
function changeCaptains(userIDs, idType, typeID) {
	var options = new Object();

	options.userIDs = userIDs;
	options.idType = idType;
	options.typeID = typeID;
	
	$.ajax({
		url: '/ajax/change-captain',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			var type = getType(idType);
			$.each(userIDs, function(index, value) {
				createNotification(idType, typeID, value, value, 'become', type, 'captain');
			})
		}
	})
}


/**
 * Ajax call to remove player from a team or group
 * @params(userID		  => id of user to remove,
 *		   idType		  => "teamID" or "groupID",
 *		   typeID		  => actual teamID or groupID)
 */
function removeUserFromType(userID, idType, typeID) {
	
	var options = new Object();
	options.userID = userID;
	options.idType = idType;
	options.typeID = typeID;
	
	$.ajax({
		url: '/ajax/remove-user-from-type',
		type: 'POST',
		data: {options: options},
		success: function(data) {
		}
	})
}

/**
 * Ajax call to add user to a team (NOT BEING USED YET)
 * @params(teamID => id of team,
 *		   userID => userID)
 */
function addUserToTeam(teamID, userID)
{
	var options = new Object();
	options.teamID = teamID;
	options.userID = userID;
	
	$.ajax({
		url: '/ajax/add-user-to-team',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			var typeID = teamID;
			var idType = 'teamID';
			var action = 'join';
			var type   = 'team';
			var details;
			createNotification(idType, typeID, userID, '', action, type, details);
			reloadPage();
		}
	})
}



/**
 * Ajax call to add user to a game
 * @params(typeID => id of game,
 *		   idType => "teamGameID" or "gameID"
 *		   userID => userID)
 */
function addUserToGame(idType, typeID, userID, confirmed)
{
	var options = new Object();
	options.idType = idType;
	options.typeID = typeID;
	options.userID = userID;
	options.confirmed = (typeof confirmed == 'undefined' ? '' : confirmed);
	
	$.ajax({
		url: '/ajax/add-user-to-game',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			
			/*
			var action = 'join';
			var type   = 'game';
			var details;
			createNotification(idType, typeID, userID, '', action, type, details);
			*/
			reloadPage();
		}
	})
}

/**
 * Ajax call to confirm or not confirm user attendance to team game or pickup game
 * @params(inOrOut 		  => "in" or "out",
 *		   type			  => "teamGame" or "pickupGame",
 *		   id			  => id or teamGame or pickupGame
 *		   insertOrUpdate => "insert" or id of already existing row
 *		   teamID		  => teamID or blank)
 */
function confirmUserToGame(inOrOut, type, id, insertOrUpdate, teamID)
{

	$.ajax({
		url: '/ajax/confirm-user',
		type: 'POST',
		data: {inOrOut: inOrOut,
			   type: type, 
			   id: id},
		success: function(data) {

			reloadPage();
		}
	})
}
	

/**
 * Ajax call to confirm (eg add as friends) or deny (delete) specific notification
 * @params(notificationLogID => id of parent notificationLogID from db,
 *		   confirmOrDeny	 => "confirm" or "deny",
 *		   type				 => type (friend, game, team, group etc) retrieved from db to determine what table to add to,
 *		   action			 => action (check, invite, etc),
 *		   optionalID		 => ID for game or group if issued, but blank if not)
 */
function notificationConfirmDeny(notificationLogID, confirmOrDeny, type, action, optionalID)
{
	var options = new Object();
	options.notificationLogID = notificationLogID;
	options.type = type;
	options.action = action;
	options.confirmOrDeny = confirmOrDeny;
	options.optionalID = optionalID;
	

	$.ajax({
		url: '/ajax/notification-action',
		type: 'POST',
		data: {options: options},
		success: function(data) {

			showConfirmationAlert(capitalize(confirmOrDeny) + ' processed');	
			reloadPage();
		}
	})
}

/**
 * Ajax call to confirm (eg add as friends) or deny (delete) specific notification
 * @params(notificationLogID => id of parent notificationLogID from db,
 *		   type				 => type (friend, game, team, group etc) retrieved from db to determine what table to add to
 *		   url		 => where to go after finish call)
 */
function notificationJoin(notificationLogID, type, url)
{
	var options = new Object();
	options.notificationLogID = notificationLogID;
	options.type = type;


	$.ajax({
		url: '/ajax/notification-action',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			if (data) {
				// Profile pic not found, failure
				window.location = data;
				return;
			} else {
				showConfirmationAlert('You have been added to the roster');
			}
			setTimeout(function() {
				window.location = url;
			}, 400);
		}
	})
}


/**
 * Create notification based on given parameters
 * @params (idType => what is typeID referring to? (teamID, groupID, userID, gameID, etc),
 *			typeID => id of group, team, user, etc that is being acted upon,
 *			actingUserID => id of user who acted,
 *			receivingUserID => id of user who is receiving action,
 *			action => action being performed (from notifications table),
 *			type => type being acted upon (from notifications table),
 *			details => details of notification (from notifications table))
 */
function createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details)
{
	var options = new Object();
	options.idType = idType;
	options.typeID = typeID;
	options.actingUserID = actingUserID;
	options.receivingUserID = (typeof receivingUserID == 'undefined' ? '' : receivingUserID);
	options.action = (typeof action == 'undefined' ? '' : action);
	options.type = (typeof type == 'undefined' ? '' : type);
	options.details = (typeof details == 'undefined' ? '' : details);
	
	$.ajax({
		url: '/ajax/create-notification',
		type: 'POST',
		data: {options: options},
		success: function(data) {
		}
	})
}

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
 * @params (search => search term to look for
 *			limit  => array with names of types of things to look for (eg "users", "teams", etc),
 *			param => optional param to be sent to callback)
 */
function searchDatabase(searchTerm, callback, limit, param)
{

	$.ajax({
		url: '/ajax/search-db',
		type: 'POST',
		data: {search: searchTerm,
			   limit: limit},
		success: function(data) {
			data = JSON.parse(data);
			if (typeof param != 'undefined') {
				callback(data, param);
			} else {
				callback(data);
			}
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
 * create sliders
 */
function buildSliders(ele, callback, options)
{
	/* slider */
	var width = $('.signup-skill-slider').width();
	
	if (typeof options == 'undefined') {
		options = {defaultValue: 2,
				   minValue: 0,
				   maxValue: 6,
				   sliderHeight: 4,
				   sliderWidth: width,
				   style: 'style1', 
				   animate: false, 
				   ticks: false, 
				   labels: false, 
				   trackerHeight: 20, 
				   trackerWidth: 19 }
	}
	
	options.callback = callback;
	
	// strackbar requires element to not be hidden (offset().left)

	ele.strackbar(options)

}


/** 
 * test dropdown menu for animation up or down
 * @params(dropdownEle => outer dropdown container that is being acted upon)
 */
function dropdownMenu(dropdownEle)
{

		var dropdown = dropdownEle;
		var hiddenID = dropdownEle.attr('dropdown-id');
		var selected = dropdown.children('.dropdown-menu-selected');
		var options  = dropdown.next('#' + hiddenID);
		
		if (dropdowns.dropdownMenuDown) {
			
			if (dropdowns.dropdownMenuDown.attr('id') == dropdown.attr('id')) {
				// Dropdown is already down
				
				options.animate({opacity: 0}, {duration: 200, complete: function() {
																			options.hide();

																			}
											  }
								);
				
				if (dropdowns.dropdownMenuDown.parents('#profile-buttons-container').length > 0) {
					// Is profile page, handle bug where container must be taller than the dropdowns when clicked, smaller when unclicked
					dropdowns.dropdownMenuDown.parents('#profile-buttons-container').animate({height: '10em'}, 300);
				}
							
				dropdowns.dropdownMenuDown = false;
				return;
			}
		}
		
		
		alignDropdown(options, selected, 'right');
		
		var top = selected.position().top + selected.innerHeight()
		options.css({top: top,
					 display: 'block'})
			   .animate({opacity: 1}, 200);
			      
		
		dropdowns.dropdownMenuDown = dropdownEle;

}

/**
 * callback function from getNewNotifications
 */
function populateNewNotifications(data)
{
	if (data[0] < 1) {
		// This is the number of returned new notifications, no new notifications
		return;
	} else {
		var currentNum = $('#nav-notification-indicator').text();
		currentNum = (currentNum == '' ? 0 : parseInt(currentNum, 10));
		var newNum = currentNum + data[0];
		
		$('#nav-notification-indicator').html(newNum);
		$('#nav-notification-indicator').parent().show();
		
		notificationTitle = '(' + newNum + ') New Notification' + (newNum > 1 ? 's' : '');
		//document.getElementsByTagName('title')[0].innerHTML = '(' + newNum + ') ' + pageTitle;
		
		clearInterval(titleInterval);
		setTitle(notificationTitle);
		
		// Interval to alternate page title from "new notification" to regular page title
		titleInterval = setInterval(function()
		{
			if (getTitle() == pageTitle) {
				// Is regular title, show notification title
				setTitle(notificationTitle);
			} else {
				setTitle(pageTitle);
			}
		}, 5000);
				
		
		$('#notifications-container').prepend(data[1]);
		
	}
	
}
		
		

/** 
 * callback function from smart slider plugin to handle slider's values
 * @params(sliderEle => slider element that is being acted upon,
 		   value	 => numeric value returned from slider plugin)
 */
function populateSliderText(sliderEle, value)
{
	
	var containerEle   = sliderEle.parents('.slider-container');
	var valueEle 	   = containerEle.find('.slider-text-value');
	var descriptionEle = containerEle.find('.slider-text-description');
	var valueArray;
	
	if (sliderEle.is('.slider-sportsmanship')) {
		// Is sportsmanship bar, get sportsmanship values
		valueArray = sliderSportsmanshipValues;
	} else {
		valueArray = sliderSkillValues;
	}
	
	var textValue	   = valueArray[value]['level'];
	
	valueEle.html(textValue);
	
	if (descriptionEle.length > 0) {
		// Description ele exists, populate
		var descriptionValue = valueArray[value]['description'];
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

	if (!results) {
		// No results
		
		output += "<div class='header-search-result dark-back medium default'>No results found</div>";
	} else {
		// Results found
	
		for (i = 0; i < results.length; i++) {
			
			if (i >= 4) {
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
 * populate invite button's search results
 * @params (results => returned results from ajax)
 */
function populateSearchResultsInviteButton(results) 
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
	
	$('#dropdown-menu-option-results-invite').html(output);
	
	if ($('#inviteSearchBar').is(':focus') && $('#inviteSearchBar').val().length >= 3) {
		// Search bar has focus and val is greater than 2 (protect against accidently overfire due to ajax delay
		$('#dropdown-menu-option-results-invite').show();
	}
	
	
	var searchBar = $('#inviteSearchBar');
	var searchVal = searchBar.val();
	$('.invite-search-result').highlight(searchVal);
	
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
	
	ele.show();
	var height = ele.outerHeight();
	if (!down) {
		ele.hide();
	}
	
	if (down) {	
		// Hidden element is down, animate it up
		ele.stop().animate({marginTop: -height}, {duration:300, complete: function() {
																			$(this).hide()
																			}
		});
	} else {
		// Hidden element is up, animate it down
		ele.css({marginTop: -height,
				 opacity: 0})
		
		if (!fadeIn) {
			// Do not fade in
			
			ele.stop().css({'opacity': 1,
							'display': 'block'})
					  .animate({marginTop: 0}, 400);
			
		} else {
			// Fade in after animation is complete
			ele.stop().css({'display': 'block'})
					  .animate({marginTop: 0}, {duration:300, complete: function() {
																			$(this).animate({opacity: 1}, {duration:300})
																		}
			});
		}
		
		ele.addClass('animate-hidden-selected');
	}
	

}

/**
 * fade in alert box
 */
function showAlert(alertEle, opacity)
{
	if ($('.alert-black-back').css('display') != 'none') {
		return;
	}
	var finalOpacity = (typeof opacity != 'undefined' ? opacity : '.85')
	displayToBlockHidden(alertEle);
	displayToBlockHidden($('.alert-black-back'));
	
	alertEle.animate({'opacity': 1}, 300);
	$('.alert-black-back').first().animate({'opacity':finalOpacity},300);
}

/**
 * fade out alert
 */
function hideAlerts()
{
	$('.alert-container').hide();
	
	$('.alert').animate({'opacity':0},{duration: 200, complete: function() {
																	$('.alert').hide()
																			   .css('opacity',1);
																	$('.alert-black-back').css('opacity',.85);
																}
	})
}

/**
 * disable close alert onclick
 */
function disableCloseAlerts()
{
	$('.alert-black-back,.alert-x').unbind('click.default');
	
}

function enableCloseAlerts()
{
	$('.alert-black-back,.alert-x').bind('click.default',function()
	{
		hideAlerts();
		
	})
}

function displayToBlockHidden(ele)
{
	ele.css({'opacity': 0,
			 'display': 'block'})
			 
	return ele;
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
	var overlayEle = inputEle.siblings('.input-overlay');

	var inputVal = $.trim(inputEle.val());
	if (inputVal !== '') {
		//inputEle.removeClass('input-fail'); // remove if problems exist in signup.js
		overlayEle.hide();
	} else {
		overlayEle.show();
		if (focusIn || inputEle.is(':focus')) {
			overlayEle.animate({'opacity':'.5'},200);
		} else {
			overlayEle.animate({'opacity':'1'},200);
		}
	}
}


/**
 * Animate nav dropdown up or down
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
 *			alignment => how to align (center, right, left),
 *			clickableDropdown => is this a clickable dropdown? boolean)
 */
 function alignDropdown(moveEle, alignEle, alignment, clickableDropdown)
 {

	 var alignedWidth = alignEle.outerWidth(true);
	 
	 
	 var alignedPos = alignEle.offset().left;
	 if (moveEle.is('.dropdown-menu-hidden-container') || moveEle.css('position') !== 'absolute') {
		 alignedPos = alignEle.position().left;
		
	 }
	
	 var windowWidth  = $(window).width();
	 var bodyWidth    = $('.centered-body').width();
	  
	 var bodyWindDiff = (bodyWidth > windowWidth) ? 0 : (windowWidth - bodyWidth)/2;

	 var left = alignedPos;
	
	 if (alignment == 'right') {
		 var widthDiff = moveEle.outerWidth(true) - alignedWidth;
		 left -= widthDiff;
	 } else if (alignment == 'center') {
		 left   = alignEle.offset().left;
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
				
				$(this).css({position: 'static',
							 top: 'auto',
							 bottom: 'auto'})
			} else {
				
				var width = $(this).width();
				var height = $(this).height();
				
				if ($(this).parents('.body-column-narrow').length > 0 &&
					(scrollTop + $('.body-column-narrow').children().height() + 10) > ($('#body-white-back').height() + $('#body-white-back').offset().top)) {
						// left column is below the white background, prevent from going further
					
						$(this).css({position: 'absolute',
									 width	 : width,
									 height  : 'auto',
									 bottom	 : 10,
									 top	 : 'auto'});
				} else {
				
					$(this).css({position: 'fixed',
								 width	 : width,
								 height  : 'auto',
								 top	 : 10,
								 bottom	 : 'auto'});
				}
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
	
	$('#tooltip').find('#tooltip-body').html(value);
	
	var tooltipWidth = $('#tooltip').innerWidth();
	var endOfBody    = $('#body-white-back').innerWidth() + $('#body-white-back').offset().left;
	
	if ((tooltipWidth + left) > endOfBody) {
		$('.tooltip-tip').css('float','right');
		left = left - (tooltipWidth - ele.innerWidth());
	} else {
		$('.tooltip-tip').css('float','left');
	}
	
	if (tooltipEle.css('text-align') == 'center') {
			// Is using width-100 and center classes...align tooltip to middle of element
			left += tooltipEle.width()/2 - 30
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
 *			zoom => zoom (higher is more zoomed)),
 *			option => object of options to add to the map
 *			callback => callback function
 */
function initializeMap(lat, lon, zoom, option, callback) 
{	
        var mapOptions = {
          //center: new google.maps.LatLng(lat, lon),
          zoom: zoom,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
		  mapTypeControl: false // Disable ability to change to satellite etc
		  
        };
        gmap = new google.maps.Map(document.getElementById("gmap"), mapOptions);
			
		if (typeof option != 'undefined') {
			gmap.setOptions(option);
		}
		
		if (userLocation.length > 0) {
			// User location is set, center on it initially
			lat = userLocation[0];
			lon = userLocation[1];
		}
		
		var latLon = new google.maps.LatLng(lat, lon);
		gmap.setCenter(latLon);
		
		// Remove "Report a map error" from bottom right of google map
		var styleOptions = {
			name: "Dummy Style"
		};
	
		 var MAP_STYLE = [
			{
				featureType: "road",
				elementType: "all",
				stylers: [
					{ visibility: "on" }
				]
			}
		];
		var mapType = new google.maps.StyledMapType(MAP_STYLE, styleOptions);
		gmap.mapTypes.set("Dummy Style", mapType);
		gmap.setMapTypeId("Dummy Style");
		
		
		callback();
		
}
/*
function setZoom()
{
		// Zoom constraint
	
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
}
*/



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

/**
 * populate confirmation alert (are you sure you want to "")
 */
function populateConfirmActionAlert(str, postContent)
{
	$('#confirm-action-text').text(str);

	if (typeof postContent != 'undefined') {
		// add post content
		$('#confirm-action-postContent').html(postContent);
	}
	
	showAlert($('#confirm-action-alert-container'));
}


/**
 * set and show confirm-alert value with str
 */
function showConfirmationAlert(str) 
{
	$('#confirm-alert').html(str)
					   .css({display: 'block',
							 opacity: 0})
					   .stop().animate({opacity: 1}, {duration: 300, complete: function() {
						   														confirmAlertTimer = setTimeout(function() {
																										animateHidden($('#confirm-alert'));
																											
																										}, 1500)
					  															 }
					   })
}
	
	
/**
 * change input background color based on validity
 * @params(ele => inputEle to change background of,
 		   isValid => should it be green (ok?) (boolean))
 */
function changeInputBackground(ele, isValid)
{
	
	if (!(ele.val().length > 0) &&
		typeof ele.attr('required') == 'undefined') {
			return false;
	}
	
	if (!isValid && (typeof ele.attr('required') != 'undefined')) {
		// Failed validity test
		ele.removeClass('input-success').addClass('input-fail');

	} else {
		// Correct input
		ele.removeClass('input-fail').addClass('input-success');
	}

}					   														
							 
	
/**
 * animate opacity down and then hide ele
 */
function animateHidden(ele)
{
	ele.stop().animate({opacity: 0},{duration: 300, complete: function() {
														   ele.hide();
														}
				})
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


/**
 * get type of page (team, group, game) from idType (teamID, gameID)
 */
function getType(idType)
{
	return idType.replace(/ID/, '');
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
		
		if (rgb[i] < 0) {
			// Do not allow negative numbers
			rgb[i] = 0;
		}
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

function createMarker(latLng, map)
{

	var marker = new google.maps.Marker({
						position: latLng,
						map: map,
						icon: '/images/global/gmap/markers/green.png',
						shadow: {
									url: 'https://maps.gstatic.com/mapfiles/ms2/micons/msmarker.shadow.png',
									size: new google.maps.Size(59, 32),
									origin: new google.maps.Point(0,0),
									anchor: new google.maps.Point(15, 34)
								},
					
				 })
	return marker;
}


function clearMarkers() {
    for(var i=0; i < markers.length; i++){
		
        markers[i].setMap(null);
    }
	
    markers = new Array();

};
	

/**
 * reload page after settimeout delay to allow ajax to complete
 */
function reloadPage()
{
	setTimeout(function() {
				location.reload();
	}, 400);
}

/**
 * set page title
 */
function setTitle(title)
{
	document.getElementsByTagName('title')[0].innerHTML = title;
}

/**
 * get page title
 */
function getTitle(title)
{
	return document.getElementsByTagName('title')[0].innerHTML;
}
	

