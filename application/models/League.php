<?php

class Application_Model_League extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_LeaguesMapper';
	protected $_attribs     = array('leagueID' 		=> '',
									'leagueName' 	=> '',
									'city'			=> '',
									'leagueLevelID' => '',
									'leagueLevel'	=> '',
									'minSkill'		=> '',
									'maxSkill'		=> '',
									'sport'			=> '',
									'sportID'		=> ''
									);
									
}
