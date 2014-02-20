// rate users/park from previous game
var games = new Array();
var selectedGame;
var animating = false;

$(function()
{
		
	if ($('#rateGame-alert-container').length > 0) {
		// Rate user/park from last game is available, show it
				
		Game.prototype.isSelected = function() {
										if (this.selected == true) {
											return true;
										} else {
											return false;
										}
									}
		
		Game.prototype.getAvailablePlayers = function() {
												// Select random users and sport rating and populate html
												var players = [];
												for (var i = 0; i < this.players.length; i++) {
													players[i] = this.players[i];
												}
												
												for (var i = 0; i < players.length; i++) {
													if (players[i] == null) {
												
														players.splice(i, 1);
														continue;
													}
													
													if (players[i].count >= 2) {
														var userID = players[i].userID;
														var index = playerIDs[userID];
														
														players.splice(index, 1);
													}
													
												}
												
												var returnArray = players.clean(undefined);
												
												return returnArray;
												
											};
											
											
		Game.prototype.getAvailableSportRatings = function() {
												// Select random users and sport rating and populate html
												var sportRatings = [];
												
												for (var i = 0; i < this.sportRatings.length; i++) {
													sportRatings.push(this.sportRatings[i]);
												}
												
												var usedRatings = [];
												if (typeof this.selectedRatings != 'undefined') {
													
													for (var i = 0; i < this.selectedRatings.length; i++) {
														usedRatings.push(this.selectedRatings[i].sportRating.sportRatingID);
													}
												}
												
												
												for (var i = 0; i < sportRatings.length; i++) {
													if ($.inArray(sportRatings[i].sportRatingID, usedRatings) >= 0) {
														var sportRatingID = sportRatings[i].sportRatingID;
														var index = this.sportRatingIDs[sportRatingID];
														
														sportRatings[index] = null;
													}
												}
												
												var returnArray = sportRatings.clean(null);
												
												return returnArray;
												
											}
		
		Game.prototype.randomizeSelection = function(index) {
												// Select random users and sport rating and populate html
												var availablePlayers = this.getAvailablePlayers();
												var players = this.getRandomPlayers(2, availablePlayers);	
												
												if (!players) {
													return false;
												}
												
												var availableSportRatings = this.getAvailableSportRatings();
												var sportRating = this.getRandomSportRating(availableSportRatings);										
												
												if (!sportRating) {
													return false;
												}
												
												
												if (typeof this.selectedRatings == 'undefined') {
													this.selectedRatings = new Array();
												}
												
												this.selectedRatings[index] = new RelativeRating({sportRating: sportRating,
																								  players: players});
												
												
												if (this.isSelected()) {
													// Is current selected game, perform HTML filler
													this.populateHTML(index);
												}
											}
											
		Game.prototype.populateHTML = function(index) {
												// Select random users and sport rating and populate html
												var relativeRating = this.selectedRatings[index];
												var rating = relativeRating.sportRating;													
												
												var container = $('.rateGame-alert-inner-container:eq(' + index + ')');
												
												container.find('.rateGame-sportRating-ing').text(capitalize(rating.ing));
												container.find('.rateGame-sportRating-description').text(rating.description);
												container.find('.rateGame-sportRating-day').text(this.date.getDayName());
												
												for (var i = 0; i < 2; i++) {
													
													var player = relativeRating.players[i];
													var src = $('#player-' + player.userID).find('img').attr('src');
													
													var innerContainer = container.find('.rateGame-sportRating-user-container:eq(' + i + ')');
													if (relativeRating.winningUserID == player.userID) {
														innerContainer.addClass('selected');
													} else {
														innerContainer.removeClass('selected');
													}
													innerContainer.attr('userID', player.userID);
													innerContainer.find('img').attr('src', src);
													innerContainer.find('.rateGame-sportRating-user-name').text(player.getShortName());
												}
											}
		
		initialRatingSetup();
		selectGame(0);
		showAlert($('#rateGame-alert-container'), .9);
		
		
		/* user selected */
		$('.rateGame-sportRating-user-container').click(function()
		{
			if ($(this).is('.selected')) {
				return false;
			}
			$(this).parent().siblings('.rateGame-sportRating-user-outer-container').find('.rateGame-sportRating-user-container.selected').removeClass('selected').trigger('mouseout');
			
			$(this).addClass('selected');
			
			var index = $(this).parents('.rateGame-alert-inner-container').index('.rateGame-alert-inner-container');
			var game = getSelectedGame();
			
			var animate = true;
			if (game.selectedRatings[index].winningUserID ||
				(index + 1) == game.selectedRatings.length) {
				// Was previously set
				animate = false;
				
				var gameIndex = getSelectedGameIndex();
				if ((index + 1) == game.selectedRatings.length 
					&& games[gameIndex + 1]) {
						// There is a next game, show nextgame
						$('#rateGame-nextGame').show();
					
				}
			}
			
			if (animate) {
				$('#rateGame-rightArrow').trigger('click');
			}
			
			game.selectedRatings[index].winningUserID = $(this).attr('userID');
			game.selectedRatings[index].losingUserID = $(this).parents('.rateGame-sportRating-user-outer-container').siblings('.rateGame-sportRating-user-outer-container').find('.rateGame-sportRating-user-container').attr('userID');
			
			if ($('#rateGame-submit').css('display') == 'none') {
				$('#rateGame-submit').fadeIn();
			}
			
			
		})
		
		/* next game */
		$('#rateGame-nextGame').click(function()
		{
			$('.rateGame-alert-game-tab-container.selected').next('.rateGame-alert-game-tab-container').trigger('click');
		})
		
		/* game tab changed */
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
		
		/* change to next/prev sportRating */
		$('.rateGame-arrow').click(function()
		{
			if (animating) {
				return false;
			}
			var animateEle = $('.rateGame-alert-animate-container');
			var index = getSelectedRatingIndex();
			
			var direction = ($(this).is('#rateGame-leftArrow') ? 'left' : 'right');
			var game = getSelectedGame();
			
			$('.rateGame-arrow').show();
			$('#rateGame-nextGame').hide();
			
			if (((index + 2) == game.selectedRatings.length ||
					!game.selectedRatings[index].winningUserID) &&
				direction == 'right') {
					$('#rateGame-rightArrow').hide();
					
				}
				
			if ((index - 1) == 0 &&
				direction == 'left') {
					$('#rateGame-leftArrow').hide();
				}
				
			if (direction == 'left') {
				index -= 1;
			} else {
				index += 1;
			}
			
			$('#rateGame-alert-container').find('.indicator').removeClass('selected');
			$('#rateGame-alert-container').find('.indicator:eq(' + index + ')').addClass('selected');
			
			animateInner(animateEle, direction);
		})
		
		/* user was not there */
		$('.rateGame-noShow').click(function() {
			
			var userID = $(this).siblings('.rateGame-sportRating-user-container').attr('userID');
			
			var game = getSelectedGame();
			game.removePlayer(userID);
			
			if (game.getAvailablePlayers().length < 2) {
				// Fewer than 2 players, cannot rate players against eachother, show first game or hide
				var index = getSelectedGameIndex();
				games.splice(index, 1);
				var siblings = $('.rateGame-alert-game-tab-container:eq(' + index + ')').siblings('.rateGame-alert-game-tab-container');
				if (siblings.length > 0) {
					// There is another game in alert, show it and hide this game
					$('.rateGame-alert-game-tab-container:eq(' + index + ')').hide();
					$('.rateGame-alert-game-tab-container:eq(' + index + ')').siblings('.rateGame-alert-game-tab-container').first().trigger('click');
				} else {
					$('.alert-black-back').trigger('click');
				}
			} else {
			
				for (var i = 0; i < game.selectedRatings.length; i++) {
					var selected = false;
					for (var b = 0; b < 2; b++) {
						var selectedRating = game.selectedRatings[i];
						if (selectedRating.players[b].userID == userID) {
							selected = true;
						}
					}
					
					if (selected) {
						// User to be removed is in this section, randomize
						game.randomizeSelection(i);
					}
				}
			}
				
			var index = getSelectedRatingIndex();
			var selectedRating = game.selectedRatings[index];
			
			var winningUserID = userID;
			var sportRatingID = selectedRating.sportRating.sportRatingID;
			
			var options = {winningUserID: userID,
						   sportRatingID: selectedRating.sportRating.sportRatingID,
						   noShow: '1'};
						   
			if (game.isPickupGame()) {
				options.oldGameID = game.oldGameID;
			} else {
				options.teamGameID = game.teamGameID;
			}
			
			submitSelectedRatings([options]);
		});
		
		/* randomize current sportRating */
		$('.rateGame-random').click(function() {
			
			var game = getSelectedGame();
			var index = $(this).parents('.rateGame-alert-inner-container').index('.rateGame-alert-inner-container');
			
			game.randomizeSelection(index);
		})
		
		/* indicator was clicked, animate to indicator */
		$('.indicator').click(function()
		{
			if ($(this).is('selected')) {
				return false;
			}
			
			$('.indicator').removeClass('selected');
			$(this).addClass('selected');
			
			var current = getSelectedRatingIndex();
			var index = $(this).index();
			var change = current - index;
			var direction = (change < 0 ? 'right' : 'left');
			
			$('.rateGame-arrow').show();
			
			if (index == 0) {
				$('#rateGame-leftArrow').hide();
			} else if(index == 2) {
				$('#rateGame-rightArrow').hide();
			}
			
			var animateEle = $('.rateGame-alert-animate-container');
			
			animateInner(animateEle, direction, Math.abs(change));
		})
		
		/* submit all ratings */
		$('#rateGame-submit').click(function()
		{
			var ratings = new Array();
			for (var i = 0; i < games.length; i++) {
				// Loop through each game
				var game = games[i];
				
				for (var b = 0; b < game.selectedRatings.length; b++) {
					if (game.selectedRatings[b]) {
						if (game.selectedRatings[b].winningUserID) {
							var winningUserID = game.selectedRatings[b].winningUserID;
							var losingUserID = game.selectedRatings[b].losingUserID;
							var sportRatingID = game.selectedRatings[b].sportRating.sportRatingID;
							
							var options = {winningUserID: winningUserID,
										   losingUserID: losingUserID,
										   sportRatingID: sportRatingID};
										   
							if (game.isPickupGame()) {
								options.oldGameID = game.oldGameID;
							} else {
								options.teamGameID = game.teamGameID;
							}	
							
							ratings.push(options);	   
						}
					}
				}
			}
						 
			
			submitSelectedRatings(ratings, 'reloadPage');
			
			$('.alert-black-back').trigger('click');
			
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
 * ajax to penalize user who did not show up
 * @params(array => array of options objects specifying userRelativeRating)
 */
function submitSelectedRatings(array, callback)
{
	
	$.ajax({
		url: '/ajax/rate-user',
		type: 'POST',
		data: {ratings: array},
		success: function(data) {
			if (typeof callback != 'undefined') {
				callback();
			}
		}
	})
}


/**
 * get index of currently shown selectedRating
 */
function getSelectedRatingIndex()
{
	var animateEle = $('.rateGame-alert-animate-container');	
	return Math.abs(Math.round(parseInt(animateEle.css('margin-left'), 10)/animateEle.children().outerWidth()))
}

/**
 * animate inner container to next child
 */
function animateInner(animateEle, direction, quantity)
{
	if (typeof quantity == 'undefined') {
		quantity = 1;
	}
	animating = true;
	
	var dir = (direction == 'left' ? 1 : -1);
	var width = animateEle.children().outerWidth();
	var marginLeft = parseInt(animateEle.css('margin-left'),10) + (width * quantity * dir);
	
	animateEle.animate({'margin-left': marginLeft},{duration: 400, complete: function() {
			animating = false;
	}
	});
}
	

/**
 * select a game and populate html with appropriate details
 */
function selectGame(index)
{
	for (var i = 0; i < games.length; i++) {
		games[i].selected = false;
	}
	
	var game = games[index];
	
	game.selected = true;
	
	$('.rateGame-sportRating-user-container').removeClass('selected');
	
	
	for (var i = 0; i < 3; i++) {
		game.populateHTML(i);
	}
	
	$('.rateGame-arrow').hide();
	if (game.selectedRatings[0].winningUserID) {
		$('#rateGame-rightArrow').show();
	}
	$('.rateGame-alert-animate-container').css('margin-left', '0');
	
	$('#rateGame-nextGame').hide();
	
	$('#rateGame-alert-container').find('.indicator').removeClass('selected');
	$('#rateGame-alert-container').find('.indicator:eq(0)').addClass('selected');
	
}

/**
 * initialize first setup for each game rating
 */
function initialRatingSetup()
{
	var game;
	for (var i = 0; i < games.length; i++) {
		game = games[i];
		
		if (i == 0) {
			game.selected = true;
		}
		
		for (var b = 0; b < 3; b++) {
			game.randomizeSelection(b);
		}
		
	}
}

/**
 * get selected game
 */
function getSelectedGame()
{
	var index = getSelectedGameIndex();
	return games[index];
}

/**
 * get selected game index
 */
function getSelectedGameIndex()
{
	for (var i = 0; i < games.length; i++) {
		if (games[i].isSelected()) {
			return i;
		}
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
