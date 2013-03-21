<?php

abstract class Application_Model_ModelAbstract
{

	protected $_mapperClass;	
	protected $_mapper;
	
	public function __construct($resultRow = false) 
	{
		if ($resultRow) {
			$this->setAttribs($resultRow);
		}
	}
	
	public function hasValue($attrib) {
		$attrib = $this->_attribs[$attrib];
		
		if (is_object($attrib) || $attrib) {
			// Attrib is an object or has a value
			return true;
		} else {
			return false;
		}
	}
	
	public function getIDs($attrib)
	{

		if (is_array($this->_attribs[$attrib])) {
			// Is array, return as array
			$ids = array();
			foreach ($this->_attribs[$attrib] as $obj) {
				if (is_array($obj)) {
					// Sub-array
					foreach($obj as $object) {
						$ids[] = $object->_attribs[$object->_primaryKey];
					}
					continue;
				} else {
					$ids[] = $obj->_attribs[$obj->_primaryKey];
				}
			}
			return $ids;
		}
	}
	
	public function implodeIDs($attrib)
	{
		$ids = $this->getIDs($attrib);
		if (is_array($ids)) {
			$str = implode(',', $ids);
			return $str;
		}
	}
	
	public function getLimitedName($attrib, $limit)
	{
		$name = $this->_attribs[$attrib];
		
		if (strlen($name) > $limit) {
			$name = '';
			for ($i = 0; $i < $limit - 2; $i++) {
				$name .= $this->_attribs[$attrib][$i];
			}
			$name .= '...';
		}
		
		return $name;
	}
	
	public function jsonEncodeChildren($attrib)
	{
		$jsonArray = array();
		if (is_array($array = $this->_attribs[$attrib])) {
			// Values are stored
			foreach ($array as $obj) {
				if (is_object($obj)) {
					// Is object
					$jsonArray[] = $obj->jsonSerialize();
				}
			}
		}
		
		return json_encode($jsonArray);
	}
		
	public function jsonSerialize()
	{
		$jsonArray = array();
		foreach ($this->_attribs as $attrib => $val) {
			if (is_object($val)) {
				$val = $val->jsonSerialize();
			}
			$jsonArray[$attrib] = $val;
		}
				
		return $jsonArray;
	}

	public function __set($name, $value)
	{
		//return $this->__call('set' . $name, $value);
		
		$method = 'set' . $name;
		
		if (!method_exists($this, $method)) {
			// Method does not exist
			if (array_key_exists($name, $this->_attribs)) {
				// $name is an attribute of class
				
				$noUcWords = array('text');
				if (!in_array($name, $noUcWords)) {
					// Uppercase value
					//$value = ucwords($value);
				}
				
				$this->_attribs[$name] = $value;
				return $this;
			} else {
				// $name is not an attribute of class
				throw new Exception('Invalid model property ' . $name . ' for model: ' . get_class($this));
			}
		}
		/*
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid model property ' . $name);
		}
		*/
		$this->$method($value);
		
	}
	
	public function __get($name)
	{
		//return $this->__call('get' . $name, '');

		$method	= 'get' . $name;
		
		if (method_exists($this, $method)) {
			// Method exists
			return $this->$method();
			
		} elseif (!array_key_exists($name, $this->_attribs)) {
				// Key is not part of attribs, look at rest of class properties
				if (!array_key_exists($name,$this->getVars())) {
					return false;
				} else {
					return $this->$name;
				}
		}
		
		// Key exists in attribs, return it
		return $this->_attribs[$name];
		
		
		
	}
	
	
	public function getDbTable()
	{
		if (isset($this->_dbTable)) {
			return $this->_dbTable;
		} else {
			return false;
		}
	}
	
	public function getOverwriteKeys()
	{
		if (isset($this->_overwriteKeys)) {
			return $this->_overwriteKeys;
		}
		
		return false;
	}
	
	public function setAttribs($resultRow)
	{
		if (!is_array($resultRow)) {
			$resultRow = $resultRow->toArray();
		}
		
		foreach ($resultRow as $key => $attrib) {
			
			if (!array_key_exists($key, $this->_attribs)) {
				// Not in attribute array
				if (!property_exists($this, $key)) {
					// Not in rest of properties, skip
					continue;
				}
				// Exists in other properties
				
			}
			if (is_object($this->_attribs[$key])) {
				// Value is set and is object
				continue;
			}

			$this->$key = $attrib;
		}
	}
	
	public function getProfilePic($size, $userID)
	{
		$directory   = '/images/users/profile/pic/' . strtolower($size) . '/';
		$absoluteSrc = PUBLIC_PATH . $directory . $userID . '.jpg';
		
		if (!file_exists($absoluteSrc)) {
			// No profile set, get default
			$picture = $directory . 'no_profile_male.jpg';
		} else {
			$picture = $directory . $userID . '.jpg';
		}
		
		return $picture;
	}
	
	public function getAttribs()
	{
		return $this->_attribs;
	}
	
	public function getPrimaryKey()
	{
		return $this->_primaryKey;
	}	
	
	public function setMapper($mapper)
	{
		if (is_string($mapper)) {
			$mapper = new $mapper();
		}
		if (!$mapper instanceof Application_Model_MapperAbstract) {
			throw new Exception('Invalid mapper class given.' . $mapper);
		}
		$this->_mapper = $mapper;
		return $this;
	}
	
	
	public function getMapper() 
	{
		if ($this->_mapper === null) {
			$this->setMapper($this->_mapperClass);
		}
		return $this->_mapper;
	}
	
	public function find($id, $column) 
	{
		$this->getMapper()->find($id, $column, $this);
		return $this;
	}
	
	public function fetchAll() 
	{
		return $this->getMapper()->fetchAll();
	}
	
	/**
	 * save object
	 * @params (loopSave => loop through child objects and save as well? (boolean))
	 */
	public function save()
	{

		$this->getMapper()->save($this);
		return $this;
	}
	
	public function getVars()
	{
		return get_object_vars($this);
	}
		

}