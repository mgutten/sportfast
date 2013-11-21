// JavaScript Document
$(function()
{
	$('.pagination-page').bind('click.change', function()
	{
		if ($(this).is('.selected')) {
			return false;
		}
		
		$('.pagination-page').removeClass('selected');
		$(this).addClass('selected');
		
		var index = $(this).text() - 1;
		var parentEle = $(this).parents('.pagination-outer-container');
		
		parentEle.children('.pagination-inner-container').hide();
		parentEle.children('.pagination-inner-container:eq(' + index + ')').show();
		
	});
	
})