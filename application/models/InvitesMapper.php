<?php

class Application_Model_InvitesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_GameInvites';
	
	 /**
	  * delete notification either by notificationLogID or by details
	  */
	 public function delete($table, $details = false, $mainID = false) 
	 {
		 $query = "DELETE FROM " . $table . " 
					WHERE ";
					
		 if ($mainID) {
			 $query .= $mainID['idType'] . " = '" . $mainID['typeID'] . "'";
		 } elseif ($details) {
			 $counter = 0;
			 foreach ($details as $column => $val) {
				 
				 if (empty($val) && !is_null($val)) {
					 continue;
				 }
				 
				 if ($counter != 0) {
					 $query .= " AND ";
				 }
				 
				 if (is_array($val)) {
					 // Array of values, use OR
					 $query .= '(';
					 $innerCounter = 0;
					 foreach ($val as $inner) {
						 if ($innerCounter != 0) {
							 $query .= " OR ";
						 }
						 $query .= " " . $column . " = '" . $inner . "' ";
						 $innerCounter++;
					 }
					 $query .= ')';

				 } else {
					 // Plain AND
					 if (is_null($val)) {
						 $query .= " " . $column . " IS NULL ";
					 } else {
						 $query .= " " . $column . " = '" . $val . "' ";
					 }
				 }
				 $counter++;
			 }
		 }


		 $db = Zend_Db_Table::getDefaultAdapter();

		 $db->query($query);
	 }

}