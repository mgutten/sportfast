<?php

class Application_Model_RelativeRating extends Application_Model_SportRating
{
	protected $_mapperClass = 'Application_Model_SportRatingsMapper';
	
	protected $_attribs     = array('userRelativeRatingID'  => '',
									'actingUser'	=> '',
									'winningUser'	=> '',
									'losingUser'	=> '',
									'actingUserID'	=> '',
									'winningUserID'	=> '',
									'losingUserID'	=> '',
									'sportRatingID'	=> '',
									'sportRating'	=> '',
									'valueGained' 	=> '',
									'losingUserRating' => '',
									'oldGameID'		=> '',
									'teamGameID'	=> '',
									'game'			=> '',
									'dateHappened'	=> '',
									'locked'		=> '',
									'dateUnlocked'	=> '',
									'unlockDate'	=> ''
									);
									
	protected $_primaryKey = 'userRelativeRatingID';
	protected $_dbTable = 'Application_Model_DbTable_UserSportRatings';

	public function getTimeFromNow($date = false, $maxDays = 14)
	{
		return parent::getTimeFromNow($this->dateHappened);
	}

	public function setDateHappened($date)
	{
		$this->_attribs['dateHappened'] = $date;
		$this->_attribs['date'] = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
		return $this;	
	}
	
	public function setDateUnlocked($date)
	{
		if (is_null($date)) {
			$date = '2050-01-01 00:00:00';
		}
		$this->_attribs['dateUnlocked'] = $date;
		$this->_attribs['unlockDate'] = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
		return $this;	
	}
}