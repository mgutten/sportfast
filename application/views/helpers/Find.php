<?php

class Application_View_Helper_Find
{
	public $resultCount;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * set resultCount for when ajax loading onto page
	 */
	public function find()
	{				
		return $this;
	}
	
	/**
	 * loop through matches and return output for find controller
	 * @params ($matches => array of game or team objects,
	 *			$type => 'game' or 'team',
	 *			$)
	 */
	public function loopMatches ($matches, $type, $resultCount = 0)
	{
	
		$this->resultCount = $resultCount;
		
		$function = 'create' . ucwords($type);
		$output   = '';
		$counter  = 0;
		$matchesCount = count($matches);

		if (!isset($matches[0])) {
			// No matches
			$output .= "<p class='heavy none-text medium width-100 center largest-text'>Oh no!</p>
							<p class='medium width-100 center larger-text'>No matches were found.</p>
							<p class='light width-100 center larger-text'>Try changing the filters.</p>";
		} else {
			
			$pages    = ceil($matchesCount / 6);
			foreach ($matches as $match) {
				if ($counter % 6 == 0) {
					// Factor of 6
					if ($counter != 0) {
						
						$output .= "</div>";
					}
	
					$output .= "<div class='find-results-inner-container clear width-100'>";
				}
				$output .= $this->$function($match, ($this->resultCount + 1));
				
				if ($counter == ($matchesCount - 1)) {
					// Last match
					
					$output .= "</div>";
				}
				
				$counter++;
			}
		}
		
		/*
		$output .= "<div class='pagination-pages-outer-container'><div class='pagination-pages-inner-container'>";
		
		
		if ($pages > 6) {
			// More than six pages, show first and last
			$output .= "<p class='left arial medium pointer hidden' id='pagination-first'> << </p>";
			$last    = "<p class='left arial medium pointer' id='pagination-last'> >> </p>";
			$limit   = 6;
		} else {
			$limit = $pages;
			$last  = '';
		}
		for ($i = 1; $i <= $limit; $i++) {
			$class = 'pagination-page pointer medium member-find-pagination';
			if ($i == 1) {
				$class .= ' light-back';
			}
			$output .= "<p class='" . $class . "'>" . $i . "</p>";
		}
		
		$output .= $last;
		
		
		
		$output .= "</div></div>";
		*/

		
		return $output;
		
	}
	
	/**
	 * create arrow next and last buttons
	 * @params ($prevOrNext => false = previous, true = next (ie bottom))
	 */
	public function createPagination ($prevOrNext)
	{
		if ($prevOrNext) {
			// next
			$src = '/images/find/pagination/next.png';
			$class = 'find-pagination-next';
			$id = 'pagination-next';
		} else {
			// last
			$src = '/images/find/pagination/prev.png';
			$class = 'find-pagination-prev';
			$id = 'pagination-prev';
		}
		
		$output = ''; 
		$output  .= "<div class='width-100 find-results-pagination-container left'>";
		$output  .=		"<div class='left find-pagination-first find-pagination-firstLast pointer animate-darker " . $id . "'><img  class='pagination-img' src='/images/find/pagination/first.png'/></div>";
		$output  .=     "<div class='left " . $class . " pointer animate-darker " . $id . "'><img class='pagination-img' src='" . $src . "'/></div>";
		$output  .= 	"<div class='left find-pagination-last find-pagination-firstLast pointer animate-darker " . $id . "'><img class='pagination-img' src='/images/find/pagination/last.png'/></div>";
		$output  .= "</div>";
		
		return $output;
	}
	
	public function createOuterStart ($id, $prefix)
	{
		$gameIndex = '';
		if ($prefix == 'games') {
			$gameIndex = " gameIndex='" . $this->resultCount . "' ";
		} elseif ($prefix == 'parks') {
			$gameIndex = " parkIndex='" . $this->resultCount . "' ";
		}
		$this->resultCount++;	
		
		return "<a href='/" . $prefix . "/" . $id . "' class='find-result-container find-result-" . $prefix . " left animate-darker' " . $gameIndex . ">";
	}
	
