<?php

class Application_Model_MessagesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Messages';
	
	/**
	 * Count any new messages
	 * @params($userID   => userID)
	 */
	
	public function countNewUserMessages($userID)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->from(array('m'  => 'messages'),
					  array('COUNT(receivingUserID) AS newMessages'))
			   ->where('receivingUserID = ?', $userID)
			   ->where('`m`.`read` = ?', '0');
		
 
		$results = $table->fetchRow($select);
		
		return $results->newMessages;
	}
	
	/**
	 * get messages for team's wall
	 * @params ($teamID	=> id of team to search for,
	 *			$savingClass => messages model 
	 */
	 public function getTeamMessages($teamID, $savingClass)
	 {
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('tm'  => 'team_messages'))
			   ->join(array('u' => 'users'),
			   		 'tm.userID = u.userID')
			   ->where('tm.teamID = ?', $teamID)
			   ->order('tm.dateHappened DESC');
			   
		$messages = $table->fetchAll($select);
		
		foreach ($messages as $message)
		{
			$savingClass->addMessage($message);
		}
		
		return $savingClass;
	 }	
}
		
			   
