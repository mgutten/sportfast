// rate users/park from previous game
var games = new Array();
var selectedGame

$(function()
{
		
	if ($('#rateGame-alert-container').length > 0) {
		// Rate user/park from last game is available, show it
		
		
		
		selectGame(0);
		showAlert($('#rateGame-alert-container'));
		
		
		$('.rateGame-sportRating-user-container').click(function()
		{
			if ($(this).is('.selected')) {
				return false;
			}
			$(this).siblings('.rateGame-sportRating-user-container.selected').removeClass('selected').trigger('mouseout');
			
			$(this).addClass('selected');
		})
		
		$('.rateGame-alert-game-tab-container').click(function()
		{
			if ($(this).is('.selected')) {
				return false;
			}
			
			$(this).siblings('.rateGame-alert-game-tab-container.selected').removeClass('selected');
			
			$(this).addClass('selected');
			
			var index = $(this).index('.rateGame-alert-game-tab-container');
			selectGame(index);
		})
			
		
		/*
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
		
		$('#rateGame-alert-container').find('.button').click(function()
		{
			$(this).addClass('schedule-button-selected inner-shadow');
		})
		
		$('.rating-remember-yes').click(function()
		{
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index += 2;
			
			
			$(this).parents('#rateGame-alert-container').find('.alert-submit-container').show();
			
			$(this).parents('.rating-main-container').find('.rating-remember-no-penalize').hide();
			
			$(this).siblings('.rating-remember-maybe').removeClass('green-b');
		})
		
		$('.rating-remember-no').click(function()
		{
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index += 2;
			
			var parentEle = $(this).parents('.rating-section-container');
			
			parentEle.siblings('.rating-section-container').hide();
			
			$(this).parents('#rateGame-alert-container').find('.alert-submit-container').show();
			
			
			$(this).parents('.rating-main-container').find('.rating-remember-no-penalize').show();
			
			$(this).siblings('.rating-remember-maybe').removeClass('underline');
			
		})
		
		$('.rating-remember-maybe').click(function()
		{
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index += 2;
			
			var parentEle = $(this).parents('.rating-section-container');
			
			parentEle.siblings('.rating-section-container').hide();
			
			$(this).parents('#rateGame-alert-container').find('.alert-submit-container').show();
			
			$('.alert-body-container').find('.rating-remember-no-penalize').hide();
			
			$(this).addClass('underline');
			
		})
		*/
		
		/**
		 * picture is unidentifiable
		 */
		$('.rating-overlay-unidentifiable').click(function()
		{
			var parentEle = $(this).parents('.alert-body-container');
			
			var idType = '';
			var typeID = '';
			var actingUserID = '';
			var receivingUserID = parentEle.find('#id').val();
			var action = 'mark';
			var type = 'user';
			var details = 'unidentifiable';
			
			createNotification(idType, typeID, actingUserID, receivingUserID, action, type, details)
			
			showConfirmationAlert('A message has been sent');
		});
		
		$('.rating-attendance').find('.button,.rating-remember-maybe').click(function()
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
			/*
			var index = $(this).parents('.alert-body-container').index('.alert-body-container');
			index -= 2;
			*/
			var index = 0;
			
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
				var sport = parentEle.find('#sport').val();
				var userID = parentEle.find('#id').val();
				var gameID = $('#rateGame-details').attr('gameID');
					
				if (parentEle.find('.rating-remember-yes').is('.inner-shadow')) {
					// "Yes" is clicked
					var skill = parentEle.find('#skill').val();
					var sportsmanship = parentEle.find('#sportsmanship').val();
					var best  = parentEle.find('.rating-dropdown').val();
								
					rateUser(userID, gameID, sport, skill, sportsmanship, best, '', '')
				} else if (parentEle.find('.rating-remember-no').is('.inner-shadow')) {
					// No is clicked
					var noShow = '1';
					
					rateUser(userID, gameID, sport, '', '', '', noShow);
				} else if (parentEle.find('.rating-remember-maybe').is('.underline')) {
					// Not sure is clicked
					var notSure = '1';
					rateUser(userID, gameID, sport, '', '', '', '', notSure);
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
function rateUser(userID, gameID, sport, skill, sportsmanship, best, noShow, notSure)
{
	var options = {userID: userID,
				   sport: sport,
				   skill: skill,
				   sportsmanship: sportsmanship,
				   bestSkill: best,
				   gameID: gameID,
				   noShow: noShow,
				   notSure: notSure};
				   	   
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
 * select a game and populate html with appropriate details
 */
function selectGame(index)
{
	var game = games[index];
	
	var players = game.getRandomPlayers(2);
	
	for (var i = 0; i < 2; i++) {
		var player = players[i];
		var src = $('#player-' + player.userID).find('img').attr('src');
		
		var container = $('.rateGame-sportRating-user-container:eq(' + i + ')');
		container.find('img').attr('src', src);
		container.find('.rateGame-sportRating-user-name').text(player.getShortName());
		
	}
	
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
