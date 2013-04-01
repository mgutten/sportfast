// user.js

$(function()
{
	$('.user-sport-tab-back').click(function()
	{
		var selectedEle = $('.user-sport-selected');
		
		// Hide currently selected tab, turn back to normal
		selectedEle.children('.user-sport-tab-selected-container').hide();
		selectedEle.children('.user-sport-tab-back').show();
		selectedEle.removeClass('user-sport-selected');
		
		// Show newly selected tab
		var index = $(this).parent().index();
		$(this).siblings('.user-sport-tab-selected-container').show();
		$(this).hide();
		$(this).parent().addClass('user-sport-selected');
		
		// Show corresponding sports container
		$('.user-sport-container-selected').hide()
										   .removeClass('user-sport-container-selected');
		$('.user-sport-container:eq(' + index + ')').show()
													.addClass('user-sport-container-selected');
		
	})
	
	/* show rating percent on mouseover */
	$(document).on('mouseenter','.user-sport-rating-other-outer', function()
 		{
			$(this).children().children('.user-sport-rating-percent').show();
		}
	)
	.on('mouseleave','.user-sport-rating-other-outer', function()
		{
			$(this).children().children('.user-sport-rating-percent').hide();
		}
	)
	
	
})