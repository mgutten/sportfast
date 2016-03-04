<?php
class Application_Model_TypesMapperAbstract extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Teams';
	
	/**
	 * Update team's captains
	 */
	public function updateCaptains($savingClass)
	{
		if ($savingClass instanceof Application_Model_Game) {
			// Is game
			$type = 'game';
			$typeID = $savingClass->gameID;
		} else {
			// Is team
			$type = 'team';
			$typeID = $savingClass->teamID;
		}
		
		$this->setDbTable('Application_Model_DbTable_' . ucwords($type) . 'Captains');
		$table   = $this->getDbTable();
		
		if (!$savingClass->hasCaptain()) {
			return false;
		}
		

		$db = Zend_Db_Table::getDefaultAdapter();
		
		$db->delete($type . '_captains', array($type . 'ID = ?' => $typeID));
		
		$insert = "INSERT INTO " . $type . "_captains (" . $type . "ID, userID) VALUES ";
		
		$counter = 0;
		foreach ($savingClass->captains as $captain => $value) {
			if ($counter != 0) {
				$insert .= ',';
			}
			$insert .= '("' . $typeID . '","' . $captain . '")';
			$counter++;
		}
		
		$db->query($insert);
		
		return $savingClass;
	}
}