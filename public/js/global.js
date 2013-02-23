// global.js
var mouseoverDropdown = false; /* fix issue when input is focus, then not focus and mouse is not over */
var fadeRunning = false;
var tooltipTimer;
var tooltipEle = null;
var sliderSkillValues = [];
sliderSkillValues[0] = {level:'Beginner',
						description: 'I have rarely (if ever) played.'};
sliderSkillValues[1] = {level:'Decent',
						description: 'I play infrequently, or have difficulty keeping up when I do play.'};
sliderSkillValues[2] = {level:'Good',
						description: 'I am an average player.  Nothing fancy, just good fundamentals.'};
sliderSkillValues[3] = {level:'Skilled',
						description: 'I am skilled.  I am better than the average player.'};
sliderSkillValues[4] = {level:'Talented',
						description: 'I am very skilled.  I am typically the best player in the game.'};
sliderSkillValues[5] = {level:'Unstoppable',
						description: 'I played (or should play) on a professional level.'};
						
var mouseoverColor;


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
	})( jQuery );
	
	/* jquery plugin to verify based on parameters passed in */
	(function($) {
	  $.fn.isValid = function(options) {
		  // Create some defaults, extending them with any options that were provided
			var settings = $.extend( {
			  'maxLength'     : 500,
			  'minLength'	  : 0,
			  'regex'		  : /.*/g
			}, options);
			
			var value 		     = this.val();
			var regexPatterns    = new Array();
			regexPatterns['num'] = /\d+/g;			

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
			if (!settings.regex.test(value)) {
				// Did not pass regex test
				return false;
			}
			
			return true;
			
	
	  };
	})( jQuery );
	
	
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
	
	/* align each dropdown with appropriate navigation */
	$('.dropdown-back-outer').each(function()
	{
		alignDropdownContainer($(this));
	})
	
	$(window).resize(function()
	{
			$('.dropdown-back-outer').each(function()
			{
				alignDropdownContainer($(this));
			})
	})
	
	
	/* change background color of narrow-column onhover */
	$('.narrow-column-header').hover(function()
	{
		mouseoverColor  = $(this).css('background-color');
		var darkerColor = getDarkerColor(mouseoverColor);
		$(this).stop().animate({backgroundColor: darkerColor},200);
	},
	function()
	{
		$(this).stop().animate({backgroundColor: mouseoverColor},200);
	})
		
	
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
			
						 
	/* dropdown animation onhover */
	$('.nav-dropdown').hover(function()
	{
		mouseoverDropdown = true;
		var outerEle = $(this).children('.dropdown-back-outer');
		var innerEle = outerEle.children();
		outerEle.show()
			    .css('z-index', 100);
		innerEle.stop().animate({marginTop : 0}, 300);
		
	},
	function()
	{
		mouseoverDropdown = false;
		var outerEle = $(this).children('.dropdown-back-outer');
		var innerEle = outerEle.children();
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
	
	
	/* test all elements for tooltip onhover */
	$('*').hover(function()
	{

		if($(this).parents('.input-container').attr('tooltip')) {
			return;
		}
		
		
		if ($(this).attr('tooltip')) {
			var ele = $(this);
			startTooltipTimer(ele);
		}
	}, function()
	{
		if(tooltipEle.is('.input-container')) {
			return;
		}
		
		endTooltipTimer();
		
			$('#tooltip').stop().animate({opacity:0},{duration:50, complete: function() {
																				$(this).hide()
														}}
										);
		
	});	
		
})


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
	/*
	var innerEle = ele.children();
	var height   = innerEle.outerHeight();
	
	if (ele.height() > 0) {
		ele.stop().animate({height: 0},400);
		return;
	} 
	
	ele.stop().animate({height: height},400);
	*/
	
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
* align dropdown containers with corresponding nav button
* @params (ele => dropdown-back-outer element)
*/
function alignDropdownContainer(ele)
{
		var parent = ele.parent();
		//var top  = parent.offset().top + parent.height();
		var windowWidth = $(window).width();
		var bodyWidth   = $('.centered-body').width();
		var top    = $('.header-bar').height();
		var left   = parseInt(parent.offset().left,10) - ((windowWidth - bodyWidth)/2);
		
		if (parent.css('float') == 'right') {
			var parentWidth = parent.width();
			var dropdownWidth = ele.width();
			var widthDiff = parentWidth - dropdownWidth;
			left = left + widthDiff;
		}
		
		ele.css({top : top,
				 left: left});
}

/**
* align absolutely positioned elements with holder
* @params (ele => .absolute element)
*/
function alignAbsolute(ele)
{
	var holder = $('#' + ele.attr('holder'));
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
	

