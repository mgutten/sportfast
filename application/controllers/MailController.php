<?php

class MailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
	
	public function inviteTypeAction()
	{
		$post = $this->getRequest()->getPost();
		$type = (isset($post['gameID']) ? 'game' : 'team');
		$types = $type . 's';
		$typeID = (isset($post['gameID']) ? $post['gameID'] : $post['teamID']);
		
		if (!empty($post['userIDs'])) {
			// UserIDs have been posted, invite from db
			$userIDs = explode(',', $post['userIDs']);
		} else {
			$userIDs = array();
		}
		
		if (!empty($post['emails'])) {
			// Emails have been posted
			$emails = explode(',', $post['emails']);
			
			for ($i = 0; $i < count($emails); $i++) {
				$emails[$i] = trim($emails[$i]);
			}
			
			$users = new Application_Model_Users();
			$emailsExist = $users->emailsExist($emails);
			
			foreach ($emailsExist as $user) {
				$key = array_search($user['email'], $emails);
				unset($emails[$key]);
				
				if (!in_array($user['userID'], $userIDs)) {
					array_push($userIDs, $user['userID']);
				}
			}
		}
		
		foreach ($userIDs as $userID) {
			$notification = new Application_Model_Notification();
			
			$notification->receivingUserID = $userID;
			$notification->actingUserID = $this->view->user->userID;
			$notification->action = 'invite';
			$notification->type   = $type;
			$notification->details = '';
			$comboTypeID = $type . 'ID';
			$notification->$comboTypeID  = $typeID;
			
			$notification->save();
		}
		
		if ($type == 'game') {
			
			$typeModel = new Application_Model_Game();
			$typeModel->getGameByID($typeID);
		} else {
			// is team
			$typeModel = new Application_Model_Team();
			$typeModel->getTeamByID($typeID);
		}
		
		foreach ($emails as $email) {
			$subject  = $this->view->user->fullName . ' invited you to join ' . $this->view->user->getHisOrHer() . ' ' . ucwords($typeModel->sport) . ' ' . ucwords($type);
			$message  = $this->buildInviteGameMessage($this->view->user->fullName, $typeModel->sport);
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: " . $this->view->user->username . "\r\n";	 
			$headers .= "Reply-To: donotreply@sportup.com" . "\r\n";
					
					
			mail($email, $subject, $message, $headers);
		}
		
		
		
		$this->_redirect('/' . $types . '/' . $typeID);
			
	}
	
	public function buildInviteGameMessage($actingName, $sport)
	{
		$output = '<html>
						<head>
						</head>
						<body>';
						
		$output .= $actingName;
		
						
		$output .=		'</body>
					 </html>'; 
					 
		
		return $output;
	}
	
	public function cancelTypeAction()
	{
		$post    = $this->getRequest()->getPost();
		$options = $post['options'];
		
		if ($options['idType'] == 'gameID') {
			$type = 'game';
			$date  = $post['date'];
			$action = 'canceled';
			
		} elseif ($options['idType'] == 'teamID') {
			$type = 'team';
			$date = '';
			$action = 'deleted';
		}
		
		$sport = $post['sport'];
		$userIDs = $post['userIDs'];
		
		$users = new Application_Model_Users();
		$emails = $users->getUserEmails($userIDs);
		
		foreach ($emails as $email) {
			$subject  = 'Your ' . $sport . ' ' . ucwords($type) . ' has been ' . $action;
			$message  = "Your " . $sport . " " . ucwords($type) . " has been " . $action . ". Please visit the " . $type . " page for more details.";
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: info@sportup.com\r\n";	 
			$headers .= "Reply-To: donotreply@sportup.com" . "\r\n";
					
					
			mail($email, $subject, $message, $headers);
		}
		
	}
	
	/**
	 * mail new password
	 */
	public function forgotAction()
	{
		$email = $this->getRequest()->getPost('email');
		
		$user = new Application_Model_User();
		$user->getUserBy('u.username', $email);
		
		if (!$user->userID) {
			// Did not find user
			$this->_redirect('/login/forgot');
		}
		
		$password = '';
		$limit = mt_rand(5,6);
		$characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';

		for ($i = 0; $i < $limit; $i++) {
			$password .= $characters[rand(0, strlen($characters) - 1)];
		}
		
		
		$user->password = $user->hashPassword($password);
		
		$user->save(false);	
		
		$subject  = 'Password Reset';
		$message  = "A password reset has been requested.  Your new password is:
						<br><br><p style='font-weight:bold;font-size:16px'>" . $password . "</p>
						<br><br>You can set your password to something more meaningful under \"Settings\".";
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: info@sportup.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportup.com" . "\r\n";			
				
		mail($email, $subject, $message, $headers);
		
		
		$session = new Zend_Session_Namespace('forgot');
		$session->email = $email;
		
		
		$this->_redirect('/login');
	}
	
	
	/**
	 * mail to support form internal contact form
	 */
	public function contactAction()
	{
		$this->view->narrowColumn = false;
		
		$post = $this->getRequest()->getPost();
		
		$form = new Application_Form_Contact();
		
		if ($form->isValid($post)) {
			// Success
			$subject  = 'Contact Form';
			$message  = $post['question'];
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: contact@sportup.com\r\n";	 
			$headers .= "Reply-To: " . $post['email'] . "\r\n";			
					
			mail("support@sportup.com", $subject, $message, $headers);
		} else {
			// Fail
			$errors = array();
			foreach ($form->getMessages() as $section => $errorType) {
				foreach ($errorType as $val) {
					$errors[$section] = str_replace('Value', ucwords($section), $val);
				}
			}
			$this->_helper->FlashMessenger->addMessage($errors, 'contactError');
			
			$this->_redirect('/contact');
		}
			
	}

}

