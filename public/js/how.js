// JavaScript Document
$(function()
{
	$('.how-sport-icon').hover(function()
	{
		var sport = $(this).attr('sport');
		
		$('#how-sport').text(sport);
		
		$(this).stop().animate({'background-color': '#444'}, 300);
		
	}, function()
	{
		$(this).stop().animate({'background-color': '#8d8d8d'}, 300);
	})
})