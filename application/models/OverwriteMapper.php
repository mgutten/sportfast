<?php
class Application_Model_OverwriteMapper extends Application_Model_MapperAbstract
{

	public function save($savingClass)
	{		
		if ($savingClass->getDbTable()) {
			// dbTable is set
			$this->setDbTable($savingClass->getDbTable());
		}
		
		$table   = $this->getDbTable();
		$attribs = $savingClass->getAttribs();
		$overwrite = $savingClass->getOverwriteKeys();
				
		if (!empty($overwrite)) {
			// overwriteKeys is set, DELETE rows with same values
			$where = array();
			foreach($savingClass->overwriteKeys as $key) {
				$where[] = $table->getAdapter()->quoteInto($key . ' = ?', $savingClass->$key);
			}

			$table->delete($where);
		}
		
		parent::save($savingClass);
				
	}

	
}
