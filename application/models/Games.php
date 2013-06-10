<?php

class Application_Model_Games extends Application_Model_TypesAbstract
{
	protected $_mapperClass = 'Application_Model_GamesMapper';
	
	protected $_attribs     = array('games' => '',
									'totalRows' => '');
									
	protected $_primaryKey  = 'gameID';
	
	public function findUserGames($userClass, $options = false, $points = false, $day = false, $hour = false)
	{
		return $this->getMapper()->findUserGames($userClass, $this, $options, $points, $day, $hour);
	}
	
	/**
	 * get games based on $where parameters
	 * @parameters ($where => non-associative array of where expressions to be used in query)
	 */
	public function getGamesNearUser($where, $userClass)
	{
		return $this->getMapper()->getGames($where, $userClass, $this);
	}
	
	/**
	 * add game to array
	 * @params ($resultRow => array or result row object with necessary data,
	 *			$byDay	   => order array by day (boolean))
	 */
	public function addGame($resultRow, $byDay = false)
	{
		if (is_object($resultRow) && !($resultRow instanceof Zend_Db_Table_Row)) {
			// Directly adding already retrieved game
			if ($game = $this->gameExists($resultRow->gameID, 'gameID')) {
				return $game;
			} else {
				$game = $this->_attribs['games'][] = $resultRow;
			}
			
			return $game;
		}
		
		if (!is_array($resultRow)) {
			$resultRow = $resultRow->toArray();
		}
		
		if ($this->hasValue('games')) {
			// Values exist
			if (isset($resultRow['teamGameID'])) {
				if ($game = $this->gameExists($resultRow['teamGameID'], 'teamGameID')) {
					// Check if game already exists in game array
					return $game;
				}
			} elseif (isset($resultRow['gameID'])) {
				if ($game = $this->gameExists($resultRow['gameID'], 'gameID')) {
					// Check if game already exists in game array
					return $game;
				}
			}
		}
		
		/*
		if ($byDay) {
			// Order games by day in array
			$date = date('w', strtotime($resultRow->date));
			echo $date;
			$game = $this->_attribs['games'][$date][] = new Application_Model_Game($resultRow);
		} else {*/
	
			$game = $this->_attribs['games'][] = new Application_Model_Game($resultRow);
			if (isset($resultRow['teamGameID'])) {
				// Change primary key for team game
				$game->setPrimaryKey('teamGameID');
			}
		//}
		
		return $game;
	}
	
	
	/**
	 * find games for Find controller given options
	 */
	public function findGames($options, $userClass, $limit = false)
	{
		return $this->getMapper()->findGames($options, $userClass, $this, $limit);
	}
	
	/**
	 * test if game exists (either team game or pickup game)
	 * @params ($id => id to test for,
	 *			$typeOfID => 'teamGameID' or 'gameID'
	 */
	public function gameExists($id, $typeOfID) {
		if (!$this->hasValue('games')) {
			return false;
		}
		foreach ($this->_attribs['games'] as $game) {
			if (is_array($game)) {
				// Game is array of sub games
				foreach ($game as $innerGame) {
					if ($this->testGameExists($innerGame, $id, $typeOfID)) {
						return $innerGame;
					}
				}
			} else if ($this->testGameExists($game, $id, $typeOfID)) {
				return $game;
			}
				
		}
		
		return false;
	}
	
	public function testGameExists($game, $id, $typeOfID) 
	{
		if (!isset($game->_attribs[$typeOfID])) {
			return false;
		}
		
		if ($game->$typeOfID == $id) {
				// Game exists, return it
				return true;
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
		
		$nextGames = array();
		$time = time();
		foreach ($this->games as $game) {
			$date = strtotime($game->date);
			$diff = $date - $time;
			
			if ($diff < 0) {
				// Past game
				continue;
			}
	
			$nextGames[$diff] = $game;
		}
		
		return reset($nextGames);
	}
	
	/**
	 * get previous games
	 */
	public function getPreviousGames()
	{
		if (!$this->hasValue('games')) {
			return false;
		}
		
		$previousGames = array();
		foreach ($this->games as $game) {
			$date = strtotime($game->date);
			if ($date < time()) {
				// Previous game
				$previousGames[$date] = $game;
			}
		}
		
		
		
		return array_reverse($previousGames);
	}
	
									
}