	public function createRight($match, $userInGame)
	{
		//$button = "<p class='button clear-right right larger-margin-top find-join hidden'>Join</p>";
		$button = '';
		if ($userInGame) {
			$button = '';
		}
		$limit = '';
		/*
		if ($match->hasValue('rosterLimit') && !($match instanceof Application_Model_Game)) {
			// Is a limit, show limit
			$limit = '/' . $match->rosterLimit;
		}
		*/
		
		if ($match instanceof Application_Model_Game) {
			$players = $match->countConfirmedPlayers();
		} else {
			// Is team
			$players = $match->totalPlayers;
		}
	
		$output  = "<div class='right find-result-right-container'>";
		$output .=		"<div class='left find-result-players-container'>";
		$output .=			"<p class='largest-text heavy darkest left width-100 center'>" . $players . $limit . "</p>";
		$output .=			"<p class='larger-text clear heavy width-100 darkest center find-players'>players</p>";
		$output .=		"</div>";
		$output .=		$button;
		$output .= "</div>";
		
		return $output;
	}
	
	/**
	 * create game container for a match (controller => find, action => games)
	 * @params ($match => game model,
	 *			$number => # of match in list
	 *			$showMatch => boolean should show whether you are already on this team?)
	 */
	public function createGame($match, $number = false, $showMatch = true)
	{
		
		$userInGame = false;
		if ($this->_view->user->games->gameExists($match->gameID, 'gameID')) {
			$userInGame = true;
		}
		
		if ($match->canceled) {
			$matchImg = '<img class="left" src="/images/global/canceled.png" tooltip="This game has been canceled. Reason: ' . $match->getCancelReason(true) . '"/>';
		} else {
			$matchImg = "<div class='left'>";
			if (!$match->isTeamGame() &&
				$showMatch) {
				// Only show match img for pickup games
				$matchImg .= "<img src='" . $match->getMatchImage() . "' tooltip='" . $match->getMatchDescription() . "' class='left find-result-match-img'/>" ;
				$class = 'find-results-joined';
			} else {
				$class = '';
			}
			$matchImg .= (($userInGame && $showMatch) ? "<p class='left " . $class . " smaller-text green'> You're playing!</p>" : '');
					  
			if (!$match->isPublic()) {
				// Private game
				$matchImg .= "<p class='clear margin-top red smaller-text'>Private Game</p>";
			}
			
			$matchImg .= "</div>";

		}
		
		if ($match->isTeamGame()) {
			// Is team game, show team game
			$leftBox = "<p class='heavy darkest clear larger-text'><span class='darkest'>vs. </span>" . $match->opponent . "</p>";
			$leftBox .= "<p class='clear medium'>" . $match->teamName . "</p>";
			$leftBox .= "<p class='clear darkest'>" . $match->getDay() . "</p>";
			$leftBox .= "<p class='clear darkest'>" . $match->getHour() . "</p>";
			$leftBox .= "<p class='clear medium'>" . $match->locationName . "</p>";
			$id = $match->teamID;
			$prefix = 'teams';
		} else {
			$leftBox  =	"<p class='left heavy largest-text darkest'>" . $match->getGameTitle() . "</p>";
			$leftBox .=	"<p class='clear darkest'>" . $match->getDay() . "</p>";		
			$leftBox .=	"<p class='clear darkest'>" . $match->getHour() . "</p>";		
			$leftBox .=	"<p class='clear darkest'>" . $match->park->parkName . "</p>";
			$id = $match->gameID;
			$prefix = 'games';
		}
			
		
		$output  = $this->createOuterStart($id, $prefix);
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			($number ? "<p class='find-result-number white green-back heavy'>" . $number . "</p>" : '');
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			$leftBox;
		$output .= 		"</div>";
		$output .=		$matchImg;
		$output .=		$this->createRight($match, $userInGame);
		$output .= "</a>";
		
		return $output;
	}
	
