// homepage.js

$(function()
{
	/* offset img container to correct position below header bar */
	var top = $('#header-bar-tall').offset().top + $('#header-bar-tall').height();
	$('#homepage-large-img-container').css('top',top);
	
	/* loop through each large-img element and lay on top of each other for fade effect */
	$('.homepage-large-img').each(function()
	{
		var num = $('.homepage-large-img').index($(this));
		if (num == 0) {
			return;
		}
		var top = -num * $(this).height();
		$(this).css('top',top);
	})
	
	setInterval(function()
				{
					fadeImgToNext();
				}, 4000)
				
	
})