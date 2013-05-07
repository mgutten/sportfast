// rating.js
var ratingClicked;


$(function()
{
	
	$('.rating-star-clickable').mousemove(function(e)
	{
		if (ratingClicked) {
			return false;
		}

		setRatingWidth(e, $(this))
		
	})
	.mouseout(function()
	{
		if (!ratingClicked) {
			$(this).children('.rating-star-back').css('width', 0);
		}
	})
	.click(function(e)
	{
		ratingClicked = true;
		
		var inputEle = $('#rating-hidden');
		
		
		var width = setRatingWidth(e, $(this));
		
		// out of 5 stars
		inputEle.val(width/$(this).width() * 5); 
	})
		
	$('#user-rating').submit(function()
	{
		if ($('#rating-hidden').val() == '') {
			// Did not input a rating
			showConfirmationAlert('Please click the stars to rate this park');
			return false;
		}
	})
	
	
	$('.flag-incorrect').click(function()
	{
		flagRemoval($(this).attr('userRatingID'));
	})
	
	
})

/**
 * ajax flag rating for removal
 */
function flagRemoval(userRatingID)
{
	$.ajax({
		url: '/ajax/flag-removal',
		type: 'POST',
		data: {userRatingID: userRatingID},
		success: function() {
			showConfirmationAlert('Rating will be reviewed shortly');
		}
	})
}

/**
 * set width of rating back
 */
function setRatingWidth(event, ele)
{
	var bar = ele.children('.rating-star-back');
	var parentOffset = ele.parent().offset(); 
	var eleWidth = ele.width()
	
	var width = event.pageX - parentOffset.left;
	
	width = roundWidth(width, eleWidth);
	
	bar.css('width', width);
	
	return width;
}


/**
 * round width of rating star to halves
 */
function roundWidth(width, parentWidth) 
{
	var percentage = width/parentWidth;
	var roundedPercentage = (Math.round(percentage * 10))/10;
	
	return roundedPercentage * parentWidth;
	
}
	

