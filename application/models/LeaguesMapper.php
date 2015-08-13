<?php

class Application_Model_LeaguesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Leagues';


	/**
	 * find upcoming leagues that are a certain sport and in or around cityID
	 * @parameters ($sports => array of sports,
	 *				$cityID => user's cityID)
	 */
	public function findLeagues($sports, $cityID, $savingClass)
	{
		$table  = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('l' => 'leagues'))
			   ->join(array('ll' => 'league_levels'),
			   		  'll.leagueID = l.leagueID')
			   ->where('l.cityID IN ' . $this->getCityIdRange($cityID));
			
		$statement = '(';
		$counter = 0;   
		foreach ($sports as $sport) {
			if ($counter != 0) {
				$statement .= ' OR ';
			}
			
			$statement .= '(ll.sport = "' . $sport . '")';
		}
		$statement .= ')';
		$counter++;
		
		$select->where($statement);
		
		$leagues = $table->fetchAll($select);
		
		foreach ($leagues as $league) {
			if ($leagueModel = $savingClass->leagueExists($league->leagueID)) {
				$leagueModel->addLeagueLevel($league);
			} else {
				$leagueModel = $savingClass->addLeague($league);
				$leagueModel->addLeagueLevel($league);
			}
		}
		
		return $savingClass;
				
			
			
	}
		

}