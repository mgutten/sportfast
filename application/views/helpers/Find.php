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
		$output  = "<div class='right find-result-right-container'>";
		$output .=		"<div class='left find-result-players-container'>";
		$output .=			"<p class='largest-text heavy darkest left width-100 center'>" . $match->totalPlayers . "/" . $match->rosterLimit . "</p>";
		$output .=			"<p class='larger-text clear heavy width-100 darkest center find-players'>players</p>";
		$output .=		"</div>";
		$output .=		$button;
		$output .= "</div>";
		
		return $output;
	}
	
	/**
	 * create game container for a match (controller => find, action => games)
	 * @params ($match => game model,
	 *			$number => # of match in list)
	 */
	public function createGame($match, $number)
	{
		$userInGame = false;
		if ($this->_view->user->games->gameExists($match->gameID, 'gameID')) {
			$userInGame = true;
		}
		
		if ($match->canceled) {
			$matchImg = '<img class="left" src="/images/global/canceled.png" tooltip="This game has been canceled. Reason: ' . $match->getCancelReason(true) . '"/>';
		} else {
			$matchImg = "<img src='" . $match->getMatchImage() . "' tooltip='" . $match->getMatchDescription() . "' class='left find-result-match-img'/>" 
					  . ($userInGame ? "<p class='left find-results-joined smaller-text green'> You're playing!</p>" : '');

			
		}
		
		$output  = $this->createOuterStart($match->gameID, 'games');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			($number ? "<p class='find-result-number white green-back heavy'>" . $number . "</p>" : '');
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy largest-text darkest'>" . $match->getGameTitle() . "</p>";
		$output .=			"<p class='clear darkest'>" . $match->getDay() . "</p>";		
		$output .=			"<p class='clear darkest'>" . $match->getHour() . "</p>";		
		$output .=			"<p class='clear darkest'>" . $match->park->parkName . "</p>";
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
		$output .=		"<img src='" . $match->getMatchImage() . "' tooltip='" . $match->getMatchDescription() . "' class='left find-result-match-img'/>";	
		$output .=		($userInGame ? "<p class='left find-results-joined smaller-text green'> You're on this team!</p>" : '');
		$output .=		$this->createRight($match, $userInGame);
		$output .= "</a>";
		
		return $output;
	}

	/**
	 * create game container for a match (controller => find, action => games)
	 * @params ($match => game model,
	 *			$number => # of match in list)
	 */
	public function createPlayer($match, $number)
	{
		$sports = $match->sports;
		$sport = reset($sports);
		$output  = $this->createOuterStart($match->userID, 'users');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			"<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy largest-text darkest'>" . $match->shortName . "</p>";
		$output .=			"<p class='clear darkest'>" . $match->city->city . ', ' . $match->city->state . "</p>";
		$output .=			"<p class='clear light hover smaller-text'>last active " . $match->getLastActiveFromNow() . "</p>"; 
		$output .=			"<div class='hidden clear hover'><p class='clear medium'>" . $match->age . " years old</p>";	
		$output .=			"<p class='clear medium'>" . $match->getHeightInFeet() . ' ' . $match->weight . "lb</p></div>";	
		$output .= 		"</div>";
		$output .=		"<div class='right find-player-right-container'>";
		$output .=			"<div class='sport-holder'><p class='left width-100 center medium smaller-text hidden hover'>" . ucwords($sport->sport) . "</p></div>";
		$output .=			"<p class='width-100 find-player-overall white largest-text heavy left center medium-background'>" . $sport->overall . "</p>";
		$output .=			"<p class='find-player-lower white larger-text  clear center medium-background' tooltip='Skill'>" . $sport->skillCurrent . "</p>";
		$output .=			"<p class='find-player-lower white larger-text  left center medium-background' tooltip='Sportsmanship'>" . $sport->sportsmanship . "</p>";
		$output .=			"<p class='find-player-lower white larger-text  left center medium-background' tooltip='Attendance'>" . $sport->attendance . "</p>";
		$output .=		"</div>";
		
		if ($sport->getFormat('league')->hasValue('format')) {
			$output .=	"<img src='/images/find/magnifying.png' class='right find-looking-for' tooltip='Looking for a league team.' />"; 
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
		$rating  = $this->_view->ratingstar('small', $width, false) . "<p class='smaller-text left indent green larger-margin-top'>" . $match->ratings->countRatings() . "</p>";
		$user    = $this->_view->user;
		
		$stash = '';
		if ($match->hasStash()) {
			$stash = "<img src='/images/global/logo/logo/green/tiny.png' class='right find-stash' tooltip='Stash available'/>";
		}
		
		$output  = $this->createOuterStart($match->parkID, 'parks');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			"<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy larger-text darkest'>" . $match->parkName . "</p>";
		$output .=			$rating;
		$output .=			"<p class='clear light'>" . $match->city . "</p>";			
		$output .=			"<p class='clear darkest'>" . $match->type . "</p>";	
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
