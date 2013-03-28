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
	
}
		
			   
