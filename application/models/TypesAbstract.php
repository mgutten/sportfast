<?php

class Application_Model_TypesAbstract extends Application_Model_ModelAbstract
{


	public function remove($id)
	{
		if (($type = $this->exists($id, true)) !== false) {
			// id is located within stored values, remove it
			unset($this->_attribs[$this->getTypeOfModel()][$type]);
		} else {
			return false;
		}
	
	}
	
	/**
	 * test if id exists
	 * @params ($id => id to check for
	 *			$key => return key? (boolean) if false, return object)
	 */
	public function exists($id, $key = false)
	{
		$array = $this->getAll();
		$primary = $this->_primaryKey;
		
		foreach ($array as $k => $type) {
			if ($type->_attribs[$primary] == $id) {
					
				if ($key) {
					return $k;
				}
				return $type;
			}
			
		}
		
		return false;
	}
	
	public function random()
	{
		$array = $this->getAll();
		
		return $array[array_rand($array)];
	}
	
	/**
	 * get type name of the model
	 */
	public function getTypeOfModel()
	{
		return strtolower(str_replace('Application_Model_','',get_class($this)));
	}
		
	
}

