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
		if ($this->hasValue('games')) {
			// Values exist
			if ($game = $this->gameExists($resultRow->teamGameID)) {
				// Check if game already exists in game array
				return $game;
			}
		}
		
		if ($byDay) {
			// Order games by day in array
			$date = date('w', strtotime($resultRow->date));
			$game = $this->_attribs['games'][$date][] = new Application_Model_Game($resultRow);
		} else {
			$game = $this->_attribs['games'][] = new Application_Model_Game($resultRow);
		}
		
		return $game;
	}
	
	public function gameExists($id) {
		foreach ($this->_attribs['games'] as $game) {
			if ($game->gameID == $id || $game->teamGameID == $id) {
				// Game exists, return it
				return $game;
			}
		}
		
		return false;
	}
	
	/**
	 * get next game (by date), games should be ordered by date
	 */
	public function getNextGame()
	{
		if (!$this->hasValue('games')) {
			return false;
		}
		
		return reset($this->_attribs['games']);
	}
	
									
}
