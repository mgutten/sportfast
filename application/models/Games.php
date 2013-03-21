<?php

class Application_Model_Games extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_GamesMapper';
	
	protected $_attribs     = array('games' => '');
	
	public function findUserGames($userClass, $options = false, $points = false)
	{
		return $this->getMapper()->findUserGames($userClass, $this, $options, $points);
	}
	
	/**
	 * add game to array
	 * @params ($resultRow => array or result row object with necessary data,
	 *			$byDay	   => order array by day (boolean))
	 */
	public function addGame($resultRow, $byDay = false)
	{
		if ($byDay) {
			// Order games by day in array
			$date = date('w', strtotime($resultRow->date));
			$game = $this->_attribs['games'][$date][] = new Application_Model_Game($resultRow);
		} else {
			$game = $this->_attribs['games'][] = new Application_Model_Game($resultRow);
		}
		return $game;
	}
									
}
