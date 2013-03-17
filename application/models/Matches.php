<?php

class Application_Model_Matches extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	
	protected $_attribs     = array('matches' => '');
	
	public function addMatches($matches)
	{
		foreach ($matches as $match) {
			$this->_attribs['matches'][] = $match;
		}
		return $this;
	}
	
	public function sortByMatch()
	{
		usort($this->_attribs['matches'], array('Application_Model_Matches','matchSort'));
		return $this->_attribs['matches'];
	}
	
	private static function matchSort($a,$b) 
	{
		// Weight order based on skillDifference and # of players (weight skillDifference more)
		if ($a->skillDifference > 12) {
			// large skillDifference, move to back of pile
			$a = -10;
			$b = 0;
		} else {
			$a = $a->totalPlayers - (abs($a->skillDifference) * .5);
			$b = $b->totalPlayers - (abs($b->skillDifference) * .5);
		}
		
       	if ($a == $b) {
			return 0;
		}
		
		return ($a > $b ? -1 : 1);
	}
	
}
