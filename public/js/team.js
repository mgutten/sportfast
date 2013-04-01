// Team profile page js
$(function()
{
	
	/* fade in user description on mouseover */
	$(document).on('mouseenter','.profile-player-overlay-container',function() 
	{
		$(this).stop().animate({opacity: 1}, 300);
	})
	.on('mouseleave','.profile-player-overlay-container',function() 
	{
		$(this).stop().animate({opacity: 0}, 300);
	})
	
	
	
	
	
})