<?php

class Application_Model_SportRating extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_RatingsMapper';
	
	protected $_attribs     = array('userSportRatingID'  => '',
									'sportRatingID'	=> '',
									'sportID'		=> '',
									'sport'			=> '',
									'value'			=> '',
									'lastChange'  	=> '',
									'lastChanged'	=> '',
									'ing'			=> '',
									'description'	=> '',
									'ed'			=> '',
									'er'			=> ''
									);
									
	protected $_primaryKey = 'userSportRatingID';
	protected $_dbTable = 'Application_Model_DbTable_UserSportRatings';

	
	public function getIcon($size, $color = 'medium')
	{
		return '/images/sports/skills/' . $size . '/' . $color . '/' . $this->ing . '.png';
	}
	
	
	/**
	 * get str "oldGameID" or "teamGameID"
	 */
	public function getIDType()
	{
		if ($this->hasValue('oldGameID')) {
			return 'oldGameID';
		} else {
			return 'teamGameID';
		}
	}
	
	/**
	 * get str "oldGameID" or "teamGameID"
	 */
	public function getTypeID()
	{
		if ($this->hasValue('oldGameID')) {
			return $this->_attribs['oldGameID'];
		} else {
			return $this->_attribs['teamGameID'];
		}
	}
		
}
	
