<?php

class Application_Model_Invite extends Application_Model_User
{
	protected $_mapperClass = 'Application_Model_InvitesMapper';
	protected $_dbTable		= 'Application_Model_DbTable_NotificationLog';	
	
	protected $_attribs     = array('gameInviteID' => '',
									'teamInviteID' => '',
									'actingUserID' => '',
									'email'		   => '',
									'gameID'	   => '',
									'teamID'	   => '',
									'numInvites'   => '',
									'firstSent'	   => '');
									
	protected $_primaryKey  = 'gameInviteID';
									
									
	public function delete() {
		if ($this->isTeamInvite()) {
			$table = 'team_invites';
			$idType = 'teamID';
		} else {
			$table = 'game_invites';
			$idType = 'gameID';
		}
		$id = ($this->hasValue($this->_primaryKey) ? array('idType' => $this->_primaryKey,
												   'typeID' => $this->_attribs[$this->_primaryKey]) : false);

		
		return $this->getMapper()->delete($table, 
										  array('email' => $this->email,
												'actingUserID' => $this->actingUserID,
												$idType => $this->$idType),
										  $id);
	}
	
	public function isTeamInvite()
	{
		if ($this->hasValue('teamInviteID') ||
			$this->hasValue('teamID')) {
			$this->_primaryKey = 'teamInviteID';
			return true;
		} else {
			return false;
		}
	}
		
}