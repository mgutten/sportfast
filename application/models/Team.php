<?php

class Application_Model_Team extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_TeamsMapper';
	
	protected $_attribs     = array('teamID' 	   => '',
									'sport' 	   => '',
									'sportID'	   => '',
									'rosterLimit'  => '',
									'teamName' 	   => '',
									'teamPic' 	   => '',
									'public'  	   => '',
									'totalPlayers' => '',
									'skillDifference' 	   => '',
									'averageSkill' => '',
									'averageAttendance'    => '',
									'averageSporstmanship' => '',
									'match'		   => '',
									'city'		   => ''
									);
									
	protected $_primaryKey = 'teamID';


	public function __construct($resultRow = false)
	{
		
		if ($resultRow) {
			$this->setAttribs($resultRow);
		}
				
	}
	
		public function getMatchName()
	{
		if (empty($this->match)) {
			// Match not set
			$diff = abs($this->skillDifference);
			if ($diff < 4) {
				// Avg skill of game and user is close, great match
				$this->match = 'great';
			} elseif ($diff < 7) {
				$this->match = 'good';
			} elseif ($diff < 10) {
				$this->match = 'decent';
			} elseif ($diff < 1000) {
				$this->match = 'bad';
			}
			
		}
		
		return $this->match;
	}
	
	public function getMatchImage()
	{
		
		return "/images/global/match/small/" . $this->getMatchName() . ".png";
	}
	
	public function getMatchDescription()
	{
		if ($this->skillDifference > 0) {
			// The players in the game are better than user
			$better = 'more skilled';
		} else {
			// The players in the game are better than user OR exactly equal
			$better = 'less skilled';
		}
		
		$match  = strtolower($this->getMatchName());
		$adverb = '';
		
		if ($match == 'good') {
			$adverb = 'slightly';
		} elseif ($match == 'decent'){
			$adverb = '';
		} elseif ($match == 'bad') {
			$adverb = 'significantly';
		}
		
		$matchDescription = 'This team is a <span class="bold medium">' . $match . '</span> match for you.<br><span class="smaller-text medium">The average player on this team is ' . $adverb . ' ' . $better . ' than you.</span>';
		
		if ($match == 'great') {
			$matchDescription = 'You are a near perfect match for the players on this team.';
		}
		
		return $matchDescription;
	}

		
	
}
