// homepage.js
var fadeInterval;
var buttonPosition = new Array;
buttonPosition[0] = {top: '336px',
					 left: '637px'};
buttonPosition[1] = {top: '224px',
					 left: '575px'};
buttonPosition[2] = {top: '208px',
					 left: '16px'};
buttonPosition[3] = {top: '248px',
					 left: '872px'};
									 					 

$(function()
{		
	setFadeInterval(5500)

								
	$('.homepage-large-img-dot').click(function()
	{
		var nextNum = $(this).index();
		var nextEle = $('.homepage-large-img:eq(' + nextNum + ')')
		
		if (nextEle.is('.fade-current') || fadeRunning) {
			return;
		}
		
		changeButtonPosition(nextNum)
		changeImgIndicator($(this));
		clearTimeout(fadeInterval);
		$('.fade-next').removeClass('fade-next');
		setNextImgFade(nextEle);
		fadeImgToNext();
		setFadeInterval(5500);
		
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
									var index = dotEle.index('.homepage-large-img-dot');
									changeButtonPosition(index);
									
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

/**
 * change button position based on picture showing
 */
function changeButtonPosition(index)
{
	$('#homepage-learn').css({top:  buttonPosition[index]['top'],
							  left: buttonPosition[index]['left']});
}
	


