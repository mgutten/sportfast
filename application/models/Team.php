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
									'totalPlayers' => '0',
									'skillDifference' 	   => '',
									'averageAttendance'    => '',
									'averageSporstmanship' => '',									
									'averageSkill' => '',
									'match'		   => '',
									'city'		   => '',
									'cityID'	   => '',
									'league'	   => '',
									'players'	   => '',
									'messages'	   => '',
									'games'		   => '',
									'captains'	   => '',
									'wins'		   => '',
									'losses'	   => '',
									'ties'		   => '',
									'minSkill'	   => '',
									'maxSkill'	   => '',
									'minAge'	   => '',
									'maxAge'	   => '',
									'systemCreated' => '',
									'leagues'	   => '',
									'picture'	   => '',
									'remove'	   => '',
									'lastActive'   => ''
									);
									
	protected $_primaryKey = 'teamID';

	public function save($loopSave = false) 
	{
		return $this->getMapper()->save($this, $loopSave);
	}
	
	public function getProfilePic($size, $id = false, $type = 'teams') 
	{
		return '/images/teams/avatars/' . $size . '/' . $this->picture . '.jpg';
		//return parent::getProfilePic($size, $this->teamID, $type);
	}
	
	/**
	 * get team info from db
	 * @params ($teamID => teamID)
	 */
	public function getTeamByID($teamID)
	{
		return $this->getMapper()->getTeamByID($teamID, $this);
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
			$matchDescription = 'You are a near perfect match for this team.';
		}
		
		return $matchDescription;
	}
	
	public function updateCaptains()
	{
		return $this->getMapper()->updateCaptains($this);
	}
	
	
	public function getLeague()
	{
		if (!$this->hasValue('league')) {
			$this->_attribs['league'] = new Application_Model_League();
		}
		
		return $this->_attribs['league'];
	}
	
	public function getPlayers()
	{
		if (!$this->hasValue('players')) {
			$this->_attribs['players'] = new Application_Model_Users();
		}
		
		return $this->_attribs['players'];
	}
	
	public function getGames()
	{
		if (!$this->hasValue('games')) {
			$this->_attribs['games'] = new Application_Model_Games();
		}
		
		return $this->_attribs['games'];
	}
	
	public function getLeagues()
	{
		if (!$this->hasValue('leagues')) {
			$this->_attribs['leagues'] = new Application_Model_Leagues();
		}
		
		return $this->_attribs['leagues'];
	}
	
	public function getMessages()
	{
		if (!$this->hasValue('messages')) {
			$this->_attribs['messages'] = new Application_Model_Messages();
			$this->_attribs['messages']->setParent($this);
		}
		
		return $this->_attribs['messages'];
	}
	
	public function getSport()
	{		
		return ucwords($this->_attribs['sport']);
	}
	
	
	public function addCaptain($userID, $creator = false)
	{
		if (!is_array($this->_attribs['captains'])) {
			$this->_attribs['captains'] = array();
		}
		
		$this->_attribs['captains'][$userID] = $creator;
		return $this;
	}
	
	public function addPlayer($resultRow)
	{
		$players = $this->getPlayers();
		return $players->addUser($resultRow);
		
	}
	
	public function addGame($resultRow)
	{
		$games = $this->getGames();
		$game = $games->addGame($resultRow);
		$game->sport = $this->sport;
		
		return $game;
		
	}
	
	public function addMessage($resultRow)
	{
		return $this->messages->addMessage($resultRow);
	}
	
	
	/**
	 * get team's next game
	 */
	public function getNextGame()
	{
		return $this->games->getNextGame();
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
	
	public function getRecord()
	{
		return $this->wins . '-' . $this->losses . '-' . $this->ties;
	}
	
	public function getTeamName()
	{
		return ucwords($this->_attribs['teamName']);
	}
	
	public function getCity()
	{
		return ucwords($this->_attribs['city']);
	}
			
	
	/**
	 * order players by whether they are confirmed or not
	 */
	public function sortPlayersByConfirmed()
	{
		$nextGame = $this->getNextGame();
		if ($this->hasValue('players')) {
			// There are players stored, sort them
			$players = $this->_attribs['players']->_attribs['users'];
			
			$playerArray = $undecided = $notConfirmed = array();
			
			foreach ($players as $player) {
				
				if ($nextGame->userConfirmed($player->userID)) {
					// User is confirmed

					array_unshift($playerArray, $player);
				} elseif ($nextGame->userNotConfirmed($player->userID)) {
					// User is not going
					array_unshift($notConfirmed, $player);
				} else {
					array_push($undecided, $player);
				}
			}
			
			foreach ($undecided as $player) {
				array_push($playerArray, $player);
			}
			
			foreach ($notConfirmed as $player) {
				array_push($playerArray, $player);
			}
			
			$players = $this->_attribs['players'];
			$players->users = $playerArray;
			
			return $this->_attribs['players'];
		} else {
			return false;
		}
	}
	
	private static function sortByConfirmed ($a, $b)
	{
		
		if ($nextGame->userConfirmed($a->userID)) {
			// User is confirmed
			return 1;
		} elseif ($nextGame->userNotConfirmed($a->userID)) {
			return -1;
		} else {
			return 0;
		}
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
		if (isset($this->_attribs['captains'][$userID])) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * test if team is public
	 */
	public function isPublic()
	{
		if ($this->_attribs['public'] == '1') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * test if team has captain
	 */
	public function hasCaptain()
	{
		if ($this->hasValue('captains')) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * delete (and move) all of team's games
	 */
	public function deleteGames()
	{
		$this->getMapper()->moveTeamGames($this->teamID);
		
		$this->getMapper()->deleteTeamGames($this->teamID);
	}
		
		
		
	
}
