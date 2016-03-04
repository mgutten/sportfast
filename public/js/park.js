// park.js

$(function()
{
	$('.park-details-tab').click(function()
	{
		
		if ($(this).is('.park-details-tab-selected')) {
			return;
		}
		
		var classy = 'light-back park-details-tab-selected rounded-corners';
		
		$('.park-details-tab-selected').removeClass(classy);
		$(this).addClass(classy);
		
		
		var id = '#park-' + $(this).text().toLowerCase();
		
		$('.park-details-outer-container').hide(); 
		$(id).show();
	})
	
	
})