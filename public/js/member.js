/* member.js */

$(function() {
	
	$('.dropdown-menu-option-container').click(function(e)
	{
		// Option has been clicked
		e.stopPropagation();
		var childText = $(this).children('p');
		var textValue = childText.text();
		var childImg  = $(this).children('img');
		
		childText.toggleClass('green');
		
		childImg.toggleClass('green-back');
		
	})

})
