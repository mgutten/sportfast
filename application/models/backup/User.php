<?php

class Application_Model_User extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_attribs     = array('userID' 		=> '',
								    'username' 		=> '',
								    'password' 		=> '',
								    'firstName' 	=> '',
								    'lastName' 		=> '',
								    'age' 			=> '',
									'streetAddress' => '',
									'cityID'		=> '',
									'weight' 		=> '',
									'height'		=> '',
									'sex' 			=> '',
									'lastRead' 		=> '',
									'active'		=> '',
									'lastActive'    => '',
									'lastRating'    => '',
									'verifyHash' 	=> '',
									'dob'			=> '',
									'city'			=> '',
									'sports'		=> array(),
									'picture'		=> '',
									'notifications' => '',
									'userLocation'	=> '',
									'games'			=> '',
									'teams'			=> '',
									'groups'		=> '',
									'players'		=> '',
									'changedLocation' => '',
									'messages'		=> '',
									'plus'			=> '',
									'fake'			=> '',
									'joined'		=> '',
									'noEmail'		=> ''  // Do not email when game is created for them
									);

	protected $_primaryKey = 'userID';	
	
	
	public function save($loopSave = true)
	{
		$this->getMapper()->save($this, $loopSave);
		return $this;
	}

	
	/** 
	 * actions to take to log user in and store all necessary info
	 */
	public function login()
	{
		$this->password = '';
		$this->getUserSportsInfo();
		$this->getUserInfo();
		$this->getOldUserNotifications();
		
		return $this;
	}
	
	public function getUserInfo()
	{
		$this->_attribs['teams'] = '';
		$this->_attribs['groups'] = '';
		$this->_attribs['games'] = '';
		$this->_attribs['players'] = '';
		
		$this->getUserTeams();
		$this->getUserFriends();
		//$this->getUserGroups();
		$this->getUserGames();
		return $this;
	}
	
	/**
	 * get last game that user played in that is in the past week
	 */
	public function getLastGame()
	{
		return $this->getMapper()->getLastGame($this->userID);
	}
	
	
	/**
	 * get shorthand (name and id) for user's friends, groups, and teams
	 */
	public function getUserFriendsGroupsTeams()
	{
		return $this->getMapper()->getUserFriendsGroupsTeams($this);
	}
	
	public function getUserRatings()
	{
		return $this->getMapper()->getUserRatings($this);
	}
	
	public function getSubscribedGames()
	{
		return $this->getMapper()->getSubscribedGames($this->userID);
	}

	public function getNextWeekScheduledGames()
	{
		$returnArray = array();
		if ($this->games->hasValue('games')) {
			// User has games scheduled
			$curDate = new DateTime();
			foreach ($this->games->getAll() as $game) {
				if ($game->gameDate->format('U') > (time() + (60*60*24*7))) {
					// Fail safe to prevent games from NEXT month from showing up this month
					continue;
				}
				
				$days = ($game->gameDate->format('j') - $curDate->format('j')); // # of days difference
				if ($days < 7) {
					// Game is happening in next week
					$returnArray[$game->gameDate->format('w')][] = $game;
				}
			}
		}
		
		return $returnArray;
	}

	public function getMessages()
	{
		if (empty($this->_attribs['message'])) {
			// No notifications object set
			$this->_attribs['message'] = new Application_Model_Messages();
		}
		return $this->_attribs['messages'];
	}
	
	/**
	 * get all of user's scheduled games from db
	 */
	public function getUserGames($byDay = true)
	{
		return $this->getMapper()->getUserGames($this, $byDay);
	}
	
	/**
	 * get all of user's teams from db
	 */
	public function getUserTeams()
	{
		return $this->getMapper()->getUserTypes('teams',$this);
	}
	
	/**
	 * get all of user's groups from db
	 */
	public function getUserGroups()
	{
		return $this->getMapper()->getUserTypes('groups',$this);
	}
	
	/**
	 * get all of user's groups from db
	 */
	public function getUserFriends()
	{
		return $this->getMapper()->getUserFriends($this);
	}
	
	/**
	 * Set user rating (skill, sportsmanship, etc) to avg of ratings and initial value (if skill))
	 */
	public function setUserRating($rating, $sportIDOrSportName)
	{
		if (!is_numeric($sportIDOrSportName)) {
			// Is not sportID already
			$sportID = $this->getSport($sportIDOrSportName)->getSportIdByName($sportIDOrSportName);
		} else {
			$sportID = $sportIDOrSportName;
		}
		
		return $this->getMapper()->setUserRating($rating, $sportID, $this->userID);
	}
	
	/**
	 * Get games model
	 * @return games model
	 */
	public function getGames()
	{
		if (empty($this->_attribs['games'])) {
			// No notifications object set
			$this->_attribs['games'] = new Application_Model_Games();
		}
		return $this->_attribs['games'];
	}
	
	/**
	 * Get teams model
	 * @return teams model
	 */
	public function getTeams()
	{
		if (empty($this->_attribs['teams'])) {
			// No notifications object set
			$this->_attribs['teams'] = new Application_Model_Teams();
		}
		return $this->_attribs['teams'];
	}
	
	/**
	 * Get groups model
	 * @return groups model
	 */
	public function getGroups()
	{
		if (empty($this->_attribs['groups'])) {
			// No notifications object set
			$this->_attribs['groups'] = new Application_Model_Groups();
		}
		return $this->_attribs['groups'];
	}
	
	/**
	 * Get friends 
	 * @return users model
	 */
	public function getPlayers()
	{
		if (!$this->hasValue('players')) {
			// No notifications object set
			$this->_attribs['players'] = new Application_Model_Users();
		}
		return $this->_attribs['players'];
	}

	public function getNotifications()
	{
		if (empty($this->_attribs['notifications'])) {
			// No notifications object set
			$this->_attribs['notifications'] = new Application_Model_Notifications($this);
			$this->_attribs['notifications']->lastRead = $this->lastRead;
		}
		return $this->_attribs['notifications'];
	}
	
	public function getOldUserNotifications()
	{
		return $this->notifications->getUserNotifications();
	}
	
	
	public function getNewUserNotifications()
	{
		return $this->notifications->getUserNotifications(true);
	}
	
	public function resetNewNotifications()
	{
		$this->notifications->resetNewNotifications();
		return $this;
	}
	
	
	public function getHisOrHer()
	{
		if (strtolower($this->sex) == 'm') {
			return 'his';
		} else {
			return 'her';
		}
	}
	
	public function getHimOrHer()
	{
		if (strtolower($this->sex) == 'm') {
			return 'him';
		} else {
			return 'her';
		}
	}
	
	public function getShortName()
	{
		return $this->firstName . ' ' . $this->lastName[0];
	}
	
	public function getFullName()
	{
		return $this->firstName . ' ' . $this->lastName;
	}
	
	
	public function getHeightInFeet()
	{
		$height = $this->getHeightFeet();
		$feet = $height['feet'];
		$inches = $height['inches'];
		
		return $feet . "' " . $inches . "\"";
	}
	
	public function getHeightFeet()
	{
		$heightInches = $this->height;
		$feet = floor($heightInches/12);
		$inches = ($heightInches - ($feet * 12));
		
		return array('feet' => $feet,
					 'inches' => $inches);
	}
	
	public function getSexFull()
	{
		$sex = $this->_attribs['sex'];
		if ($sex == 'm') {
			$sex = 'Male';
		} else {
			$sex = 'Female';
		}
		
		return $sex;
	}

	
	public function setAgeFromDob()
	{
		$dob = $this->getDobDate();
		$now = new DateTime();
		$interval = $now->diff($dob);
		
		$this->_attribs['age'] = $interval->y;
	}
	
	public function setHeightFromFeetAndInches($feet, $inches)
	{
		$total = $feet * 12;
		$total += $inches;
		
		return $this->height = $total;
	}
		
	
	public function setFirstName($firstName)
	{
		$this->_attribs['firstName'] = ucwords($firstName);
		return $this;
	}
	
	public function setLastName($lastName)
	{
		$this->_attribs['lastName'] = ucwords($lastName);
		return $this;
	}
	
	public function setPhoto($photo)
	{
		$this->_attribs['photo'] = $photo;
		return $this;
	}
	
	public function getSport($sport) 
	{
		$sport = strtolower($sport);
		if (!isset($this->_attribs['sports'][$sport])) {
			$this->_attribs['sports'][$sport] = new Application_Model_Sport();
		}
		return $this->_attribs['sports'][$sport];
	}
	
	public function getSportNames()
	{	
		$returnArray = array();
		
		foreach ($this->sports as $sportName => $model) {
			if ($model->sportID) {
				$returnArray[] = $sportName;
			}
		}
		
		return $returnArray;
	}
	
	public function getDobDate()
	{
		return DateTime::createFromFormat('Y-m-d', $this->dob);
	}
	
	public function getJoinedDate()
	{
		return DateTime::createFromFormat('Y-m-d H:i:s', $this->joined);
	}
	
	public function getSportTypes()
	{
		$returnArray = array();
		foreach ($this->sports as $sport) {
			$sportName = $sport->_attribs['sport'];
			foreach ($sport->types as $type) {
				if (strtolower($type->_attribs['typeName']) == 'pickup' && strtolower($type->_attribs['typeSuffix']) == null) {
					$returnArray[$sportName] = false;
					continue;
				}
				  $innerArray = array();
				  $typeName = $type->_attribs['typeName'];
				  
				  $suffix = $type->_attribs['typeSuffix'];
				  if ($suffix == 'null') {
					  $suffix = false;
				  }
				  //$innerArray['typeSuffix'] = $suffix;
				  $returnArray[$sportName][$typeName][$suffix] = true;
			}
		}
		
		return $returnArray;
	}
		
	
	public function sortSportsByOverall()
	{
		if ($this->hasValue('sports')) {
			// There are matches stored, sort them
			uasort($this->_attribs['sports'], array('Application_Model_Sports','overallSort'));
			return $this->_attribs['sports'];
		} else {
			return false;
		}
	}
	
	public function getCity()
	{
		if (!is_object($this->_attribs['city'])) {			
			$this->_attribs['city'] = new Application_Model_City();
		}
		return $this->_attribs['city'];
	}
	
	/*
	public function setCity($city)
	{
		$this->_attribs['city'] = new Application_Model_City();
		$this->_attribs['city']->city = $city;
		
		return $this;
	}
	*/
	
	public function getLocation()
	{
		if (empty($this->_attribs['userLocation'])) {
			
			$this->_attribs['userLocation'] = new Application_Model_Location();
		}
		return $this->_attribs['userLocation'];
	}
	
	public function setLocation($location) {
		$this->_attribs['userLocation'] = $location;
		
		return $this;
	}
	
	public function getUserSportsInfo()
	{
		return $this->getMapper()->getUserSportsInfo($this->userID,$this);
	}
	
	public function getUserBy($column, $value)
	{
		return $this->getMapper()->getUserBy($column, $value, $this);
	}
	
	public function getUserByID($userID)
	{
		return $this->getMapper()->getUserBy('u.userID', $userID, $this);
	}
	
	public function setLastReadCurrent()
	{
		$this->_attribs['lastRead'] = date("Y-m-d H:i:s", time());
		return $this;
	}
	
	public function setLastActiveCurrent()
	{
		$this->_attribs['lastActive'] = date("Y-m-d H:i:s", time());
		return $this;
	}
	
	public function setLastRatingCurrent()
	{
		$this->_attribs['lastRating'] = date("Y-m-d H:i:s", time());
		return $this;
	}
	
	public function hasProfilePic()
	{
		if ($this->getProfilePic('small') == '/images/users/profile/pic/small/default.jpg') {
			return false;
		} else {
			return true;
		}
	}
	
	public function hasSport($sport)
	{
		$sport = strtolower($sport);
		
		if (isset($this->_attribs['sports'][$sport])) {
			
			if ($this->getSport($sport)->sportID != '') {
				// Sport is set AND has a sportID (wasn't accidentally set with getSport
				return true;
			} else {
				$this->unsetSport($sport);
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function getProfilePic($size, $userID = false, $type = 'users') 
	{
		return parent::getProfilePic($size, $this->userID, $type);
	}
	
	public function getBoxProfilePic($size, $type = 'users', $class = '', $outerClass = '', $userID = false) 
	{
		return parent::getBoxProfilePic($size, $this->userID, $type, $class, $outerClass);
	}
	
	public function setChangedLocation($value) 
	{
		$this->_attribs['changedLocation'] = $value;
		return $this;
	}
	
	/**
	 * get time that user was last active in a str
	 */
	public function getLastActiveFromNow()
	{
		$date = $this->_attribs['lastActive'];
		return parent::getTimeFromNow($date, 30);
	}
	
	/**
	 * check whether user wants teams (to play in league) or not
	 */
	public function wantsTeams()
	{
		foreach ($this->sports as $key => $sport) {
			if (isset($sport->_attribs['formats']['league'])) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * reset stored user's location and city to home
	 */
	public function resetHomeLocation()
	{
		$this->getMapper()->resetHomeLocation($this);
	}
	
	
	public function unsetSport($sportName) 
	{
		$sportName = strtolower($sportName);
		unset($this->_attribs['sports'][$sportName]);
	}
	
	/**
	 * remove sport from db
	 */
	public function removeSport($sportName, $andGames = true)
	{
		$sportName = strtolower($sportName);
		$sport = $this->getSport($sportName);
		$sportID = $sport->sportID;
		
		$this->unsetSport($sportName);
		
		$this->getMapper()->removeSport($this->userID, $sportID, $andGames);
	}
	
	/**
	 * remove friend both in db and in players category
	 */
	public function removeFriend($friendUserID)
	{
		$this->players->remove($friendUserID);
		
		return $this->getMapper()->removeFriend($friendUserID, $this->userID);
	}
	
	/**
	 * verify user based on input hash (for signup verifyHash)
	 */
	public function verify($hash)
	{
		return $this->getMapper()->verify($hash);
	}
	
	/**
     * Create a hash (encrypt) of a plain text password.
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
    public function hashPassword($password) {
        return $this->getMapper()->hashPassword($password);
    }
 
    /**
     * Compare the plain text password with the $hashed password.
     *
     * @params ($password => password to check
     * 			$hash => the hashed password
     * 	 		$user_id => the user row ID)
     * @return bool True if match, false if no match.
     */
    public function checkPassword($password, $hash, $user_id = '') {
		
       return $this->getMapper()->checkPassword($password, $hash, $user_id);
    }



}

