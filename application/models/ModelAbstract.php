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
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid model property ' . $name);
		}
		$this->$method($value);
	}
	
	public function __get($name)
	{
		$method	= 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid model property ' . $name);
		}
		return $this->$method();
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