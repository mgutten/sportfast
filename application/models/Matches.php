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
					if (!isset($this->_attribs['matches'][$i])) {
						// End of matches
						break;
					}
					array_push($returnMatches, $this->_attribs['matches'][$i]);
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
		if ($a->skillDifference > 12) {
			// large skillDifference, move to back of pile
			$a = -10;
			$b = 0;
		} else {
			$a = $a->totalPlayers - (abs($a->skillDifference) * .7);
			$b = $b->totalPlayers - (abs($b->skillDifference) * .7);
		}
		
       	if ($a == $b) {
			return 0;
		}
		
		return ($a > $b ? -1 : 1);
	}
	
}
