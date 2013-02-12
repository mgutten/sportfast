<?php

abstract class Application_Model_MapperAbstract
{
	
	protected $_dbTable;
	protected $_dbTableClass;	
	
	public function save($savingClass)
	{			
		$data = array();
		$columns = $savingClass->getAttribs();

		foreach($columns as $column => $value) {
			$data[$column] = $value;
		}
		
		$primaryColumn = $savingClass->primaryKey;
		$primaryKey = $data[$primaryColumn];
		if ($primaryKey === null) {
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array($primaryColumn . ' = ?' => $primaryKey));
		}

	}
	
	public function find($id, Application_Model_ModelAbstract $modelClass)
	{
		$result = $this->getDbTable()->find($id);
		
		if (count($result) == 0) {
			return;
		}
		
		$row = $result->current();
		
		$modelClass->setUsername($row->username);
		return $modelClass;
		
	}
	
	public function fetchAll($storageClass)
	{
		$resultSet 	= $this->getDbTable()->fetchAll();
		$entries 	= array();
		foreach ($resultSet as $row) {
			$entry 	= new $storageClass();
				$entry->setUsername($row->username)
						->setID($row->id);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function setDbTable($dbTable)
	{
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}
	
	public function getDbTable()
	{
		if ($this->_dbTable === null) {
			$this->_dbTable = new $this->_dbTableClass();
		}
		return $this->_dbTable;
	}
	

}