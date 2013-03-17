<?php

class Application_Model_Games extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GamesMapper';
	
	protected $_attribs     = array('games' => '');
	
	public function findUserGames($userClass, $options = false)
	{
		return $this->getMapper()->findUserGames($userClass, $this, $options);
	}
	
	public function addGame($resultRow)
	{

		$game = $this->_attribs['games'][] = new Application_Model_Game($resultRow);
		return $game;
	}
									
}
