<?php

class Application_Controller_Helper_UserInfo extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * get and store all user info
	 */
	public function userinfo()
	{
		$auth = Zend_Auth::getInstance();
		
		$user = $auth->getStorage();
		//$user->getUserBy('u.userID',$_COOKIE['user']);
		$user->getUserSportsInfo();
		$user->getUserGames();
		$user->getOldUserNotifications(); // Must be after get games, teams, and groups call
		//$auth->getStorage()->write($user);
		
		return;
	}
	
}
			
