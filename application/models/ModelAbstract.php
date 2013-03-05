<?php

abstract class Application_Model_ModelAbstract
{

	protected $_mapperClass;	
	protected $_mapper;
	
	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}
	
	public function __set($name, $value)
	{
		//return $this->__call('set' . $name, $value);
		
		$method = 'set' . $name;
		
		if (!method_exists($this, $method)) {
			// Method does not exist
			if (array_key_exists($name, $this->_attribs)) {
				// $name is an attribute of class
				$this->_attribs[$name] = $value;
				return $this->_attribs[$name];
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
		
		if (!method_exists($this, $method)) {
			// Method does not exist
			if (!array_key_exists($name, $this->_attribs)) {
				// Key is not part of attribs, look at rest of class properties
				return $this->$name;
			}
			return $this->_attribs[$name];
		}
		return $this->$method();
		
	}
	/*
	public function __call($method, $args)
	{
		
		if (method_exists($this, $method)) {
			// Method is defined in the class
			return $this->$method();
		}
		
		if ((($methodGet = ltrim('get',$method)) !== $method) &&
					 isset($this->_attribs[lcfirst($methodGet)])) {
			// Property is being called
			return $this->_attribs[lcfirst($methodGet)];
		} elseif ((($methodSet = ltrim('set',$method)) !== $method)) {
			// Property is being set
			$this->_attribs[lcfirst($methodSet)] = $args;
			return $this;
		} else {
			throw new Exception('Call to undefined method or invalid attribute ' . $method);
		}
	}
	*/
	
	
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
		$resultRow = $resultRow->toArray();

		foreach ($resultRow as $key => $attrib) {
			if (!array_key_exists($key, $this->_attribs)) {
				// Not in attribute array
				if (!property_exists($this, $key)) {
					// Not in rest of properties, skip
					continue;
				} 
				// Exists in other properties
				
			}
			
			$this->$key = $attrib;
		}
	}
	
	public function getAttribs()
	{
		return $this->_attribs;
	}
	
	public function getPrimaryKey()
	{
		return $this->_primaryKey;
	}
	
	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if(in_array($method,$methods)) {
				$this->$method($value);
			}
		}
		return $this;
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
	
	public function find($id) 
	{
		$this->getMapper()->find($id, $this);
		return $this;
	}
	
	public function fetchAll() 
	{
		return $this->getMapper()->fetchAll();
	}
	
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