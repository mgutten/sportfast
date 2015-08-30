// js for editing game details
var calendarDay;
var pageType = 'game';

$(function()
{
	$('#calendar-' + calendarDay).trigger('click');
	
	
	$('#edit-game-change-captain').click(function()
	{
		showAlert($('#change-captain-alert-container'));
	})
	

})
		