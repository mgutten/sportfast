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
		$name = $newName = $this->$attrib;

		if (strlen($name) > $limit) {
			$newName = '';
			for ($i = 0; $i < $limit - 2; $i++) {
				$newName .= $name[$i];
			}
			$newName .= '...';
		} 
		
		return $newName;
	}
	
	/**
	 * Get difference of time between $date and now
	 * @params ($date => datetime,
	 *			$maxDays => max # of days to show "x days ago" before show date)
	 * @returns str
	 */
	public function getTimeFromNow($date, $maxDays)
	{
		$date = strtotime($date);
		$now  = time();
		$diff = $now - $date;
		
		if (($seconds = $diff) < 60) {
			$time = $seconds;
			$post = ($time == 1 ? 'second ago' : 'seconds ago');
		} elseif (($minutes = floor($seconds/60)) < 60) {
			// Under 60 minutes
			$time = $minutes;
			$post = ($time == 1 ? 'minute ago' : 'minutes ago');
		} elseif (($hours = floor($minutes/60)) < 24) {
			// > 60 minutes, under 24 hours
			$time = $hours;
			$post = ($time == 1 ? 'hour ago' : 'hours ago');
		} elseif (($days = floor($hours/24)) < $maxDays) {
			// > 24 hours, under 7 days
			$time = $days;
			$post = ($time == 1 ? 'day ago' : 'days ago');
		}  else {
			// > 6 days, show date
			$time = date ('l, M j',$date);
			$post = '';
		}
		
		return $time . ' ' . $post;
		
	}
	
	
	/**
	 * Get all of main attribs individual models
	 * @returns one dimension array of models
	 */
	public function getAll()
	{
		$returnArray = array();
		$mainAttrib  = strtolower(str_replace('Application_Model_', '', get_class($this)));
		
		if (empty($this->_attribs[$mainAttrib])) {
			return $returnArray;
		}
		
		foreach ($this->_attribs[$mainAttrib] as $outer) {
			if (is_array($outer)) {
				// Inner arrays found, loop
				foreach ($outer as $inner){
					$returnArray[] = $inner;
				}
			} else {
				// No inner array
				$returnArray[] = $outer;
			}
		}
		
		return $returnArray;
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
	
	public function setPrimaryKey($value) 
	{
		$this->_primaryKey = $value;
		
		return $this;
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
		if (!empty($this->_dbTable)) {
			return $this->_dbTable;
		} else {
			return false;
		}
	}
	
	public function setDbTable($dbTable)
	{
		$this->_dbTable = $dbTable;
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
	
	/**
	 * get profile pic in any size for a given model (team, user, group)
	 * @params ($size => tiny, small, medium, large
	 *			$id	  => id to attempt to use to get picture
	 *			$type => plural type that we are searching for)
	 * @returns href for picture (str)
	 */
	public function getProfilePic($size, $id, $type = 'users')
	{
		if ($type == 'logo') {
			// Show logo
			$directory   = '/images/global/logo/logo/' . $size . '.png';
			
			return $directory;
			
		} else {
			// Other type
			$directory   = '/images/' . strtolower($type) . '/profile/pic/' . strtolower($size) . '/';
			$absoluteSrc = PUBLIC_PATH . $directory . $id . '.jpg';
		}
		
		if (!file_exists($absoluteSrc)) {
			// No profile set, get default
			$picture = $directory . 'default.jpg';
		} else {
			$picture = $directory . $id . '.jpg';
		}
		
		return $picture;
	}
	
	/**
	 * get box profile pic in any size for a given model (team, user, group)
	 * @params ($size => tiny, small, medium, large
	 *			$id	  => id to attempt to use to get picture
	 *			$type => plural type that we are searching for)
	 * @returns href for picture (str)
	 */
	public function getBoxProfilePic($size, $id, $type = 'users', $class = '', $outerClass = '')
	{
		$picture = $this->getProfilePic($size, $id, $type);
		
		$output  = "<div class='box-img-container-" . $size . " " . $outerClass . "'>";
		$output .= 		"<img src='" . $picture . "' class='box-img-" . $size . " " . $class . "'/>";
		$output .= "</div>";
		
		return $output;
	}
	
	/**
	 * get sports icon
	 * @params ($size => tiny, small, medium, large
	 *			$type => outline, solid,
	 *			$color => (optional) medium, light, dark)
	 * @returns path to icon
	 */
	public function getSportIcon($sport, $size = 'medium', $type = 'outline', $color = 'medium')
	{
		$path = '/images/global/sports/icons/' . strtolower($size) . '/' . strtolower($type);
		if ($type == 'solid') {
			$path .= '/' . strtolower($color);
		}
		$path .= '/' . strtolower($sport) . '.png';
		
		return $path;
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
	
	public function delete()
	{
		return $this->getMapper()->delete($this);
	}
		
	
	public function getVars()
	{
		return get_object_vars($this);
	}
		

}