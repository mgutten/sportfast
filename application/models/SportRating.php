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
}
	
