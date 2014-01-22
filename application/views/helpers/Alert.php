<?php

class Application_View_Helper_Alert
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function alert()
	{
		return $this;
	}
	
	public function start($id, $header = false, $x = true)
	{
		$output  = "<div class='alert-container alert' id='" . $id . "-alert-container'>";
		$output .= "<p class='alert-header white heavy'>" . $header . "</p>";
		if ($x) {
			$output .= "<p class='white bold arial alert-x pointer'>X</p>";
		}
		$output .= "<div class='alert-body-container'>";
					
		return $output;
	}
	
	public function end()
	{
		$output  = "</div></div>";
					
		return $output;
	}
	
	public function changesAlert() 
	{
		$output = $this->start('changes');
		
		$output .= "<div class='changes-alert-container'>";
		$output .= 		"<p class='width-100 largest-text medium center'>Save all changes?</p>";
		$output .= 		"<p class='button larger-text changes-save'>Save</p>";
		$output .= 		"<p class='button larger-text changes-discard'>Discard</p>";
		$output .= "</div>";
		
		$output .= $this->end();
		
		return $output;
	}
	
	public function confirmAlert()
	{
		$output  = $this->start('confirm-action','');
		$output .= 	"<p class='width-100 clear center'>&nbsp;Are you sure you want to <span id='confirm-action-text'></span>?</p>";
		$output .=	"<div class='clear width-100' id='confirm-action-postContent'></div>";
		$output .=	"<p class='button clear' id='confirm-action'>Yes</p>";
		$output .=	"<p class='button' id='deny-action'>No</p>";
		$output .= $this->end();	
		
		return $output;
	}
	
	/**
	 * invite players to game/team alert
	 * @params ($pendingInvites => associated array of 'joined' and 'notJoined' arrays, to be used to see if there are any pending invites for current user)
	 */
	public function inviteAlert($pendingInvites = false)
	{
		$href = '';
		if ($this->_view->game) {
			// Is game page
			$href = '/games/' . $this->_view->game->gameID;
		} elseif ($this->_view->team) {
			$href = '/teams/' . $this->_view->team->teamID;
		}
		
		$count = (count($pendingInvites['notJoined']) + count($pendingInvites['joined']['notMembers']));
		
		$form = new Application_Form_General();
		
		$output  = $this->start('invite','Invite players');
		$output .=  "<a href='" . $href . "/pending' class='right smaller-text medium' tooltip='You have " . $count . " pending invites for this game'>" . $count . " pending invites</a>";
		$output .= 	"<div class='clear width-100'>"
						. $form->text->setAttrib('id', 'inviteSearchAlert')
							   		 ->setLabel("Start typing a player's name...")
					. "</div>";
		$output .= "<div class='clear width-100' id='invite-alert-results'>";
		$output .= "</div>";
		$output .= "<a href='" . $href . "/invite' class='clear width-100 center medium smaller-text larger-margin-top'>or by email</a>";
		$output .= $this->end();
		
		return $output;
	}
	
	/**
	 * minimal signup alert
	 * @params ($user => Application_Model_User,
	 *			$cityID => cityID to input for user
	 *			$location => str 'POINT(lat lon)')
	 */
	public function minimalSignup($user, $cityID, $location) {
		
		$signupForm = new Application_Form_Signup();
		
		$output = $this->start('signup1', '', false);
			
			/*echo "<div class='clear width-100'>
					<p class='right medium smaller-text width-100 center'>Have an account?</p>
					<div class='clear width-100'><a href='/login' class='auto-center button smaller-text' id='game-signup-login'>Login</a></div>
				  </div>";*/
			
		$output .= "<img src='/images/global/logo/large2.png' class='left' id='game-signup-logo'/>";
		
		$output .= "<div class='right'>
				<p class='right medium smaller-text'>Have an account?</p>
				<a href='/login' class='clear-right button smaller-text' id='game-signup-login'>Login</a>
			  </div>";
		
		
		
		$output .= "<form action='/signup/basic' method='post' id='user-signup'>";
		if ($user->hasValue('username')) {			
			$output .= "<p class='clear width-100 center heavy red margin-top'>Please signup to complete action</p>";
		} else {
			$output .= "<p class='clear width-100 center heavy red margin-top'>Signup required</p>";
		}
		$output .= "<div class='game-signup-hover width-100 clear'>";
		$output .= "<p class='game-signup-hover-target clear light width-100 center largest-margin-top'>Enter your name so other players can recognize you.</p>";
		
		$output .= "<div class='clear width-100'>" . $signupForm->firstName . "</div>";
		$output .= "<div class='clear width-100'>" . $signupForm->lastName . "</div>";
		$output .= "</div>";
		
		$output .= "<div class='game-signup-hover width-100 clear'>";
		$email = '';
		if ($user->hasValue('username')) {
			$email = $user->username;
		}
		$output .= "<p class='clear largest-margin-top light width-100 center game-signup-hover-target'>Your email will be your login for Sportfast. </p>";
		$output .= "<div class='clear width-100'>" . $signupForm->email->setValue($email) . "</div>";
		$output .= "<div class='clear width-100'>" . $signupForm->signupPassword . "</div>
					<p class='clear light smaller-text width-100 center game-signup-hover-target'>Must be at least 8 characters.</p>";
		$output .= "</div>";
		//$output .= "<p class='clear smaller-text medium width-100 center hidden' id='game-password-reqs'>Must be at least 8 characters</p>";
		
		/*
		$output .= "<div id='game-signup-how-container' class='dropshadow'>
				<p class='white clear width-100 smaller-text center'>How Sportfast Works</p>
				</div>";
		*/
		$output .= "<div class='clear width-100 largest-margin-top' id='game-signup-options-container'>";
		$output .= "<p class='clear light width-100 center'>Sportfast analyzes players' skill, age, and availability to create competitive games <a href='/how' target='_blank' class='medium underline'>learn more</a>:</p>";
		$output .= "<div class='white-background animate-darker clear games-signup-option-container pointer' tooltip='More information includes:<ul class=\"heavy medium\"><li>Age</li><li>Zipcode</li><li>Sports Preferences</li></ul>'>
				<div class='clear width-100'>
					<img src='/images/games/account/all.png' class='auto-center'/>
				</div>
				<p class='width-100 center medium heavy clear'>I want to use this site to its full potential.</p>
				<p class='clear width-100 center medium smaller-text'>More information required</p>
			  </div>";
		
		$output .= "<div class='white-background animate-darker clear games-signup-option-container pointer' id='game-signup-minimal' tooltip='By choosing this, you cannot:<ul class=\"heavy medium\"><li>Join Sportfast-created games</li><li>Be matched to nearby games</li><li>Receive ratings</li>'>
				<div class='clear width-100'>
					<img src='/images/games/account/limited.png' class='auto-center'/>
				</div>
				<p class='width-100 center medium heavy clear '>I just want the bare minimum.</p>
				<p class='clear width-100 center medium smaller-text'>You're done, but site function will be limited</p>
			  </div>";
		$output .= "</div>";
		$output .= "<p class='clear medium smaller-text margin-top width-100 center'>By selecting an option you agree to Sportfast's <a href='/about/terms' class='inherit underline' target='_blank'>Terms and Conditions</a>.</p>";
		
		$output .= "<input type='hidden' name='location' value='" . $location . "'/>";
		$output .= "<input type='hidden' name='cityID' value='" . $cityID . "'/>";
		
		$output .= "</form>";
			
		$output .= $this->end();
		
		return $output;
	}
	
	/**
	 * message players of game/team
	 */
	public function messageAlert()
	{
		if ($this->_view->game) {
			// Is game
			$typeID = $this->_view->game->gameID;
			$idType = 'gameID';
		} else {
			// Team
			$typeID = $this->_view->team->teamID;
			$idType = 'teamID';
		}
		
		$form = new Application_Form_General();
		
		$output  = $this->start('message','Message players');
		
		$output .= "<form action='/mail/message' method='post'>";
		
		$output .= "<div class='clear width-100'>";
		$output .= $form->textarea->setAttrib('id', 'messageBody')
								  ->setName('messageBody')
							 	  ->setLabel("Write message here..."); 
		$output .= "<p class='clear smaller-text medium larger-indent'>This message will be sent to all subscribers and currently attending players.</p>";
		$output .= "</div>";
		
		$output .= $form->hidden->setAttrib('id', $idType)
								->setName($idType)
								->setValue($typeID);
		
		$output .= $form->submit->setAttrib('id', 'messageSubmit')
								->setAttrib('class', 'button larger-text heavy')
								->setLabel('Send');
		
		$output .= "</form>";
		$output .= $this->end();
		
		return $output;
	}
	
	/**
	 * alert for post-game ratings of users or park that game was played at
	 * @params ($game => games model with stored games with park and players)
	 */
	public function ratingAlert($games)
	{
		$output = '';
		$countGames = count($games->getAll());
		$players = "<span class='rateGame-alert-players'>";
		$html = "<div id='rateGame-alert-container' class='alert-container'>
					<!--<p class='clear white smaller-text '>" . $countGames . " recent game" . ($countGames == 1 ? '' : 's') . "</p>-->
					<div class='rateGame-alert-games-tab-container clear margin-top width-100'>"; 
		$js   = "<script type='text/javascript'>";
		
		$counter = 0;
		foreach ($games->getAll() as $game) {
			if ($game->isPickupGame()) {
				$id = 'pickup' . $game->oldGameID;
				$parkName = $game->getLimitedName('parkName', 14);
				$fullParkName = $game->parkName;
				$gameOpts = "{gameID:" . $game->gameID . ",
							  oldGameID:" . $game->oldGameID . ",";
			} else {
				$id = 'teamGame' . $game->teamGameID;
				$parkName = $game->getLimitedName('leagueLocationName', 14);
				$fullParkName = $game->leagueLocationName;
				$gameOpts = "{teamGameID:" . $game->teamGameID . ",";
			}
			$gameOpts .= "date:'" . $game->date . "',
						  park:'" . $fullParkName . "',
						  sport: '" . strtolower($game->sport) . "',
						  sportID: '" . $game->sportID . "'}";
			$js .= "var game = new Game(" . $gameOpts . ");";
			
			foreach ($game->players->getAll() as $user) {
				$js .= "game.addPlayer({userID: " . $user->userID . ",
										firstName: '" . $user->firstName . "',
										lastName: '" . $user->lastName . "'});";
			}
			
			foreach ($game->sportRatings->getAll() as $rating) {
				$js .= "game.addRating({sportRatingID: " . $rating->sportRatingID . ",
										ing: '" . $rating->ing . "',
										description: '" . $rating->description . "'});";
			}
			
			$js .= "games.push(game);";
			

			foreach ($game->players->getAll() as $user) {
				$players .= "<div class='rateGame-alert-player-container hidden' id='player-" . $user->userID . "'>
								<img src='" . $user->getProfilePic('large') . "' class='clear'/>
								<p class='clear margin-top largest-text darkest heavy'>" . $user->getShortName() . "</p>
							 </div>";
			}
			
			$selected = '';
			if ($counter == 0) {
				$selected = 'selected';
			}
			
			$sport = new Application_Model_Sport();
			$sport->sportID = $game->sportID;
			$sport->sport = $game->sport;
			
			$html .= "<div class='left rateGame-alert-game-tab-container " . $selected . " pointer rounded-corners' >
						<img src='" . $sport->getIcon('tiny', 'solid', 'white') . "' class='left solid' tooltip='" . $sport->sport . "'/>
						<img src='" . $sport->getIcon('tiny', 'outline') . "' class='left dark-back outline hidden'/>
						<p class='left indent heavy white'>" . $game->getDay() . "</p>
						<p class='clear white smaller-text parkName' tooltip='" . $fullParkName . "'>at " . $parkName . "</p>
					  </div>";
			
			$counter++;
		}
		
		$players .= "</span>";
		$js .= "</script>";
		
		$html .= "</div>";
		
		$html .= "<div class='clear rateGame-alert-outer-container width-100'>
					<div class='left rateGame-arrow' id='rateGame-leftArrow'></div>";
					
		$html .=	"<div class='left rateGame-alert-inner-container width-100'>
						<div id='rateGame-sportRating-container' class='white'>
							<div id='rateGame-sportRating-back' class=''></div>
							<div id='rateGame-sportRating-text-container' class='inherit'>
								<p class='largest-text inherit heavy clear width-100 center' id='rateGame-sportRating-ing'>Shooting</p>
								<p class='clear inherit smaller-text width-100 center'>Who <span class='inherit' id='rateGame-sportRating-description'>shot better</span>?</p>
							</div>
						</div>
						<div class='left rateGame-sportRating-user-container animate-opacity'>
							<div class='clear rateGame-sportRating-user-img-container'>
								<img src='" . $user->getProfilePic('large') . "' class='left'/>
								<div class='rateGame-sportRating-user-name-container'>
									<div class='transparent-black'></div>
									<p class='width-100 center larger-text heavy white rateGame-sportRating-user-name'>" . $user->getShortName() . "</p>
								</div>
							</div>					
						</div>
						<div class='right rateGame-sportRating-user-container animate-opacity'>
							<div class='clear rateGame-sportRating-user-img-container'>
								<img src='" . $user->getProfilePic('large') . "' class='left'/>
								<div class='rateGame-sportRating-user-name-container'>
									<div class='transparent-black'></div>
									<p class='width-100 center larger-text heavy white rateGame-sportRating-user-name'>" . $user->getShortName() . "</p>
								</div>
							</div>					
						</div>
					</div>";
		
		$html .= " <div class='left rateGame-arrow' id='rateGame-rightArrow'></div>
				  </div>";
					
		
		$html .= "</div>";
		
		/*
		$ratings = $this->getRandomRatings($game);
		if (!$ratings[0]->hasValue('userID')) {
			$please = 'the location.';
		} else {
			// Is user 
			$please = 'some of the other players.';
		}
		$details = "You recently played " . $game->sport . " on " . $game->gameDate->format('l') . ".  <br><span class='white'>Please rate " . $please . "</span>";
		$output  = $this->start('rateGame', $details);
		
		$form = $this->_view->rateGameForm;
		
		$output .= "<span id='rateGame-details' gameID='" . $game->gameID . "'></span>";
		
		if (empty($ratings)) {
			return false;
		}
		
		$counter = 0;
		foreach ($ratings as $rating) {
			
			if ($counter > 0) {
				$output .= "<div class='alert-body-container'>";
			}
			
			$user = $park = false;
			if ($rating->userID) {
				// Is user
				$name = $rating->shortName;
				$user = true;
				$typeClass = 'user';
			} else {
				// Is park
				$name = $rating->parkName;
				$park = true;
				$typeClass = 'park';
			}
			
			$output .= "<div class='left overlay-container indent'>";
			$output .= 	"<img src='" . $rating->getProfilePic('large') . "' class='left overlay-trigger'/>";
			$output .=	"<div class='clear overlay-pic overlay-pic-large black-back'>
						 </div>
						 <div class='left overlay-pic overlay-pic-large'>
							<p class='clear width-100 center white heavy largest-text margin-top'>" . $name . "</p>
							<p class='clear width-100 center white smaller-text heavy rating-overlay-unidentifiable action pointer'>mark picture as unidentifiable</p>
						 </div>";
			$output .= "</div>";
			$output .= "<div class='clear rating-main-container margin-top " . $typeClass . "'>";
			
			if ($user) {
				// Attendance
				$output .=  "<p class='clear width-100 center smaller-text medium'>All user ratings are anonymous.</p>";
				$output .=	"<div class='clear width-100 rating-section-container rating-attendance'>";
				$output .=		"<p class='clear width-100 medium light-back'>Did " . $rating->getHeOrShe() . " show up?</p>";
				$output .=		"<p class='clear button pointer button-small larger-margin-top rating-animate-trigger rating-remember-yes'>Yes</p>";
				$output .=		"<p class='left button pointer button-small larger-margin-top rating-remember-no'>No</p>";
				$output .=		"<p class='right pointer larger-margin-top smaller-text medium rating-remember-maybe action'>Not Sure</p>";
				$output .= 	"</div>";
				
				// Skill
				$output .=	"<div class='clear width-100 rating-section-container hidden'>";
				$output .=		"<p class='clear width-100 medium light-back rating-section-header'>How skilled?</p>";
				$output .=		"<div class='clear width-100 larger-margin-top'>"
								. $this->_view->slider()->create(array('id'    		=> 'skill-rating',
																		'desc'			=> true,
																		'valuePosition' => 'above',
																		'valueClass'	=> 'green-bold',
																		'descClass'		=> 'smaller-text',
																		'sliderClass'	=> 'rating-animate-trigger',
																		'type'			=> 'skill'))
								. "</div>";
				$output .=	"";
				
				// Sportsmanship
				$output .=	"";
				$output .=		"<p class='clear width-100 medium light-back rating-section-header rating-sportsmanship-header'>Sportsmanship?</p>";
				$output .=		"<div class='clear width-100 larger-margin-top'>"
								. $this->_view->slider()->create(array('id'    		=> 'sportsmanship-rating',
																	'desc'			=> true,
																	'valuePosition' => 'above',
																	'valueClass'	=> 'green-bold',
																	'descClass'		=> 'smaller-text',
																	'sliderClass'	=> 'rating-animate-trigger',
																	'type'			=> 'sportsmanship'))
								. "</div>";
				
				// Good at?				
				$output .=		"<p class='clear width-100 medium light-back rating-section-header rating-sportsmanship-header'>Good at?</p>";
				$output .=		"<div class='clear width-100 larger-margin-top'>";
				$output .=			"<select class='clear rating-dropdown darkest'>";
				
				foreach ($this->_view->rateGameSkills as $array) {
					$output .=	"<option>" . ucwords($array['skilling']) . "</option>";
				}
				$output .=				"<option>None</option>";
				$output .=			"</select>";
				$output .=		"</div>";				
				
				$output .=	"</div>";
				
				$output .= "<p class='width-100 hidden center red smaller-text clear larger-margin-top rating-remember-no-penalize'>" . ucwords($rating->getHisOrHer()) . " attendance will be slightly penalized.</p>";
				
				$output .=	$form->skill;
				$output .=	$form->sportsmanship;
				$output .=	$form->id->setValue($rating->userID);

			} elseif ($park) {
				// Crowdedness
				$output .=	"<div class='clear width-100 rating-section-container rating-attendance'>";
				$output .=		"<p class='clear width-100 medium light-back'>Did the game happen here?</p>";
				$output .=		"<p class='clear button pointer button-small larger-margin-top rating-animate-trigger rating-remember-yes'>Yes</p>";
				$output .=		"<p class='left button pointer button-small larger-margin-top rating-remember-no'>No</p>";
				$output .= 	"</div>";
				
				// Quality
				$output .=	"<div class='clear width-100 rating-section-container hidden'>";
				$output .=		"<p class='clear width-100 medium light-back rating-section-header'>Quality?</p>";
				$output .=		"<div class='clear width-100 larger-margin-top'>"
								. $this->_view->getHelper('ratingstar')->clickablestar('large')
								. "</div>";
				$output .=		"<div class='clear width-100 larger-margin-top'>";
				$output .=			$form->comment;
				$output .=		"</div>";
				$output .=	"</div>";
				
				$output .=	$form->id->setValue($rating->parkID);
				$output .= "<input type='hidden' id='rating-hidden'/>";
			}
			
			$output .=	$form->sport;
			
			$output .= "</div>";
			
			$output .= "</div>"; // alert-body-container close
			
			$counter++;
		}
		
		/*$output .= "<div class='alert-body-container margin-top clear hidden'>";
		$output .=		"<p class='button rating-submit larger-text'>Submit</p>";
		$output .= "</div>";
		*/
		
		/*
		$output .= "<div class='alert-body-container margin-top hidden clear alert-submit-container'>";
		$output .=		"<p class='button rating-submit larger-text'>Submit</p>";
		$output .= "</div>";
		
		$output .= "</div>";
		*/
		
		$output = $js . $html . $players;
		
		return $output;
		
	}
	
	public function getRandomRatings($game)
	{
	
		$ratings = array();
		$chosen = array($this->_view->user->userID);
		$park = ($game->hasValue('park') ? false : true);
		$b = 0;
		
		for ($i = 0; $b < 1; $i++) {
			$random = mt_rand(1,10);
			
			if ($random > 9 && !$park) {
				// Choose to rate park 10% of the time
				$ratings[] = $game->park;
				$park = true;
				$b++;
			} else {
				// Choose random user
				$player = $game->players->random();
				if (in_array($player->userID, $chosen)) {
					// Already been chosen
					continue;
				}
				$chosen[] = $player->userID;
				$ratings[] = $player;
				$b++;
			}
		}
		
		return $ratings;
	}

}
