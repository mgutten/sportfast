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
	 */
	public function inviteAlert()
	{
		$href = '';
		if ($this->_view->game) {
			// Is game page
			$href = '/games/' . $this->_view->game->gameID . '/invite';
		} elseif ($this->_view->team) {
			$href = '/teams/' . $this->_view->team->teamID . '/invite';
		}
		
		$form = new Application_Form_General();
		
		$output  = $this->start('invite','Invite players');
		$output .= 	"<div class='clear width-100'>"
						. $form->text->setAttrib('id', 'inviteSearchAlert')
							   		 ->setLabel("Start typing a player's name...")
					. "</div>";
		$output .= "<div class='clear width-100' id='invite-alert-results'>";
		$output .= "</div>";
		$output .= "<a href='" . $href . "' class='clear width-100 center medium smaller-text larger-margin-top'>or by email</a>";
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
	 * @params ($game => game model with stored park and players)
	 */
	public function ratingAlert($game)
	{
		$ratings = $this->getRandomRatings($game);
		$details = "You recently played " . $game->sport . " on " . $game->gameDate->format('l') . ".  <br><span class='white'>Please rate some of the other players.</span>";
		$output  = $this->start('rateGame', $details);
		
		$form = $this->_view->rateGameForm;
		
		$output .= "<span id='rateGame-details' gameID='" . $game->gameID . "'></span>";
		
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
		
		$output .= "<div class='alert-body-container margin-top clear hidden'>";
		$output .=		"<p class='button rating-submit larger-text'>Submit</p>";
		$output .= "</div>";
		
		$output .= "<div class='alert-body-container margin-top hidden right'>";
		$output .=		"<p class='button rating-submit larger-text'>Submit</p>";
		$output .= "</div>";
		
		$output .= "</div>";
		
		return $output;
		
	}
	
	public function getRandomRatings($game)
	{
	
		$ratings = array();
		$chosen = array($this->_view->user->userID);
		$park = false;
		$b = 0;
		
		for ($i = 0; $b < 2; $i++) {
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