	/**
	 * create game container for a match (controller => find, action => games)
	 * @params ($match => team model,
	 *			$number => # of match in list,
	 *			$showMatch => show whether user is on team as well as match)
	 */
	public function createTeam($match, $number = false, $showMatch = true)
	{
		$userInGame = false;
		if ($this->_view->user->teams->teamExists($match->teamID)) {
			$userInGame = true;
		}
		
		$matchImg = "";
		if ($showMatch) {
			// Only show match img for pickup games
			$matchImg .= "<img src='" . $match->getMatchImage() . "' tooltip='" . $match->getMatchDescription() . "' class='left find-result-match-img'/>" ;

			$matchImg .= ($userInGame ? "<p class='left smaller-text green find-results-joined'> You're on this team!</p>" : '');
		}
		
		if ($number) {
			$number = "<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		}
		$output  = $this->createOuterStart($match->teamID, 'teams');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			$number;
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy largest-text darkest'>" . $match->teamName . "</p>";
		$output .=			"<p class='clear darkest larger-text'>" . $match->sport . " Team</p>";	
		$output .=			"<p class='clear light'>" . $match->city . "</p>";		
		$output .= 		"</div>";
		$output .=		$matchImg;
		$output .=		$this->createRight($match, $userInGame);
		$output .= "</a>";
		
		return $output;
	}

	/**
	 * create game container for a match (controller => find, action => games)
	 * @params ($match => game model,
	 *			$number => # of match in list)
	 */
	public function createPlayer($match, $number = true)
	{
		$sports = $match->sports;
		$sport = reset($sports);
		
		if ($number) {
			$number = "<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		}
		
		$output  = $this->createOuterStart($match->userID, 'users');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			$number;
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy largest-text darkest'>" . $match->shortName . "</p>";
		$output .=			"<p class='clear darkest'>" . $match->city->city . ', ' . $match->city->state . "</p>";
		$output .=			"<p class='clear light hover smaller-text'>last active " . $match->getLastActiveFromNow() . "</p>"; 
		$output .=			"<div class='hidden clear hover'><p class='clear medium'>" . $match->age . " years old</p>";	
		$output .=			"<p class='clear medium'>" . $match->getHeightInFeet() . "</p></div>";	
		$output .= 		"</div>";
		if ($sport) {
			$output .=		"<div class='right find-player-right-container'>";
			$output .=			"<div class='sport-holder'><p class='left width-100 center medium smaller-text hidden hover'>" . ucwords($sport->sport) . "</p></div>";
			$output .=			"<p class='find-player-overall white largest-text heavy left center medium-back'>" . $sport->overall . "</p>";
			//$output .=			"<p class='find-player-lower white larger-text  clear center medium-background' tooltip='Skill'>" . $sport->skillCurrent . "</p>";
			//$output .=			"<p class='find-player-lower white larger-text  left center medium-background' tooltip='Sportsmanship'>" . $sport->sportsmanship . "</p>";
			//$output .=			"<p class='find-player-lower white larger-text  left center medium-background' tooltip='Attendance'>" . $sport->attendance . "</p>";
			$output .=		"</div>";
			
			if ($sport->getFormat('league')->hasValue('format')) {
				$output .=	"<img src='/images/find/magnifying.png' class='right find-looking-for' tooltip='Looking for a league team.' />"; 
			}
		}
		
		
		$output .= "</a>";
		
		return $output;
	}
	
	/**
	 * create park container for a match (controller => find, action => parks)
	 * @params ($match  => park model,
	 *			$number => # of match in list)
	 */
	public function createPark($match, $number)
	{
		$width   = $match->ratings->getStarWidth('quality') . '%';
		$rating  = $this->_view->ratingstar('small', $width, false) . "<p class='hidden smaller-text clear green'>" . $match->ratings->countRatings() . "</p>";
		$user    = $this->_view->user;
		
		$stash = '';
		if ($match->hasStash()) {
			$stash = "<img src='/images/global/logo/logo/green/tiny.png' class='right find-stash' tooltip='Stash available'/>";
		}
		
		$membership = '';
		if ($match->membershipRequired) {
			$membership = "<p class='left larger-indent red smaller-text margin-top'>Membership Required</p>";
		}
		
		$output  = $this->createOuterStart($match->parkID, 'parks');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			"<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy larger-text darkest' style='font-size:1.75em'>" . $match->getLimitedName('parkName', 28) . "</p>";
		$output .=			$rating;
		$output .=			"<p class='clear light'>" . $match->city . "</p>";			
		$output .=			"<p class='clear darkest'>" . $match->type . "</p>" . $membership;	
		$output .= 		"</div>";
		$output .=		"<div class='right find-park-right-container'>";
		$output .=			"<p class='largest-text darkest width-100 center heavy find-park-distance' tooltip='Miles from your home.'>" . $match->getDistanceFromUser($user->location->getLatitude(), $user->location->getLongitude()) . "</p>";
		$output .=			"<p class='clear darkest width-100 center heavy larger-text find-players'>miles</p>";
		$output .=		"</div>";
		$output .=		$stash;
		$output .= "</a>";
		
		return $output;
	}

	

}
