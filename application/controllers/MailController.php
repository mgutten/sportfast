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
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
					
					
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
			// Is game
			$model = new Application_Model_Game();
			$model->gameID = $options['typeID'];
			$model->date = $post['date'];
			$action = 'mailCancelGame';
			//$date  = $post['date'];		
		} elseif ($options['idType'] == 'teamID') {
			// Is team
			$model = new Application_Model_Team();
			$model->teamID = $options['typeID'];
			$action = 'mailCancelTeam';
		}
		
		$model->sport = $post['sport'];
		$userIDs = $post['userIDs'];
		
		$users = new Application_Model_Users();
		$emails = $users->getUserEmails($userIDs);
		
		
		foreach ($emails as $email) {
			$this->$action($email, $model);		
		}
		
	}
	
	/**
	 * mail email to user that team/game has been canceled or deleted
	 * @params ($email => where to send,
	 *			$model => Game or Team model)
	 */
	public function mailCancelGame($email, $model)
	{
		$sport = ucwords($model->sport);
		$id = $model->gameID;
		
		$subject  = $sport . ' ' . $type . ' ' . $action;
		$message  = $this->mailStart();
		
		$message .= "<p class='bold largest-text'>Your " . $sport . " game has been canceled.</p>";
		$message .= "<br><br><p class='bold larger-text'>Today at " . $model->gameDate->format('ga') . "</p>";
		$message .= "<br><br><p>Please visit the <a href='http://www.sportfast.com/games/" . $id . "'>game page</a> for more details.</p>";
					
		$message .= $this->mailEnd();
		
		
		$message .= "<br><br><p class='medium'>Reason: </p><p>" . $cancelReason . "</p>";
		
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: info@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
	}
	
	/**
	 * mail email to user that game is on and happening
	 * @params ($email => where to send,
	 *			$game => Game model)
	 */
	public function mailGameOn($email, $game)
	{
		$sport = $game->sport;
		
		$id = $game->gameID;
		
		
		$subject  = $sport . ' Game On!';
		$message  = $this->mailStart();
		
		$message .= "<p>Game on!  See you out there!</p>
					 <br><p class='largest-text bold'>Today at " . $game->gameDate->format('ga') . "</p>
					 <p class='larger-text bold'>" . $game->park->parkName . "</p>
					 <p class='largest-text bold'>" . $game->totalPlayers . " players</p>
					 <br><a href='http://www.sportfast.com/games/" . $id . "' class='green-button largest-text bold' style='text-decoration:none'>View Page</a>
					 <br><br><p>Things to remember:</p>
					 <li>Games typically last between 1 and 2 hours</li>
					 <li>Bring your equipment <span class='medium'>(shoes, ball, disc, etc)</span></li>
					 <li>Have fun!</li>
					<br><br>Please visit the <a href='http://www.sportfast.com/games/" . $id . "'>game page</a> for more details.";
					
		$message .= $this->mailEnd();
		
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: info@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
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
		$headers .= "From: info@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";			
				
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
			$headers .= "From: contact@sportfast.com\r\n";	 
			$headers .= "Reply-To: " . $post['email'] . "\r\n";			
					
			mail("support@sportfast.com", $subject, $message, $headers);
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
	
	/**
	 * send email to warn of impending removal for inactive types
	 */
	public function warnInactiveAction()
	{
		$inactive = $this->getRequest()->getParam('inactive');
		
		foreach ($inactive as $email) {
			$subject  = 'Account Inactivity';
			$message  = (isset($email['firstName']) ? $this->buildWarnInactiveUserMessage($email) : $this->buildWarnInactiveTeamMessage($email));
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: support@sportfast.com\r\n";	 
			$headers .= "Reply-To: support@sportfast.com\r\n";			
					
			mail($email['username'], $subject, $message, $headers);
		}
			
	}
	
	/**
	 * build message for warnInactive action
	 * @params ($array => array of user details (username, userID, firstName, lastActive))
	 */
	public function buildWarnInactiveUserMessage($array)
	{
		$output  = $this->mailStart();
								
		$output .= "<p>We noticed you haven't visited our site in a while.  While we're sad, we understand.  However, 
					to keep our database up-to-date, we must deactivate inactive users after a period of 60 days.</p>";
					
		$output .= "<br><p><span class='bold larger-text'>
					Your account has been inactive for " . $array['lastActive'] . " days.  If you wish to keep your 
					account active, please <a href='http://www.sportfast.com/login'>login</a> within the next couple days.</span></p>";
					
		$output .= "<br><p>If you don't mind your account becoming inactive, then you do not need to do anything.</p>";
		
		$output .= "<br><p>Thanks!</p>";
		
		$output .= $this->supportSignature();
			
						
		$output .= $this->mailEnd();
					 
		
		return $output;
	}
	
	/**
	 * html header and body function (as well as standard styles) for email
	 */
	public function mailStart()
	{
		$output = '<html>
					<body>';
					
		$output .= $this->buildStyle();
		
		$output .= "<table width='98%'>
						<tr><td>
						<table width='650' border='0' cellpadding='0' cellspacing='0' align='center'>
						<tr>
							<td width='650' align='center'>
								<tr><td>";
							
						
									 		
		return $output;
	}
	
	/**
	 * html header and body function (as well as standard styles) for email
	 */
	public function mailEnd()
	{
		$output = "				</td></tr>
								</td>
							</tr>
						</table>
						</td>
						</tr>
					</table>
					
					</body>
					</html>";
		
		return $output;
	}
	
	/**
	 * create standard styles css for emails
	 */
	public function buildStyle()
	{	
		$output = "<style>";
		
		$output .= "
					p {
						margin: 0;
					}
					
					p,div,span,li,ul,a {
						font-family: Arial, Helvetica, Sans-Serif;
					}
					
					p,div,span,li,ul {
						color: #333;
						
					}
					
					.medium {
						color: #8d8d8d;
					}
					
					.light {
						color: #bbb;
					}
					
					.bold {
						font-weight: bold;
					}
					
					.larger-text {
						font-size: 1.25em;
					}
					
					.largest-text {
						font-size: 2em;
					}
					
					.smaller-text {
						font-size: .9em;
					}
					
					.center {
						width: 100%;
						text-align: center;
					}
					
					.green-button {
						padding: .2em .8em;
						background: #58bf12;
						color: #fff;
					}
					
					";
					
		$output .= "</style>";
		
		return $output;
	}

		
	
	public function supportSignature()
	{
		$output  = "<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;'>Marshall</p>
					<p class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d; margin: 0;'>Sportfast Support Team</p>
					<p class='smaller-text medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d; font-size: .9em; margin: 0;'>support@sportfast.com</p>";
		
		return $output;
	}
	
	/**
	 * email users whether their game has been canceled or not (game happening in next 2 hours from CronController => updateGameStatusAction)
	 */
	public function gameStatusAction()
	{
		$games = $this->getRequest()->getParam('games');
		
		// Email canceled users
		foreach ($games['canceled'] as $game) {
			foreach ($game->players->getAll() as $user) {
				$this->mailCancelGame($user->username, $game);
			}
		}
		
		// Email game on users
		foreach ($games['on'] as $game) {
			foreach ($game->players->getAll() as $user) {
				$this->mailGameOn($user->username, $game);
			}
		}
	}
	
	public function testAction()
	{
		
		$subject  = $this->view->user->fullName . ' invited you to join ' . $this->view->user->getHisOrHer() . ' ' . ucwords($typeModel->sport) . ' ' . ucwords($type);
		$message  = $this->mailStart();
		$message .= "<td width='100%'><tr align='center'><p class='medium largest-text'>Testing the classes and mail server</p></tr></td>";
		$message .= $this->mailEnd();
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: " . $this->view->user->username . "\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
	}
		
		

}

