// homepage.js
var fadeInterval;

$(function()
{		
	setFadeInterval(5000);
								
	$('.homepage-large-img-dot').click(function()
	{
		var nextNum = $(this).index();
		var nextEle = $('.homepage-large-img:eq(' + nextNum + ')')
		
		if (nextEle.is('.fade-current') || fadeRunning) {
			return;
		}
		
		changeImgIndicator($(this));
		clearTimeout(fadeInterval);
		$('.fade-next').removeClass('fade-next');
		setNextImgFade(nextEle);
		fadeImgToNext();
		setFadeInterval(4000);
		
	})
		
				
	
})


/**
* set fade interval
* params
*/
function setFadeInterval(duration)
{
	fadeInterval = setInterval(function()
								{
									var dotEle = $('.homepage-large-img-dot-selected').next('.homepage-large-img-dot');
									if (dotEle.length < 1) {
										dotEle = $('.homepage-large-img-dot').first();
									}
									changeImgIndicator(dotEle);
									fadeImgToNext();
								}, duration)
}

/**
* change the indicator dot for large img
* @params (dotEle => dot ele that is to be selected)
*/
function changeImgIndicator(dotEle)
{
		$('.homepage-large-img-dot-selected').removeClass('homepage-large-img-dot-selected');
		dotEle.addClass('homepage-large-img-dot-selected');
}
	


