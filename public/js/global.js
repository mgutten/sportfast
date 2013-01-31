// global.js
var mouseoverDropdown = false;

$(function()
{
	/* align each dropdown with appropriate navigation */
	$('.dropdown-back-outer').each(function()
	{
		var parent = $(this).parent();
		var top = parent.offset().top + parent.height();
		var left = parent.offset().left;
		
		if (parent.css('float') == 'right') {
			var parentWidth = parent.width();
			var dropdownWidth = $(this).width();
			var widthDiff = parentWidth - dropdownWidth;
			left = left + widthDiff;
		}
		
		$(this).css({top : top,
					 left: left});
	})
			
						 
	/* dropdown animation onhover */
	$('.nav-dropdown').hover(function()
	{
		mouseoverDropdown = true;
		var outerEle = $(this).children('.dropdown-back-outer');
		var innerEle = outerEle.children();
		outerEle.show()
			    .css('z-index', 100)
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
		fadeOutInputOverlay($(this), true)
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
	


		
		
})

/**
* function to cause fade effect for series of imgs
* @params
*/
function fadeImgToNext()
{
	var currentEle = $('.fade-current');
	var nextEle = $('.fade-next');
	var duration = 2000;
	var eleClass = $.trim(currentEle.attr('class').replace('fade-current',''));
	var nextNextEle = nextEle.next('.' + eleClass);
	
	if (nextNextEle.length == 0) {
		nextNextEle = $('.' + eleClass).first();
	}
	
	currentEle.animate({'opacity': 0}, {duration: duration,
										complete: function()
												{
													$(this).removeClass('fade-current')
														   .css({'opacity': 1});
													nextEle.removeClass('fade-next')
														   .addClass('fade-current');
													nextNextEle.addClass('fade-next');
												}
	});
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

