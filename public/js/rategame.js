// rate users/park from previous game

$(function()
{
	
	if ($('#rateGame-alert-container').length > 0) {
		// Rate user/park from last game is available, show it
		
		$('#rateGame-alert-container,.rating-section-container').show();
		$('.slider-container').each(function()
		{
			
			var width = $(this).width();
			var callback;
			var options = {defaultValue: 2,
						   minValue: 0,
						   maxValue: 6,
						   sliderHeight: 4,
						   sliderWidth: width,
						   style: 'style1', 
						   animate: false, 
						   ticks: false, 
						   labels: false, 
						   trackerHeight: 20, 
						   trackerWidth: 19 }
						   
			if ($(this).is('#slider-container-skill-rating')) {
				// Is rating for skill
				callback = populateSkillInput;
			} else if ($(this).is('#slider-container-sportsmanship-rating')) {
				// Is rating for skill
				callback = populateSportsmanshipInput;
				options.maxValue = 3;
				options.defaultValue = 1;
			}
			
			buildSliders($(this).find('.signup-skill-slider'),callback,options);
		})
		$('#rateGame-alert-container,.rating-section-container').hide();
		$('.rating-main-container').each(function()
		{
			$(this).children('.rating-section-container').first().show();
		})

		showAlert($('#rateGame-alert-container'));
		
		$('#rateGame-alert-container').find('.button').click(function()
		{
			$(this).addClass('schedule-button-selected inner-shadow');
		})
		
		$('.rating-remember-yes').click(function()
		{
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index += 2;
			
			$(this).parents('#rateGame-alert-container').find('.alert-body-container:eq(' + index + ')').show();
		})
		
		$('.rating-remember-no').click(function()
		{
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index += 2;
			
			var parentEle = $(this).parents('.rating-section-container');
			
			parentEle.siblings('.rating-section-container').hide();
			
			$(this).parents('#rateGame-alert-container').find('.alert-body-container:eq(' + index + ')').show();
			
		})
		
		$('.rating-attendance').find('.button').click(function()
		{
			$(this).siblings('.button').removeClass('inner-shadow schedule-button-selected');
		})
		
		$('.rating-animate-trigger').click(function()
		{
			var curEle  = $(this).parents('.rating-section-container')
			var nextEle = curEle.next('.rating-section-container');
			
			curEle.css('z-index', 2);
			nextEle.css('z-index', 1);
			
			if (nextEle.css('display') == 'none') {
				// Still hidden, animate down
				animateNotShow(nextEle, false, true);
			}
		})
		
		/* submit */
		$('.rating-submit').bind('click.submit', function()
		{
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index -= 2;
			
			parentEle = $('.alert-body-container:eq(' + index + ')');
						
			if (parentEle.find('.rating-main-container').is('.park')) {
				// Is park rating
				var sport = parentEle.find('#sport').val();
				var quality = parentEle.find('#rating-hidden').val();
				var comment = parentEle.find('#comment').val();
				var parkID = parentEle.find('#id').val();
				var gameID = $('#rateGame-details').attr('gameID');
				var success  = '1';
				
				if (quality == '' && !parentEle.find('.rating-remember-no').is('.inner-shadow')) {
					showConfirmationAlert('Please rate this park.');
					return false;
				}
				
				if (parentEle.find('.rating-remember-no').is('.inner-shadow')) {
					// No button was clicked, submit rating with crowded
					success = '0';
				}
				
				ratePark(parkID, gameID, sport, quality, comment, success)
			} else {
				// Is user rating
				if (!parentEle.find('.rating-remember-no').is('.inner-shadow')) {
					// "No" is not clicked
					var sport = parentEle.find('#sport').val();
					var skill = parentEle.find('#skill').val();
					var sportsmanship = parentEle.find('#sportsmanship').val();
					var best  = parentEle.find('.rating-dropdown').val();
					var userID = parentEle.find('#id').val();
					var gameID = $('#rateGame-details').attr('gameID');
								
					rateUser(userID, gameID, sport, skill, sportsmanship, best)
				}
			}
			

			parentEle.find('.rating-main-container').hide();

			$(this).text('Submitted')
				   .unbind('click.submit');
				   
			if (testSubmitted()) {
				// All alerts have been rated
				$('.alert-black-back').trigger('click');
			}
		});
			
	}
	
})

/**
 * ajax to rate user
 */
function rateUser(userID, gameID, sport, skill, sportsmanship, best)
{
	var options = {userID: userID,
				   sport: sport,
				   skill: skill,
				   sportsmanship: sportsmanship,
				   bestSkill: best,
				   gameID: gameID};
			   
	$.ajax({
		url: '/ajax/rate-type',
		type: 'POST',
		data: {options: options,
			   type: 'user'},
		success: function(data) {
		}
	})
}


/**
 * ajax to rate park
 */
function ratePark(parkID, gameID, sport, quality, comment, success)
{
	var options = {parkID: parkID,
				   sport: sport,
				   quality: quality,
				   comment: comment,
				   gameID: gameID,
				   success: success};
			   
	$.ajax({
		url: '/ajax/rate-type',
		type: 'POST',
		data: {options: options,
			   type: 'park'},
		success: function(data) {
		}
	})
}


/**
 * custom callback function from slider to update hidden input skill
 * @params(sliderEle => slider element
 *		   value     => new value of slider)
 */
function populateSkillInput(sliderEle, value) 
{
	
	var hiddenEle = sliderEle.parents('.alert-body-container').find('#skill');
	
	hiddenEle.val(sliderSkillValues[value]['level']);
		
	populateSliderText(sliderEle, value);
}

/**
 * custom callback function from slider to update hidden input skill
 * @params(sliderEle => slider element
 *		   value     => new value of slider)
 */
function populateSportsmanshipInput(sliderEle, value) 
{
	
	var hiddenEle = sliderEle.parents('.alert-body-container').find('#sportsmanship');
	
	hiddenEle.val(sliderSportsmanshipValues[value]['level']);
		
	populateSliderText (sliderEle, value);
}

/**
 * test whether all the ratings on alert have been submitted or not
 */
function testSubmitted()
{
	var total = $('#rateGame-alert-container').find('.rating-submit').length;
	var count = 0;
	
	$('#rateGame-alert-container').find('.rating-submit').each(function()
	{
		if ($(this).text().toLowerCase() == 'submitted') {
			count++;
		}
	})
	
	if (count == total) {
		return true;
	} else {
		return false;
	}
}
