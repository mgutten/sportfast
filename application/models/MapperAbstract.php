<?php

abstract class Application_Model_MapperAbstract
{
	
	protected $_dbTable;
	protected $_dbTableClass;	
	
	public function save($savingClass, $loopSave = true)
	{			
		
		if ($savingClass->getDbTable()) {
			// dbTable is set
			$this->setDbTable($savingClass->getDbTable());
		}
		
		$data    = array();
		$table   = $this->getDbTable();
		$columns = $table->info(Zend_Db_Table_Abstract::COLS);
		$attribs = $savingClass->getAttribs();
		$models  = array();
		
		
		/*
		foreach ($columns as $column => $value) {
				
			$data[$value] = $savingClass->$value;
		}
		*/
		foreach ($attribs as $column => $value) {
			// Loop through savingClass attributes and determine what is an individual object
			// and what is a valid column for this table
			if (is_object($value)) {
				array_push($models, $value);
				continue;
			} elseif(is_array($value)) {
				@$firstValue = array_shift(array_values($value));
				
				if (is_object($firstValue)) {
					// First value of array is object, valid
					foreach($value as $key => $val) {
						array_push($models, $val);
					}
				} elseif (is_array($firstValue)) {
					// Array of arrays (e.g. Sport Model availabilities Su => 12 => obj
					foreach($value as $key => $val) {
						foreach($val as $obj) {
							array_push($models, $obj);
						}
					}
				} else {
					// Invalid array with no objects, just values
					throw new Exception('Invalid attribute array without being objects at: ' . $column);
					
				}
				continue;
			} elseif (!in_array($column, $columns)) {
				continue;
			} elseif ((strpos($value, 'POINT(') !== false) && (get_class($savingClass) == 'Application_Model_Location')) {
				// This attrib is a location 
				$data[$column] = new Zend_Db_Expr("GeomFromText('" . $value . "')");
				continue;
			} elseif (empty($value)) {
				// Skip empty columns
				continue;
			} elseif ($savingClass instanceof Application_Model_User && $column == 'cityID' && !empty($savingClass->changedLocation)) {
				// Is user class and location has been changed temporarily, do not change cityID for user row, skip
				continue;
			}
			
			$data[$column] = strtolower(trim($savingClass->$column));
			
		}
		
		
		$primaryColumn = $table->info('primary');
		$primaryColumn = $primaryColumn[1];
		$primaryKey    = $savingClass->$primaryColumn;


		if (empty($primaryKey)) {
			// No primary key set, create row
			$primaryVal = $this->getDbTable()->insert($data);
			// Update savingClass primary key
			$savingClass->$primaryColumn = $primaryVal;
	
		} else {
			// Primary key is already set, row exists, update it
			$this->getDbTable()->update($data, array($primaryColumn . ' = ?' => $primaryKey));
		}
		
		if ($loopSave) {
			// Loop through children objects and save as well
			foreach ($models as $key => $model) {
				$modelAttribs = $model->getAttribs();
				
				foreach ($attribs as $attrib => $val) {
					
					if (array_key_exists($attrib, $modelAttribs)) {
						// Both parent class ($savingClass) and child class have same columns, set child to parents
						$model->$attrib = $savingClass->$attrib;
					}
					
				}
				
	
				$model->save();
			}
		}

	}
	
	public function getForeignID($table, $column, $whereValues)
	{
		$this->setDbTable($table);
		$table	   = $this->getDbTable();
		$tableName = $table->info('name');
		$select    = $table->select()
						   ->from($tableName, $column);
		foreach ($whereValues as $columns => $value) {
			if (strtolower($value) == 'null') {
				$select->where($columns . ' IS NULL');
				continue;
			}
			$select->where($columns . ' = ?', $value);
		}
		$select->limit(1);
		
		
		$result = $table->fetchRow($select);  
		
		return $result->$column;
	}


	
	public function getColumnValue($column, $value)
	{
		$table  = $this->getDbTable();
		$select = $table->select($column);
		$select->where($column . ' = ?', $value)
			   ->limit(1);
		$result = $table->fetchRow($select);
		
		return $result;
		
	}
	
	public function find($id, $column, Application_Model_ModelAbstract $modelClass)
	{
		$table  = $this->getDbTable();
		$select = $table->select()
						->where($column . ' = ?', $id)
						->limit(1);
				
		$result = $table->fetchRow($select);
		
		if (count($result) == 0) {
			return;
		}
		
		//$modelClass->setUsername($row->username);
		$modelClass->setAttribs($result);
		
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
			throw new Exception('Invalid table data gateway provided: ' . $dbTable);
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