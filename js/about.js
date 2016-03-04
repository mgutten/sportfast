// JavaScript Document
var faqTimeout;
$(function()
{
	/* faq */
	$('.faq-question').click(function()
	{
		if ($('.faq-answer-selected').length > 0) {
			animateNotShow($('.faq-answer-selected'), true);
			
			var answerEle = $(this).siblings('div').children()
			if (answerEle.is('.faq-answer-selected')) {
				answerEle.removeClass('faq-answer-selected');
				return false;
			}
			var ele = $(this);
			
			clearTimeout(faqTimeout);
			faqTimeout = setTimeout(function() {
					selectQuestion(ele);
			}, 400)
				
		} else {
			selectQuestion($(this));
		}
		
		
	})
})

/**
 * question is clicked on faq page
 */
function selectQuestion(questionEle)
{
	var answerEle = questionEle.siblings('div').children();
	
	$('.faq-answer-selected').removeClass('faq-answer-selected');
		
	answerEle.addClass('faq-answer-selected');
		
	animateNotShow(answerEle, false);
}