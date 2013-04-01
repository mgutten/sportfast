<?php

class Application_Model_Game extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GamesMapper';
	
	protected $_attribs     = array('gameID' 	    => '',
									'teamGameID'	=> '',
									'park' 			=> '',
									'public'		=> '',
									'sport'			=> '',
									'sportID' 		=> '',
									'rosterLimit' 	=> '',
									'maxSkill'  	=> '',
									'minSkill'		=> '',
									'maxAge'    	=> '',
									'minAge'   		=> '',
									'recurring'	  	=> '',
									'date'	  		=> '',
									'totalGoing'	=> '',
									'averageSkill'  => '',
									'averageAttendance'    => '',
									'averageSporstmanship' => '',
									'skillDifference'	   => '',
									'totalPlayers' 	=> '',
									'match'			=> '',
									'confirmed'		=> '',
									'confirmedPlayers' 	  => '0',
									'notConfirmedPlayers' => '',
									'opponent'		=> '',
									'locationName'  => '',
									'streetAddress' => ''
									);
									
	protected $_primaryKey = 'gameID';


	public function __construct($resultRow = false)
	{
		if ($resultRow) {
			$this->setAttribs($resultRow);
			$this->getPark()->setAttribs($resultRow);
		}
				
	}
	
	public function getPark()
	{
		if (empty($this->_attribs['park'])) {
			$this->_attribs['park'] = new Application_Model_Park();
		} else {
			$this->_attribs['park'];
		}
		return $this->_attribs['park'];
	}
	
	public function getLimitedParkName($limit) {
		
		return $this->getPark()->getLimitedName('parkName', $limit);
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
		
		$matchDescription = 'This game is a <span class="bold medium">' . $match . '</span> match for you.<br><span class="smaller-text medium">The average player in this game is ' . $adverb . ' ' . $better . ' than you.</span>';
		
		if ($match == 'great') {
			$matchDescription = 'You are a near perfect match for the players in this game.';
		}
		
		return $matchDescription;
	}
	
	public function getDay($format = 'l')
	{
		$curDate  = new DateTime();
		$gameDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->date);
		$diff	  = $curDate->diff($gameDate)->days;
		
		if ($diff == 0) {
			// Today
			return 'Today';
		} elseif ($diff == 1) {
			// Tomorrow
			return 'Tomorrow';
		} elseif ($diff < 7) {
			// Under a week, prepend "this"
			return 'This ' . $gameDate->format($format);
		}
		
		return $gameDate->format($format);
	}
	
	public function getHour()
	{
		return date('ga', strtotime($this->date));
	}
	
	public function getShortDate()
	{
		return date('M j', strtotime($this->date));
	}
	
	public function getOpponent()
	{
		if (!$this->hasValue('opponent')) {
			return 'Unknown Team';
		}
		
		return $this->_attribs['opponent'];
	}

	public function countConfirmedPlayers()
	{
		$count = $this->_attribs['confirmedPlayers'];
		
		if (is_array($count)) {
			$count = count($count);
		}
		
		return $count;
	}
	
	public function addConfirmedPlayer($userID)
	{
		if (!is_array($this->_attribs['confirmedPlayers'])) {
			$this->_attribs['confirmedPlayers'] = array();
		}
		
		$player = $this->_attribs['confirmedPlayers'][$userID] = true;
		
		return $player;
	}
	
	public function addNotConfirmedPlayer($userID)
	{
		if (!is_array($this->_attribs['notConfirmedPlayers'])) {
			$this->_attribs['notConfirmedPlayers'] = array();
		}
		
		$player = $this->_attribs['notConfirmedPlayers'][$userID] = true;
		
		return $player;
	}
	
	public function userConfirmed($userID)
	{
		return !empty($this->_attribs['confirmedPlayers'][$userID]);
	}
	
	public function userNotConfirmed($userID)
	{
		return !empty($this->_attribs['notConfirmedPlayers'][$userID]);
	}

}
