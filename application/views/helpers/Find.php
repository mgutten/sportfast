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
		$output  = $this->createOuterStart($match->gameID, 'games');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			"<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy largest-text darkest'>" . $match->getGameTitle() . "</p>";
		$output .=			"<p class='clear darkest'>" . $match->getDay() . "</p>";		
		$output .=			"<p class='clear darkest'>" . $match->getHour() . "</p>";		
		$output .=			"<p class='clear darkest'>" . $match->park->parkName . "</p>";
		$output .= 		"</div>";
		$output .=		"<img src='" . $match->getMatchImage() . "' tooltip='" . $match->getMatchDescription() . "' class='left find-result-match-img'/>";	
		$output .=		($userInGame ? "<p class='left find-results-joined smaller-text green'> You are playing!</p>" : '');
		$output .=		$this->createRight($match, $userInGame);
		$output .= "</a>";
		
		return $output;
	}
	
	/**
	 * create game container for a match (controller => find, action => games)
	 * @params ($match => team model,
	 *			$number => # of match in list)
	 */
	public function createTeam($match, $number)
	{
		$userInGame = false;
		if ($this->_view->user->teams->teamExists($match->teamID)) {
			$userInGame = true;
		}
		$output  = $this->createOuterStart($match->teamID, 'teams');
		$output .=		"<div class='left find-result-img-container'>";
		$output .=			"<img src='" . $match->getProfilePic('medium') . "' class='left'/>";
		$output .=			"<p class='find-result-number white green-back heavy'>" . $number . "</p>";
		$output .= 		"</div>";
		$output .=		"<div class='left find-result-left-container'>";
		$output .=			"<p class='left heavy largest-text darkest'>" . $match->teamName . "</p>";
		$output .=			"<p class='clear darkest larger-text'>" . $match->sport . " Team</p>";	
		$output .=			"<p class='clear light'>" . $match->city . "</p>";		
		$output .= 		"</div>";
		$output .=		"<img src='" . $match->getMatchImage() . "' tooltip='" . $match->getMatchDescription() . "' class='left find-result-match-img'/>";	
		$output .=		($userInGame ? "<p class='left find-results-joined smaller-text green'> You are on this team!</p>" : '');
		$output .=		$this->createRight($match, $userInGame);
		$output .= "</a>";
		
		return $output;
	}

	

}
