<?php

class Application_Model_Matches extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	
	protected $_attribs     = array('matches' => '',
									'totalRows' => '');
	
	public function addMatches($matches)
	{
		if ($matches) {
			// Matches exist
			foreach ($matches as $match) {
				$this->_attribs['matches'][] = $match;
			}
		}
		
		return $this;
	}
	
	/**
	 * sort matches by match, starting at $offset and going for $end
	 */
	public function sortByMatch($offset = 0, $end = false)
	{
		
		if (!empty($this->_attribs['matches'])) {
			// There are matches stored, sort them
			/*$matches = array();
			if ($offset != 0) {
				$end = (!$end ? count($this->_attribs['matches']) : $end);
				for ($i = $offset; $i < $end; $i++) {
					array_push($matches, $this->_attribs['matches'][$i]);
				}
			} else {
				$matches = $this->_attribs['matches'];
			}*/
			$matches = $this->attribs['matches'];
			usort($matches, array('Application_Model_Matches','matchSort'));
			
			$returnMatches = array();
			if ($end) {
				$end = (!$end ? count($this->_attribs['matches']) : $end);
				for ($i = $offset; $i < ($end + $offset); $i++) {
					if (!isset($matches[$i])) {
						// End of matches
						break;
					}
					array_push($returnMatches, $matches[$i]);
				}
				
			} else {
				$returnMatches = $matches;
			}
		
			return $returnMatches;
		} else {
			return false;
		}
	}
	
	private static function matchSort($a,$b) 
	{
		// Weight order based on skillDifference and # of players (weight skillDifference more)
		if (abs($a->skillDifference) > 14) {
			// large skillDifference, move to back of pile
			$a = -10;
			$b = 0;
		} else {
			/*
			$timeUntil = $timeUntilB = 0;
			if ($a instanceof Application_Model_Game) {
				// $a is a game
				$diff = $a->gameDate->format('U') - time();

				$diff = floor(($diff/60)/60); // convert to hours
				if ($diff <= 24) {
					// Under 24 hours until gametime, move up the list
					$timeUntil = $diff;
				}
			}
			if ($b instanceof Application_Model_Game) {
				// $a is a game
				$diff = $b->gameDate->format('U') - time();
				$diff = floor(($diff/60)/60); // convert to hours
				if ($diff <= 24) {
					// Under 24 hours until gametime, move up the list
					$timeUntilB = $diff;
				}
			}
			*/		
				
			$a = $a->totalPlayers - (abs($a->skillDifference) * .7);
			$b = $b->totalPlayers - (abs($b->skillDifference) * .7);
			
		}
		
       	if ($a == $b) {
			return 0;
		}
		
		return ($a < $b ? 1 : -1);
	}
	
}
