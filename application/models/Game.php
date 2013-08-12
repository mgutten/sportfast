<?php

class Application_Model_Game extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GamesMapper';
	
	protected $_attribs     = array('gameID' 	    => '',
									'teamGameID'	=> '',
									'park' 			=> '',
									'parkID'		=> '',
									'parkName'		=> '',
									'backupParkID'  => '',
									'backupParkName'=> '',
									'public'		=> '',
									'sport'			=> '',
									'sportID' 		=> '',
									'rosterLimit' 	=> '',
									'minPlayers'	=> '',
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
									'streetAddress' => '',
									'winOrLoss'		=> '',
									'gameDate'		=> '',
									'teamID'		=> '',
									'leagueLocationID'	=> '',
									'players'		=> '',
									'type'			=> '',
									'typeID'		=> '',
									'messages'		=> '',
									'captains'		=> '',
									'subscribers'	=> '',
									'city'			=> '',
									'cityID'		=> '',
									'canceled'		=> '',
									'cancelReason'  => '',
									'remove'		=> '',
									'sportfastCreated' => '',
									'doNotEmail'    => ''
									);
									
	protected $_primaryKey = 'gameID';
	protected $_dbTable   = 'Application_Model_DbTable_Games';


	public function __construct($resultRow = false)
	{
		if ($resultRow) {
			$this->setAttribs($resultRow);
			$this->type->setAttribs($resultRow);
			$this->getPark()->setAttribs($resultRow);
		}
				
	}
	
	public function getSportTypeID($sportID, $typeName, $typeSuffix = false) {
		if (!$typeSuffix) {
			$typeSuffix = 'null';
		} 
	
		return $this->getMapper()->getForeignID('Application_Model_DbTable_SportTypes', 'typeID', array('sportID' => $sportID,
																									   'typeName' => $typeName,
																									   'typeSuffix' => $typeSuffix));
	}
	
	/**
	 * get captains from db
	 */
	public function getGameCaptains()
	{
		return $this->getMapper()->getGameCaptains($this->gameID);
	}
	
	public function updateCaptains()
	{
		return $this->getMapper()->updateCaptains($this);
	}
	
	/**
	 * add user to game in db
	 */
	public function addUserToGame($gameID, $userID)
	{
		return $this->getMapper()->addUserToGame($gameID, $userID);
	}
	
	
	/**
	 * get all players for the game
	 * @params ($onlyReal => return only real players? (boolean))
	 */
	public function getGamePlayers($onlyReal = false)
	{
		return $this->getMapper()->getGamePlayers($this, $onlyReal);
	}
	
	/**
	 * get history data for chart of subscribe game
	 * @returns array of game models in order of most recent to least recent
	 */
	public function getHistoryData($interval = 6, $userID = false)
	{
		$gameID = $this->gameID;
		if ($this->recurring != '1') {
			// Not recurring
			return false;
		}
		
		$data = $this->getMapper()->getHistoryData($gameID, $interval, $userID);
		
		return $data;
	}
		
		
																			   
	
	public function getProfilePic($size, $id = false, $type = false)
	{
		if ($this->_primaryKey == 'gameID') {
			// Is pickup game
			$type = 'parks';
			$id = $this->parkID;
		} else {
			// Is team game
			$type = 'teams';
			$id = $this->teamID;
		}
		
		return parent::getProfilePic($size, $id, $type);
	}
	
	public function getAverage($rating)
	{
		$finalRating = 0;
		$totalCount  = 0;
		if (!$this->hasValue('players')) {
			// There are no players
			return $finalRating;
		}
		
		$sport = $this->sport;
		foreach ($this->players->users as $player) {
			$finalRating += $player->getSport($sport)->$rating;
			$totalCount++;
		}
		
		return floor($finalRating/$totalCount);
	}


	public function setPrimaryKey($key)
	{
		if ($key == 'teamGameID') {
			parent::setPrimaryKey($key);
			$this->setDbTable('Application_Model_DbTable_TeamGames');
		}
		return $this;
	}
	
	public function getGameByID($gameID)
	{
		return $this->getMapper()->getGameByID($gameID, $this);
	}
	
	public function searchDbForLeagueLocation($locationName = false, $address = false, $cityID = false)
	{
		return $this->getMapper()->searchDbForLeagueLocation($locationName, $address, $cityID);
	}
	
	public function updateLeagueLocation($locationID, $data)
	{
		return $this->getMapper()->updateLeagueLocation($locationID, $data);
	}
	
	public function getMessages()
	{
		if (!$this->hasValue('messages')) {
			$this->_attribs['messages'] = new Application_Model_Messages();
			$this->_attribs['messages']->setParent($this);
		}
		
		return $this->_attribs['messages'];
	}

	
	public function getType()
	{
		if (!$this->hasValue('type')) {
			$this->_attribs['type'] = new Application_Model_SportType();
		}
		
		return $this->_attribs['type'];
	}

	public function getPlayers()
	{
		if (!$this->hasValue('players')) {
			$this->_attribs['players'] = new Application_Model_Users();
		}
		
		return $this->_attribs['players'];
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


	/**
	 * get reason for cancelation
	 * @params ($text => should text be returned if nothing is stored in value?)
	 */
	public function getCancelReason($text = false)
	{
		if ($this->hasValue('cancelReason')) {
			return ucfirst($this->_attribs['cancelReason']);
		} elseif ($text) {
			return 'No reason given.';
		} else {
			return false;
		}
	}
	
	/**
	 * get game title (ie pickup basketball, or singles tennis match)
	 * @params ($mail => will this be used in email, ie should it have inline css instead of classes?))
	 */
	public function getGameTitle($mail = false)
	{
		// if want bolded prefix, change class to inherit
		if ($mail) {
			// Inline css
			$css = 'style="font-size:inherit;font-color:inherit;font-weight:normal"';
		} else {
			$css = 'class="darkest"';
		}
		
		$gameTitle = '<span ' . $css . '>' . $this->type->typeName . '</span> ' . $this->sport;
		if ($this->type->hasValue('typeSuffix')) {
			$gameTitle .= ' <span ' . $css . '>' . $this->type->typeSuffix . '</span>';
		}
		return $gameTitle;
	}
	
	public function getTotalPlayers()
	{
		if (!$this->hasValue('totalPlayers')) {
			return '0';
		}
		
		return $this->_attribs['totalPlayers'];
	}
	
	public function getLimitedParkName($limit) {
		
		return $this->getPark()->getLimitedName('parkName', $limit);
	}
	
	public function getSport()
	{
		return ucwords($this->_attribs['sport']);
	}
	
	public function getBackupParkName()
	{
		if (!$this->hasValue('backupParkName')) {
			return 'None';
		} else {
			return ucwords($this->_attribs['backupParkName']);
		}
	}
	
	
	public function isGameOn()
	{
		if ($this->totalPlayers >= $this->minPlayers) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isPublic()
	{
		if ($this->_attribs['public'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isRecurring()
	{
		if ($this->_attribs['recurring'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isPickup()
	{
		if ($this->hasValue('gameID')) {
			return true;
		}
		return false;
	}
	
	public function isTeamGame()
	{
		if ($this->hasValue('teamGameID')) {
			return true;
		}
		return false;
	}
	
	public function getMatchName()
	{
		if (empty($this->match)) {
			// Match not set
			$diff = abs($this->skillDifference);
			if ($diff < 4) {
				// Avg skill of game and user is close, great match
				$this->match = 'great';
			} elseif ($diff < 9) {
				$this->match = 'good';
			} elseif ($diff < 14) {
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
		$curDate  = new DateTime('now');
		$gameDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->date);
		$diff	  = $curDate->diff($gameDate, false);
		$posOrNeg = $diff->format("%R");
		$dayDiff  = $diff->format("%a");
		$hourDiff = $diff->format("%h");
		$minDiff  = $diff->format("%i");
		
		if ($hourDiff > 12 && $hourDiff < 24) {
			// Correct for ::diff() failure when event is (eg 2 days ahead but under 48 hours, returns 1 day)
			$dayDiff += 1;
		}
		
		$diff = $posOrNeg . $dayDiff;
		$endNextWeek = (7 - $curDate->format('w')) + 7;
		
		if ($diff <= -7 || $diff >= $endNextWeek) {
			// More than one week forward or back
			return $gameDate->format('M j');
		}
		
		
		$prepend = '';
		if ($format == 'l') {
			// Format is longer day (Tuesday) show more details
			if ($diff == 0) {
				// Today
				return 'Today';
			} elseif ($diff == 1) {
				// Tomorrow
				return 'Tomorrow';
			} elseif ($diff == -1) {
				return 'Yesterday';
			} elseif ($diff < -1 && $diff > -7) {
				// Last
				$prepend = 'Last ';
			} elseif ($diff < 7) {
				// Under a week, prepend "this"
				$prepend = 'This ';
			} elseif ($diff >= 7 && $diff < $endNextWeek) {
				$prepend = 'Next ';
			}
		}
		
		return $prepend . $gameDate->format($format);
	}
	
	
	public function getHour()
	{
		$date = $this->gameDate;
		if ($date->format('i') !== '00') {
			return $this->gameDate->format('g:ia');
		} else {
			return $this->gameDate->format('ga');
		}
	}
	
	public function getPlayersNeeded()
	{
		$difference = $this->minPlayers - $this->totalPlayers;
		
		if ($difference == 1) {
			return $difference . ' player';
		} elseif ($difference <= 0) {
			return false;
		} else {
			return $difference . ' players';
		}
	}

	
	public function getShortDate()
	{
		return $this->gameDate->format('M j');
	}
	
	public function getOpponent()
	{
		if (!$this->hasValue('opponent')) {
			return 'Unknown Team';
		}
		
		return ucwords($this->_attribs['opponent']);
	}

	public function getWinOrLoss()
	{
		if (!$this->hasValue('winOrLoss')) {
			return '?';
		}
		
		return $this->_attribs['winOrLoss'];
	}
	
	public function getFullWinOrLoss()
	{
		$winOrLoss = $this->getWinOrLoss();
		if ($winOrLoss == '?') {
			return '?';
		} elseif ($winOrLoss == 'W') {
			return 'Win';
		} elseif ($winOrLoss == 'L') {
			return 'Loss';
		} else {
			return 'Tie';
		}
		
		//return $this->_attribs['winOrLoss'];
	}
	
	public function setDate($date) {
		$this->_attribs['date'] = $date;
		$this->_attribs['gameDate'] = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
		return $this;
	}
	
	
	public function countConfirmedPlayers()
	{
		$count = $this->_attribs['confirmedPlayers'];
		
		if (is_array($count)) {
			$count = count($count);
		}
		
		return $count;
	}
	
	/**
	 * add player to players list
	 */
	public function addPlayer($resultRow)
	{
		$players = $this->getPlayers();
		return $players->addUser($resultRow);
		
	}
	
	public function addCaptain($userID, $creator = false)
	{
		if (!is_array($this->_attribs['captains'])) {
			$this->_attribs['captains'] = array();
		}
		
		$this->_attribs['captains'][$userID] = $creator;
		return $this;
	}
	
	public function addSubscriber($userID)
	{
		if (!is_array($this->_attribs['subscribers'])) {
			$this->_attribs['subscribers'] = array();
		}
		
		$this->_attribs['subscribers'][$userID] = true;
		return $this;
	}

	/**
	 * test $userID to see if user is captain of team
	 */
	public function isCaptain($userID)
	{

		if (isset($this->_attribs['captains'][$userID])) {
			// User is captain
			return true;
		} else {
			return false;
		}
	}

	public function isCreator($userID) 
	{
		if ($this->_attribs['captains'][$userID]) {
			return true;
		} else {
			return false;
		}
	}

	public function isSubscriber($userID)
	{
		if (isset($this->_attribs['subscribers'][$userID])) {
			// User is subscribed
			return true;
		} else {
			return false;
		}
	}

	public function hasCaptain()
	{
		if ($this->hasValue('captains')) {
			return true;
		} else {
			return false;
		}
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
	
	/**
	 * move player from confirmed to not confirmed or vice versa
	 * @params ($userID => user's id to move,
	 *			$inOrOut => 1 = confirmed, 0 = notconfirmed)
	 */
	public function movePlayerConfirmation($userID, $inOrOut) 
	{
		$userID = (int)$userID;
		$this->confirmed = $inOrOut;
		
		if ($inOrOut) {
			// Now confirmed			
			$this->confirmedPlayers += 1;
		} else {
			// Now not confirmed
			$this->confirmedPlayers -= 1;
		}
		
		return $this;
	}
	
	/**
	 * get correct type name for sport (ie basketball GAME, football GAME, tennis MATCH
	 
	public function getTypeName()
	{
		if ($this->sport == 'Tennis') {
			return 'Match';
		} else {
			return 'Game';
		}
	}*/
}
