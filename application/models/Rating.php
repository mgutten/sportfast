<?php

class Application_Model_Rating extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_RatingsMapper';
	
	protected $_attribs     = array('userRatingID'  => '',
									'parkRatingID'	=> '',
									'quality'		=> '',
									'crowd'			=> '',
									'success'		=> '',
									'dateHappened'  => '',
									'date'			=> '',
									'comment'		=> '',
									'skill'		    => '',
									'sportsmanship' => '',
									'attendance'	=> '',
									'skillValue'	=> '',
									'sportsmanshipValue' => '',
									'attendanceValue'	 => '',
									'sport'			=> '',
									'sportID'		=> '',
									'gameID'		=> '',
									'skiller'		=> '',
									'skilling'		=> '',
									'bestSkill'		=> '',
									'user'			=> '',
									'userID'		=> '',
									'receivingUserID' => '',
									'givingUserID'	  => '',
									'parkID'		=> '',
									'skillRatingName' => '',
									'sportsmanshipRatingName' => '',
									'type'			=> '',
									'incorrect'		=> ''
									);
									
	protected $_primaryKey = 'userRatingID';
	protected $_dbTable = 'Application_Model_DbTable_UserRatings';
	
	
	public function save($loopSave = false) {
		$this->getMapper()->save($this, $loopSave);
	}
	
	public function __construct($resultRow = false) {
		
		$user = new Application_Model_User($resultRow);
		$this->user = $user;
		
		parent::__construct($resultRow);
	}
	
	/**
	 * retrieve number value of rating level
	 * @params ($level => 'beginner', 'good', etc
	 *			$type => 'user', 'park',
	 *			$ratingType => 'skill', 'sportsmanship'
	 */
	public function getValueFromRating($level, $type, $ratingType)
	{
		return $this->getMapper()->getValueFromRating($level, $type, $ratingType);
	}
	
	public function getUnsuccessfulParkRating($parkID, $gameID)
	{
		return $this->getMapper()->getUnsuccessfulParkRating($parkID, $gameID);

	}
	
	public function setPark()
	{
		$this->_primaryKey = 'parkRatingID';
		$this->_dbTable = 'Application_Model_DbTable_ParkRatings';
	}
	
	public function setDateHappenedCurrent()
	{
		//$this->_attribs['dateHappened'] = date("Y-m-d H:i:s", time());
		return $this->setCurrent('dateHappened');
	}
	
	public function isUser()
	{
		if ($this->hasValue('userRatingID')) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getStarWidth($attrib)
	{
		$average = $this->$attrib;
		
		//$rounded = round($average / 10); 
		$rounded = $average * 20; // 5 stars, so multiply by 20 to convert to 100% scale
		
		return $rounded;
	}
	
	public function getQuotedComment()
	{
		if (!$this->hasValue('comment')) {
			// No comment
			return '<span class="light">No comment was given.</span>';
		} else {
			return '"' . $this->_attribs['comment'] . '"';
		}
	}
	
	public function setDateHappened($date)
	{
		$this->_attribs['dateHappened'] = $date;
		$this->_attribs['date'] = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
		return $this;
	}
	
	public function getTimeFromNow($date = false, $maxDays = false)
	{
		return parent::getTimeFromNow($this->dateHappened);
	}
	
	public function getSport()
	{
		return ucwords($this->_attribs['sport']);
	}
	
}
