<?php

class Application_Model_Rating extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_RatingsMapper';
	
	protected $_attribs     = array('userRatingID'  => '',
									'skill'		    => '',
									'sportsmanship' => '',
									'attendance'	=> '',
									'skillValue'	=> '',
									'sportsmanshipValue' => '',
									'attendanceValue'	 => '',
									'sport'			=> '',
									'sportID'		=> '',
									'skiller'		=> '',
									'skilling'		=> ''
									);
									
	protected $_primaryKey = 'teamID';
	
}
