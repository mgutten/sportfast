// global.js
var mouseoverDropdown = false; /* fix issue when input is focus, then not focus and mouse is not over */
var fadeRunning = false;
var tooltipTimer;
var tooltipEle = null;

$(function()
{
	
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
		$(this).stop().animate({backgroundColor: '#C6C6C6'},200);
	},
	function()
	{
		$(this).stop().animate({backgroundColor: '#E0E0E0'},200);
	})
	
	
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
		
			$('#tooltip').stop().animate({opacity:0},{duration:100, complete: function() {
																				$(this).hide()
			}});
		
	});
	
		
})


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
	var top   = ele.offset().top + ele.innerHeight();
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
	

