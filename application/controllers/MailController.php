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
	
	public function preDispatch()
	{
		$render = array('contact',
						'add-user-subscribe-game',
						'add-user-team-game',
						'add-user-team',
						'unsubscribe-game',
						'invite-user-member');
		if (!in_array($this->getRequest()->getActionName(), $render)) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
		}
	}
	
	
	/**
	 * message all players of team/game
	 */
	public function messageAction()
	{
		$post = $this->getRequest()->getPost();
		
		if (isset($post['gameID'])) {
			// Is game
			$typeModel = new Application_Model_Game();
			$typeModel->getGameByID($post['gameID']);
			
			$subscribers = array();
			if (is_array($typeModel->subscribers)) {
				$subscribers = array_keys($typeModel->subscribers);
			}
			
			$confirmed = array();
			if (is_array($typeModel->confirmedPlayers)) {
				$confirmed = array_keys($typeModel->confirmedPlayers);
			}
			
			$userIDs = array_unique(array_merge($subscribers, $confirmed));
			
			$subject = $this->view->user->fullName . ' sent a message to your ' . $typeModel->sport . ' game';
			
			$returnURL = "/games/" . $post['gameID'] . "";
		} else {
			$typeModel = new Application_Model_Team();
			$typeModel->getTeamByID($post['teamID']);
			
			$userIDs = $typeModel->players->getUserIDs();
			
			$subject = $this->view->user->fullName . ' sent a message to ' . $typeModel->teamName;
			
			$returnURL = "/teams/" . $post['teamID'] . "";
		}
		
		unset($userIDs[array_search($this->view->user->userID, $userIDs)]);
		
		$users = new Application_Model_Users();
		$userEmails = $users->getUserEmails($userIDs);
		
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');	
		
		foreach ($userEmails as $userID => $email) {
			
			$mail = new PHPMailer;
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($email);  
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = $subject;
			
			$body = $this->buildMessage($post['messageBody'], $typeModel, $userID);
			
			$mail->Body    = $body['html'];
			$mail->AltBody = $body['text'];
			
			$mail->send();
			
			//mail($email, $subject, $body['html']);
		}
		
		$session = new Zend_Session_Namespace('message');
		$session->sent = true;
		
		$this->_redirect($returnURL);
	}
	
	public function buildMessage($message, $typeModel, $userID = false)
	{
		$output = $text = '';
		
		$output = $this->mailStart();
		
		$lower = "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:11px;'>";
		
		if ($typeModel instanceof Application_Model_Game) {
			
			if ($typeModel->isSubscriber($userID)) {
				// Allow unsubscribe
				$lower .= "You have received this email because you are subscribed to this game.  To unsubscribe, click <a href='http://www.sportfast.com/mail/unsubscribe-game/" . $typeModel->gameID . "/" . $userID . "' style='font-size:1em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>here</a>.</p>";
			} else {
				$lower .= "You have received this email because you are attending this game.</p>";
			}
			$url = 'http://www.sportfast.com/games/' . $typeModel->gameID;
			$type = 'game';
		} else {
			// Team
			$lower .= "You have received this email because you are on this team.</p>";
			$url = 'http://www.sportfast.com/teams/' . $typeModel->teamID;
			$type = 'team';
		}
		
		$output .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:12px;'>
								" . $this->view->user->firstName . " said...
							</p>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:14px;background:#e9e9e9;padding:10px;'>" 
							. nl2br($message) . 
						"</p></td>
					</tr>
					<tr height='30'>
						<td></td>
					</tr>
					<tr>
						<td align='center'><a href='" . $url . "' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:16px;'>view " . $type . "</a></td>
					</tr>
					<tr height='30'>
						<td></td>
					</tr>
					<tr>
						<td>" . $lower . "</td>
					</tr>";
					
		$text  .= $message;
		
		$output .= $this->mailEnd();
		
		$returnArray = array('html' => $output,
							 'text' => $text);
							 
		return $returnArray;
	}
						

					 
	
	/**
	 * invite users to team game from reserves list
	 */
	public function inviteTeamGameAction()
	{
		$options = $this->getRequest()->getParam('options');
		
		$userIDs = $options['userIDs'];
		$teamID = $options['teamID'];
		$teamGameID = $options['teamGameID'];
		
		$game = new Application_Model_Game();
		$game->teamGameID = $teamGameID;
		$game->getGameByID($teamGameID);
		
		
		if (empty($options['userIDs'])) {
			return;
		}
		
		
		$users = new Application_Model_Users();
		$userEmails = $users->getUserEmails($userIDs);
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');		
		
		foreach ($userEmails as $userID => $email) {
			
			$mail = new PHPMailer;
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($email);  
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = $this->view->user->fullName . " invited you to " . $game->getTeamNamePossession() . " upcoming " . strtolower($game->sport) . " game";
			$body 	   	   = $this->buildInviteTeamGameMessage($this->view->user, $game, $userID);
			
			$mail->Body    = $body['html'];
			$mail->AltBody = $body['text'];
			
			$mail->send();
			
			
			/*
			$subject  = $this->view->user->fullName . " invited you to " . $game->getTeamNamePossession() . " upcoming " . strtolower($game->sport) . " game";
			$message  = $this->buildInviteTeamGameMessage($this->view->user, $game, $userID);
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: " . $this->view->user->fullName . " <support@sportfast.com>\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
						
			mail($email, $subject, $message, $headers);
			*/
		}
	}
		
	public function buildInviteTeamGameMessage($actingUser, $game, $userID)
	{
		$text = '';
		
		$output  = $this->mailStart();
		$output .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:1.25em;text-align:center'><strong>" . $actingUser->fullName . "</strong> invited you to play in  " . $game->getTeamNamePossession() . " upcoming " . strtolower($game->sport) . " game.</p>
						</td>
				  </tr>
					 
					 <tr>
						 <td height='20px'></td>
					 </tr>";
		
		$text .= $actingUser->fullName . " invited you to play in  " . $game->getTeamNamePossession() . " upcoming " . strtolower($game->sport) . " game.\n";
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		$output .= "<tr><td align='center' colspan='3'>
						 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>vs. " . $game->opponent . "</p>
						 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->gameDate->format('l') . " at " . $time . "</p>
						 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->locationName . "</p>
					 </td></tr>";
					 
		$text .= "\n \t vs. " . $game->opponent . "\n";
		$text .= "\t " . $game->gameDate->format('l') . " at " . $time . "\n";
		$text .= "\t "  . $game->locationName . "\n";
		
		$inURL = "http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "/1";
		$outURL = "http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "/0";
		$maybeURL = "http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "/2";
					 
		$output .= $this->buildConfirmedButtons($inURL, $outURL, $maybeURL);
		
		$text .= "\n IN: http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "	
				 \n OUT: http://www.sportfast.com/mail/remove-user-team-game/" . $game->teamGameID . "/" . $userID . "
				 \n view team: http://www.sportfast.com/teams/" . $game->teamID;	
		
		$output .= "<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/teams/" . $game->teamID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view team</a>
						</td>
					 </tr>";
					
		$output .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $output;
		$returnArray['text'] = $text;
		
		return $returnArray;
	}
			
	
	public function reinviteUsersAction()
	{
		$games = $this->getRequest()->getParam('games');
		$teams = $this->getRequest()->getParam('teams');
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');
		
		$adminMessage = '';
		
		foreach ($games as $game) {
			$adminMessage .= "\n GameID: " . $game->gameID . "\n";
			
			$firstLetter = strtolower($game->sport[0]);
			$an = false;
			
			if ($firstLetter == 'a' || $firstLetter == 'e' || $firstLetter == 'i' || $firstLetter == 'o' || $firstLetter == 'u') {
				$an = true;
			}
			
			foreach ($game->players->getAll() as $user) {
				
				$email = $user->username;
				
				$mail = new PHPMailer;
			
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = 'box774.bluehost.com';  				  // Specify main and backup server
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = 'support@sportfast.com';            // SMTP username
				$mail->Password = 'sportfast.9';                      // SMTP password
				//$mail->SMTPSecure = "ssl";
				
				
				$mail->From = 'support@sportfast.com';
				$mail->FromName = 'Sportfast';
				$mail->addAddress($email);  // Add a recipient
				$mail->addReplyTo('donotreply@sportfast.com');
				
				$mail->isHTML(true);        // Set email format to HTML
				
				
				$mail->Subject = 'Final Reminder: You have been invited to join a' . ($an ? 'n' : '') . ' ' . ucwords($game->sport) . ' Game';
				$body 	   	   = $this->buildReinviteGameMessage($game, false, false, $email);
				
				$mail->Body    = $body['html'];
				$mail->AltBody = $body['text'];
				
				$mail->send();
				//mail($email, 'Reminder', $body['html']);
				
				$adminMessage .= $email . "\n";
				
				
				//mail ($email, $mail->Subject, $body['html']);
			}
		}
		
		foreach ($teams as $team) {
			
			$adminMessage .= "\n TeamID: " . $team->teamID . " \n";
			
			$firstLetter = strtolower($team->sport[0]);
			$an = false;
			
			if ($firstLetter == 'a' || $firstLetter == 'e' || $firstLetter == 'i' || $firstLetter == 'o' || $firstLetter == 'u') {
				$an = true;
			}
			
			foreach ($team->players->getAll() as $user) {
				
				$email = $user->username;
				
				$mail = new PHPMailer;
			
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = 'box774.bluehost.com';  				  // Specify main and backup server
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = 'support@sportfast.com';            // SMTP username
				$mail->Password = 'sportfast.9';                      // SMTP password
				//$mail->SMTPSecure = "ssl";
				
				
				$mail->From = 'support@sportfast.com';
				$mail->FromName = 'Sportfast';
				$mail->addAddress($email);  // Add a recipient
				$mail->addReplyTo('donotreply@sportfast.com');
				
				$mail->isHTML(true);                                  // Set email format to HTML
				
				
				$mail->Subject = 'Final Reminder: You have been invited to join a' . ($an ? 'n' : '') . ' ' . ucwords($team->sport) . ' Team';
				$body 	   	   = $this->buildReinviteGameMessage($team, false, false, $email);
				
				$mail->Body    = $body['html'];
				$mail->AltBody = $body['text'];
				
				$mail->send();
				//mail($email, 'Reminder', $body);
				
				$adminMessage .= $email . "\n";
				
			}
		}
		
		if ($adminMessage != '') {
			$subject  = "ADMIN: Reinvite Users";
			$message  = $adminMessage;
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1" . "\r\n";
			$headers .= "From: support@sportfast.com\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
					
	
			mail('guttenberg.m@gmail.com', $subject, $message, $headers);
		}
	}
	
	public function buildReinviteGameMessage($typeModel, $note = false, $isUser = false, $email = false)
	{
		$output = $this->mailStart();
		$text = $main = '';
		$type = ($typeModel instanceof Application_Model_Game ? 'game' : 'team');
						
		if ($type == 'game') {

			$output .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:24px;text-align:center;font-weight:bold'>You must choose any option below to continue receiving email reminders.</p>
						</td>
					 </tr>
					 <tr>
						<td height='20'>
						</td>
					 </tr>
					 <tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:14px;text-align:center'>You have been invited to join a weekly " . strtolower($typeModel->sport) . " game at " . $typeModel->park->parkName . ".  
							<br><br>If you wish to view this game's details and/or receive future communications regarding any game-related updates <strong>you should select an option below</strong> to join.  
							Otherwise, this will be your last email reminder regarding this game.</p>
						</td>
					 </tr>
					 <tr>
						<td height='20px'></td>
					 </tr>";
			
			
			$time = ($typeModel->gameDate->format('i') > 0 ? $typeModel->gameDate->format('g:ia') : $typeModel->gameDate->format('ga'));
			
			$main .= "<tr><td align='center' colspan='3'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->getGameTitle(true) . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->park->parkName . "</p>
						 </td></tr>";
						 
			$textMain  = "\t " . $typeModel->getGameTitle(true);
			$textMain .= "\n \t " . $typeModel->gameDate->format('l') . " at " . $time;
			$textMain .= "\n \t " . $typeModel->park->parkName;

			
			$id = $typeModel->gameID;
		} else {
			// Team
			$main = $textMain = '';
			
			$main .= "<tr>
								<td align='center' colspan='3'>
									<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:16px;text-align:center'>You have been invited to join a " . strtolower($typeModel->sport) . " team, <strong>" . $typeModel->teamName . "</strong>.
									<br><br>
									If you wish to receive team messages and updates, then <strong>you should join below</strong>.  This will be your final reminder to join this team and let your teammates know if you are playing.</p>
								</td>
						  </tr>
							 
							 <tr>
								 <td height='20px'></td>
							 </tr>";
							 
			$textMain .= "You have been invited to join a " . strtolower($typeModel->sport) . " team, " . $typeModel->teamName . " \n";
			
			$main .= " <tr><td align='center' colspan='3'>
							<p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->teamName . "</p>
							<p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->sport . " Team</p>
						</td></tr>";
						
			$textMain .= "\n \t" . $typeModel->teamName;
			$textMain .= "\n \t" . $typeModel->sport . " Team";
			
			
			$id = $typeModel->teamID;
		}			
		

		$src = "invite-user-" . $type . "/" . $id;

		
		$text .= "\n \n " . $textMain . "\n";		
	
		$output .= "<tr>
						 <td height='20px'></td>
					 </tr>
					 ";
		$output .= 		$main;			 
		
		if ($type == 'game') {
			$inUrl    = "http://www.sportfast.com/mail/" . $src . "/1";
			$outUrl   = "http://www.sportfast.com/mail/" . $src . "/0";
			$maybeUrl = "http://www.sportfast.com/mail/" . $src . "/2";
			
			if (!$isUser) {
				$inUrl .= "/" . urlencode($email);
				$outUrl .= "/" . urlencode($email);
				$maybeUrl .= "/" . urlencode($email);
			}
			
			$buttonSection = $this->buildConfirmedButtons($inUrl, $outUrl, $maybeUrl);
			
			if ($typeModel->isRecurring()) {
				$buttonSection .= $this->buildMemberButtons($typeModel->gameID, false, $email);
			}
			
			$text .= "\n \n \t IN: " . $inUrl;
			$text .= "\n \t OUT: " . $outUrl;
			$text .= "\n \t MAYBE: " . $maybeUrl . "\n";
		} else {
			$buttonSection = "<tr>
								 <td height='20px'></td>
							 </tr>
							 <tr>
								<td align='center' colspan='3'>
									<a href='http://www.sportfast.com/mail/" . $src . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>Join</a>
								</td>
							 </tr>";
			
			$text .= "\n \t JOIN: http://www.sportfast.com/mail/" . $src . " \n";
		}
		
		$output .= $buttonSection;
					 
		
		if (!$isUser) {
			$output .=  $this->sportfastExplanation();
			
			$text .= $this->sportfastExplanation(true);
					 
		} else {
			$output .= "<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/" . $type . "s/" . $id . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view " . $type . "</a>
						</td>
					 </tr>";
			/*	 
			$output .= "<tr>
					 		<td height='30px'></td>
					 	</tr>
						<tr>
					 	<td align='center'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 11px; color: #8d8d8d; margin: 0;'>To unsubscribe, please access your account settings.
						</td>
					 </tr>";
			*/
					 
			$text .= "\n \t view " . $type . ": http://www.sportfast.com/" . $type . "s/" . $id . "\n";
		}
		
						
		$output .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $output;
		$returnArray['text'] = $text;
		
		return $returnArray;		 
	}
	
	public function inviteTypeAction()
	{
		$post = $this->getRequest()->getPost();
		
		$type = (isset($post['gameID']) ? 'game' : 'team');
		$types = $type . 's';
		$typeID = (isset($post['gameID']) ? $post['gameID'] : $post['teamID']);
		
		$userEmails = $emails = array();
		
		if (!empty($post['userIDs'])) {
			// UserIDs have been posted, invite from db
			$userIDs = explode(',', $post['userIDs']);
			
			$users = new Application_Model_Users();
			$userEmails = $users->getUserEmails($userIDs);
			
		} else {
			$userIDs = array();
		}
		
		
		
		if (!empty($post['emails'])) {
			// Emails have been posted
			$emails = explode(',', $post['emails']);
			
			$pattern = '/([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' .
						'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)/i';
			
			for ($i = 0; $i < count($emails); $i++) {
				preg_match ($pattern, $emails[$i], $matches);
				$emails[$i] = $matches[0];
			}
			
			
			$users = new Application_Model_Users();
			$emailsExist = $users->emailsExist($emails);
			
			foreach ($emailsExist as $user) {
				$key = array_search($user['email'], $emails);
				unset($emails[$key]);
				
				if (!in_array($user['userID'], $userIDs)) {
					array_push($userIDs, $user['userID']);
					$userEmails[$user['userID']] =  $user['email'];
				}
			}
		}
		
		
		$note = '';
		if (!empty($post['note'])) {
			// Personalized note was sent
			$note = $post['note'];
		}

		if ($type == 'game') {
			// Is game
			$typeModel = new Application_Model_Game();
			$typeModel->getGameByID($typeID);
			
			$subjectAdd = ' on ' . $typeModel->gameDate->format('l');
		} else {
			// Is team
			$typeModel = new Application_Model_Team();
			$typeModel->getTeamByID($typeID);
			$subjectAdd = '';
		}
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');		
		
		$counter = 0;
		foreach ($userIDs as $userID) {
			if ($typeModel->players->exists($userID)) {
				// Do not email players who are already on the team
				continue;
			}
			
			$notification = new Application_Model_Notification();
			
			$notification->receivingUserID = $userID;
			$notification->actingUserID = $this->view->user->userID;
			$notification->action = 'invite';
			$notification->type   = $type;
			$notification->details = '';
			$comboTypeID = $type . 'ID';
			$notification->$comboTypeID  = $typeID;
			
			$notification->save();
			
			$mail = new PHPMailer;
			
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($userEmails[$userID]);  
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = $this->view->user->fullName . ' invited you to ' . $this->view->user->getHisOrHer() . ' ' . ucwords($typeModel->sport) . ' ' . ucwords($type) . $subjectAdd;
			$body 	   	   = $this->buildInviteGameMessage($this->view->user, $typeModel, $note, $userID);
			
			$mail->Body    = $body['html'];
			$mail->AltBody = $body['text'];
			
			
			
			$mail->send();
			//mail($userEmails[$userID], $mail->Subject, $body['html']);
			
			/*
			$subject  = $this->view->user->fullName . ' invited you to ' . $this->view->user->getHisOrHer() . ' ' . ucwords($typeModel->sport) . ' ' . ucwords($type);
			$message  = $this->buildInviteGameMessage($this->view->user, $typeModel, $note, $userID);
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: " . $this->view->user->fullName . " <support@sportfast.com>\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
					
	
			mail($userEmails[$userID], $subject, $message, $headers);
			*/
			$counter++;
		}		
		
		
		foreach ($emails as $email) {
			
			$mail = new PHPMailer;
			
			
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = 'box774.bluehost.com';  				  // Specify main and backup server
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'support@sportfast.com';            // SMTP username
			$mail->Password = 'sportfast.9';                      // SMTP password
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($email);  // Add a recipient
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  // Set email format to HTML
			
			$mail->Subject = $this->view->user->fullName . ' invited you to ' . $this->view->user->getHisOrHer() . ' ' . ucwords($typeModel->sport) . ' ' . ucwords($type) . $subjectAdd;
			$body 	   	   = $this->buildInviteGameMessage($this->view->user, $typeModel, $note, false, $email);
			
			$mail->Body    = $body['html'];
			$mail->AltBody = $body['text'];
			

			$mail->send();
			//mail($email, $mail->Subject, $body['html']);
		
			/*
			$subject  = $this->view->user->fullName . ' invited you to ' . $this->view->user->getHisOrHer() . ' ' . ucwords($typeModel->sport) . ' ' . ucwords($type);
			$message  = $this->buildInviteGameMessage($this->view->user, $typeModel, $note);
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: " . $this->view->user->fullName . " <support@sportfast.com>\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
					
	
			mail($email, $subject, $message, $headers);
			*/
		}
		
		$invites = array_merge($emails, $userEmails);
		
		
		if (!empty($emails) ||
			!empty($userEmails)) {
			$typeModel->saveInvites(array_merge($emails, $userEmails), $this->view->user->userID);
		}
		
		$session = new Zend_Session_Namespace('invites');
		$session->sent = true;
		
		
		$this->_redirect('/' . $types . '/' . $typeID);
			
	}
	
	/**
	 * mail for invite to a game or team
	 * @params ($actingUser => Application_Model_User of user who invited,
	 *			$typeModel => Application_Model_Game or Application_Model_Team,
	 *			$note => optional personalized note (str),
	 *			$isUser => if true (aka receivingUserID value), do not include introductory stuff)
	 */
	public function buildInviteGameMessage($actingUser, $typeModel, $note = false, $isUser = false, $email = false)
	{
		$output = $this->mailStart();
		$text = $main = '';
		$type = ($typeModel instanceof Application_Model_Game ? 'game' : 'team');
						
		if ($type == 'game') {
			
			if ($isUser) {
				$output .= "<tr>
							<td colspan='3'>
								<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:16px;text-align:center'><strong>" . $actingUser->fullName . "</strong> invited you to join " . $actingUser->getHisOrHer() . " " . strtolower($typeModel->sport) . " game.</p>
							</td>
					  	 </tr>
						 <tr>
							<td height='20px'></td>
						 </tr>";
			}
			
			$time = ($typeModel->gameDate->format('i') > 0 ? $typeModel->gameDate->format('g:ia') : $typeModel->gameDate->format('ga'));
			
			$main .= "<tr><td align='center' colspan='3'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->getGameTitle(true) . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->park->parkName . "</p>
						 </td></tr>";
						 
			$textMain  = "\t " . $typeModel->getGameTitle(true);
			$textMain .= "\n \t " . $typeModel->gameDate->format('l') . " at " . $time;
			$textMain .= "\n \t " . $typeModel->park->parkName;
			
			//$intro = "Sportfast will allow us to see who is going, to find players if we need them, to receive any updates, and to track stats on ourselves as well as our game.";
			$intro = array("see who is going",
						   "receive any game-related updates",
						   "track your own stats as well as our game's",
						   "find local pickup games for different sports");
			
			$id = $typeModel->gameID;
		} else {
			// Team
			$main = $textMain = '';
			if ($isUser) {
				$main .= "<tr>
								<td colspan='3'>
									<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:16px;text-align:center'><strong>" . $actingUser->fullName . "</strong> invited you to join " . $actingUser->getHisOrHer() . " " . strtolower($typeModel->sport) . " team.</p>
								</td>
						  </tr>
							 
							 <tr>
								 <td height='20px'></td>
							 </tr>";
							 
				$textMain .= $actingUser->fullName . " invited you to join " . $actingUser->getHisOrHer() . " " . strtolower($typeModel->sport) . " team. \n";
			}
			$main .= " <tr><td align='center' colspan='3'>
							<p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->teamName . "</p>
							<p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->sport . " Team</p>
						</td></tr>";
						
			$textMain .= "\n \t" . $typeModel->teamName;
			$textMain .= "\n \t" . $typeModel->sport . " Team";
			
						
			//$intro = "Sportfast will allow everyone to quickly say whether they're \"in\" or \"out\" for our next game, to see our upcoming schedule, to receive automatic reminders for upcoming games, and to track our progress over the season.";
			$intro = array("see who is \"in\" or \"out\" for our next game",
						   "view our upcoming schedule",
						   "receive automatic reminders for upcoming games",
						   "track our progress over the season",
						   "find local pickup games for different sports");
			
			$id = $typeModel->teamID;
		}			
		
		if ($note) {
			// Add personalized note
			$output .= "<tr><td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 12px; color: #8d8d8d; margin: 0;text-align:left;'>" . $actingUser->firstName . " said...</p>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #777; margin: 0;text-align:left;background:#e9e9e9;padding:5px;'>" . $note . "</p>
							</td>
						</tr>
						<tr height='20'>
							<td></td>
						</tr>";
						
			$text .= "\"" . $note . "\" \n";
		}
		
		if (!$isUser) {
			$text .= "\n I've moved our " . $typeModel->sport . " " . $type . " to Sportfast so we can coordinate easier.  With Sportfast, you can:";
			
			if ($type == 'game') {
				if ($typeModel->isRecurring()) {
					$output .= "<tr><td colspan='3'>
									<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 24px; color: #333;font-weight:bold; margin: 0;text-align:center;'>You must choose any option below to continue receiving email reminders.</p>
									</td>
								</tr>
								<tr height='20'>
									<td></td>
								</tr>";
				}
			}
			
			$output .= "<tr><td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; margin: 0;'>I've moved our " . $typeModel->sport . " " . $type . " to Sportfast so we can coordinate easier.  With Sportfast, you can:</p>
							<ul class='bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; font-weight: bold;'>";
			//<br><br>" . $intro . "  It's designed specifically to help organize, find, and manage recreational sports.				
			foreach ($intro as $point) {
				$output .= "<li style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333;'>" . $point . "</li>";
				$text .= "\n \t -" . $point;
			}
			$output .= "</ul>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; margin: 0;'>Sportfast was designed for recreational athletes.  It's simple and fast.
							<br><br>" . $actingUser->shortName . "</p>
						</td></tr>";
						
			$text .= "\n \n Sportfast was designed for recreational athletes.  It's simple and fast. \n";
			$text .= "\n " . $actingUser->shortName . " ";
			
			$src = "invite-user-" . $type . "/" . $id;
		} else {
			// Is user, add to game
			if ($type == 'game') {
				$src = "add-user-subscribe-game/" . $id . "/" . $isUser;
			} elseif ($type == 'team') {
				// Is team
				$src = "add-user-team/" . $id . "/" . $isUser;
			}

		}
		
		$text .= "\n \n " . $textMain . "\n";		
	
		$output .= "<tr>
						 <td height='20px'></td>
					 </tr>
					 ";
		$output .= 		$main;			 
		
		if ($type == 'game') {
			$inUrl    = "http://www.sportfast.com/mail/" . $src . "/1";
			$outUrl   = "http://www.sportfast.com/mail/" . $src . "/0";
			$maybeUrl = "http://www.sportfast.com/mail/" . $src . "/2";
			
			if (!$isUser) {
				$inUrl .= "/" . urlencode($email);
				$outUrl .= "/" . urlencode($email);
				$maybeUrl .= "/" . urlencode($email);
			}
			
			$buttonSection = $this->buildConfirmedButtons($inUrl, $outUrl, $maybeUrl);
			
			if ($typeModel->isRecurring()) {
				$buttonSection .= $this->buildMemberButtons($id, $isUser, $email);
			}
			
			$text .= "\n \n \t IN: " . $inUrl;
			$text .= "\n \t OUT: " . $outUrl;
			$text .= "\n \t MAYBE: " . $maybeUrl . "\n";
		} else {
			if (!$isUser) {
				$src .= "/0/" . urlencode($email); // 0 is for param2 which must be #
			}
			
			$buttonSection = "<tr>
								 <td height='20px'></td>
							 </tr>
							 <tr>
								<td align='center' colspan='3'>
									<a href='http://www.sportfast.com/mail/" . $src . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>Join</a>
								</td>
							 </tr>";
			
			$text .= "\n \t JOIN: http://www.sportfast.com/mail/" . $src . " \n";
		}
		
		$output .= $buttonSection;
					 
		
		if (!$isUser) {
			if ($type != 'game') {
				$output .=  "<tr>
							 <td height='15px'></td>
						 </tr>
						 <tr>
							 <td colspan='3'>
								<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 16px; color: #58bf12; font-weight: bold;text-align:center'>
									You will not receive any future reminders regarding this " . $type . " or be able to view its details unless you " . ($type == 'game' ? 'select an option' : 'join') . ". 
								</p>
							 </td>
						 </tr>";
			}
			$output .= $this->sportfastExplanation();
			
			$text .= $this->sportfastExplanation(true);
					 
			$text .= "\n You will not receive any future reminders regarding this " . $type . " or be able to view its details unless you join.";
		} else {
			$output .= "<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/" . $type . "s/" . $id . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view " . $type . "</a>
						</td>
					 </tr>";
			/*	 
			$output .= "<tr>
					 		<td height='30px'></td>
					 	</tr>
						<tr>
					 	<td align='center'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 11px; color: #8d8d8d; margin: 0;'>To unsubscribe, please access your account settings.
						</td>
					 </tr>";
			*/
					 
			$text .= "\n \t view " . $type . ": http://www.sportfast.com/" . $type . "s/" . $id . "\n";
		}
		
						
		$output .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $output;
		$returnArray['text'] = $text;
		
		return $returnArray;		 
		
		//return $output;
	}
	
	/**
	 * invite to join sportfast
	 */
	public function inviteSportfastAction()
	{
		$post = $this->getRequest()->getPost();
				
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
				
			}
		}
		
		$note = '';
		if (!empty($post['note'])) {
			// Personalized note was sent
			$note = $post['note'];
		}
		
		foreach ($emails as $email) {
			$subject  = $this->view->user->fullName . ' invited you to join Sportfast';
			$message  = $this->buildInviteSportfast($this->view->user, $note);
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: " . $this->view->user->fullName . " <support@sportfast.com>\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
					
	
			mail($email, $subject, $message, $headers);
		}
		
		$session = new Zend_Session_Namespace('invites');
		$session->sent = true;
	}
	
	/**
	 * mail for invite to join sportfast (NOT IN USE)
	 * @params ($actingUser => Application_Model_User of user who invited,)
	 */
	public function buildInviteSportfast($actingUser, $note = false)
	{
		$output = $this->mailStart();
		
		$output .= "<tr>
						<td>
							<p style='text-align:center;font-family: Arial, Helvetica, Sans-Serif; font-size: 18px; color: #333; margin: 0;'>
								<strong>" . $actingUser->fullName . "</strong> invited you to join <bold>Sportfast</bold>!
							</p>
						</td>
					</tr>";
					
		if ($note) {
			// Personalized note attached
			$output .= "<tr height='20'>
							<td width='20%'></td>
							<td width='60%'><p style='text-align:center;font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #777; margin: 0;'>\"" . $note . "\"</p></td>
							<td width='20%'></td>
					    </tr>";
		}
		
		$output .= " <tr>
						 <td height='20px'></td>
					 </tr>
					 <tr>
						<td align='center'>
							<a href='http://www.sportfast.com/how' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>learn more</a>
						</td>
					 </tr>";
					 
		$output .= $this->sportfastExplanation();
		
		return $output;
					
	}
	
	/**
	 * "join" button clicked from invite to join sportfast email (want to join this game)
	 */
	public function inviteUserGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$confirmed  = $this->getRequest()->getParam('param2');
		$email = urldecode($this->getRequest()->getParam('param3'));
		
		$user = new Application_Model_User();
		$exists = $user->getUserBy('u.username', $email);
		
		if ($exists) {
			return $this->_redirect('/mail/add-user-subscribe-game/' . $gameID . '/' . $user->userID . '/' . $confirmed);
		}
		
		/*
		$user = new Application_Model_User();
		$user->getMapper()->getUserBy('u.username', $email);
		*/
		
		$session = new Zend_Session_Namespace('signupInvite');
		$session->type = 'game';
		$session->id = $gameID;
		$session->email = ($email == '1' ? '' : $email); // 1 is default value of param3
		$session->confirmed = $confirmed;
		
		$this->_redirect('/games/' . $gameID);
	}
	
	/**
	 * "join" button clicked from invite to join sportfast email (want to join this game)
	 */
	public function inviteUserTeamAction()
	{
		$teamID = $this->getRequest()->getParam('id');
		$confirmed  = $this->getRequest()->getParam('param2');
		$email = urldecode($this->getRequest()->getParam('param3'));

		
		//$this->_redirect('/signup');
		
		$user = new Application_Model_User();
		$exists = $user->getUserBy('u.username', $email);
		
		if ($exists) {
			return $this->_redirect('/mail/add-user-team/' . $teamID . '/' . $user->userID);
		}
		
		/*
		$user = new Application_Model_User();
		$user->getMapper()->getUserBy('u.username', $email);
		*/
		$session = new Zend_Session_Namespace('signupInvite');
		$session->type = 'team';
		$session->id = $teamID;
		$session->email = ($email == '1' ? '' : $email); // 1 is default value of param3
		
		
		$this->_redirect('/teams/' . $teamID);
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
			$model->cancelReason = (isset($post['cancelReason']) ? $post['cancelReason'] : 'No reason given');
			$action = 'mailCancelGame';
			//$date  = $post['date'];		
		} elseif ($options['idType'] == 'teamID') {
			// Is team
			$model = new Application_Model_Team();
			$model->teamID = $options['typeID'];
			$model->getTeamByID($options['typeID']);
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
	 *			$model => Game model)
	 */
	public function mailCancelGame($email, $model)
	{
		$sport = ucwords($model->sport);
		$id = $model->gameID;
		
		$time = ($model->gameDate->format('i') > 0 ? $model->gameDate->format('g:ia') : $model->gameDate->format('ga'));
		
		$subject  = $sport . ' Game Canceled';
		$message  = $this->mailStart();
		
		$top = $lower = $text = '';
		if (strtolower($model->cancelReason) == 'not enough players') {
			// Canceled because not enough players
			$subject = $sport . ' Game: Not Enough Players';
			$players = ($model->totalPlayers == 1 ? 'player' : 'players');
			
			$text .= "There " . ($players == 'players' ? 'are' : 'is') . " only " . $model->totalPlayers . " " . $players . " attending. \n \n";
			
			$top = "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0; text-align:center'>There " . ($players == 'players' ? 'are' : 'is') . " only " . $model->totalPlayers . " " . $players . " attending.  Check the game page for more details.</p>
					 <tr>
					 	<td height='30'></td>
					 </tr>";
			$lower = "<p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $model->totalPlayers . " " . $players . "</p>";
		} else {
			$top = "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0; text-align:center;'>Your " . $sport . " game has been canceled.</p>
					<tr>
					 	<td height='30'></td>
					 </tr>";
		}
		
		$text .= $model->sport . " Game \n";
		$text .= $model->gameDate->format('l') . " at " . $time . "\n";
		
		$message .= "<tr>
						<td>" . $top . "</td>
					 </tr>
					 <tr>
						<td align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $model->sport . " Game</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $model->gameDate->format('l') . " at " . $time . "</p>
							 " . $lower . "
						 </td>
					 </tr>";
		
		$text .= "\n Please visit the game page for more details: http://www.sportfast.com/games/" . $id . " \n";
		$message .= "<tr>
						<td height='20'></td>
					</tr>
					<tr>
						<td align='center'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1em; color: #333; margin: 0;'>Please visit the <a href='http://www.sportfast.com/games/" . $id . "' style='font-family: Arial, Helvetica, Sans-Serif; color: #333;margin: 0;'>game page</a> for more details.</p>
						</td>
					</tr>";
		
		if ($model->cancelReason != '') {
			
			$text .= "\n Reason: \"" . $model->cancelReason . "\"";
			
			$message .= "<tr>
							<td height='40'></td>
						 </tr>
						 <tr>
							<td>
								<p class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;margin: 0;'>Reason: </p><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;margin: 0;'>" . $model->cancelReason . "</p>
							</td>
						</tr>";
					
		}
		
		$message .= $this->mailEnd();
		/*
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: games@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
		*/

		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');		
		
		$mail = new PHPMailer;
		$mail->isSMTP();                                      
		$mail->Host = 'box774.bluehost.com';  				 
		$mail->SMTPAuth = true;                               
		$mail->Username = 'support@sportfast.com';                            
		$mail->Password = 'sportfast.9';                           
		//$mail->SMTPSecure = "ssl";
		
		$mail->From = 'support@sportfast.com';
		$mail->FromName = 'Sportfast';
		$mail->addAddress($email);  
		$mail->addReplyTo('donotreply@sportfast.com');
		
		$mail->isHTML(true);                                  
		
		$mail->Subject = $subject;
		
		$mail->Body    = $message;
		$mail->AltBody = $text;
		
		$mail->send();
		
		//mail($email, $subject, $message);
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
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		
		$day = ($game->gameDate->format('d') == date('d') ? 'Today' : $game->gameDate->format('l'));
		
		$subject  = $game->sport . ' Game On';
		$message  = $this->mailStart();
		
		$text  = "Your " . strtolower($game->sport) . " game is on!  Have fun. \n";
		$text .= "\n \t " . $day . " at " . $time;
		$text .= "\n \t " . $game->park->parkName;
		$text .= "\n \t " . $game->totalPlayers;
		$text .= "\n \n View game for more details: http://www.sportfast.com/games/" . $id . " \n";
		
		$message .= "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;text-align:center;'>Your " . strtolower($game->sport) . " game is on!  Have fun.</p>
					 <tr>
					 	<td height='30'></td>
					 </tr>
					 <tr>
					 	<td align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $day . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->park->parkName . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->totalPlayers . " players  <span style='font-family: Arial, Helvetica, Sans-Serif; font-size: .4em; color: #8d8d8d; font-weight: bold; margin: 0;'>" . ($game->countMaybeConfirmedPlayers() > 0 ? '+' . $game->countMaybeConfirmedPlayers() . ' maybe' : '') . "</span></p>
						 </td>
					 </tr>
					 <tr>
					 	<td height='20'></td>
					 </tr>
					 <tr>
					 	<td align='center'>	 
							<a href='http://www.sportfast.com/games/" . $id . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>view game</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='50'></td>
					 </tr>
					 <tr>
					 	<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;'>Things to remember:</p>
								 <li>Show up on time (some people have places to be)</li>
								 <li>Games typically last between 1 and 2 hours</li>
								 <li>Bring your equipment <span class='medium'>(shoes, ball, disc, etc)</span></li>
								 <li>Do you need the stash?  Find out where it is on the park's page</li>
								 <li>Have fun!</li>
							
						</td>
					</tr>
					<tr>
					 	<td height='30'></td>
					</tr>
					<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d;'>Please visit the <a href='http://www.sportfast.com/games/" . $id . "' style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>game page</a> for more details.</p>
						</td>
					</tr>";
					
		$message .= $this->mailEnd();
		
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');		
		
		
		$mail = new PHPMailer;
		$mail->isSMTP();                                      
		$mail->Host = 'box774.bluehost.com';  				 
		$mail->SMTPAuth = true;                               
		$mail->Username = 'support@sportfast.com';                            
		$mail->Password = 'sportfast.9';                           
		//$mail->SMTPSecure = "ssl";
		
		
		$mail->From = 'support@sportfast.com';
		$mail->FromName = 'Sportfast';
		$mail->addAddress($email);  
		$mail->addReplyTo('donotreply@sportfast.com');
		
		$mail->isHTML(true);                                  
		
		$mail->Subject = $subject;
		
		$mail->Body    = $message;
		$mail->AltBody = $text;
		
		$mail->send();
		
		//mail($email, $subject, $message);
	}
	
	/**
	 * mail email to user that team has been canceled or deleted
	 * @params ($email => where to send,
	 *			$model => Team model)
	 */
	public function mailCancelTeam($email, $model)
	{
		
		$subject  = $model->teamName . ' Deleted';
		$message  = $this->mailStart();
		
		$top = $lower = $text = '';

		$top = "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;'>
					Your " . strtolower($model->sport) . " team, <strong>" . $model->teamName . "</strong>, has been deleted.
				</p>
				<br><br>
				<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;'>
					We hope Sportfast has been useful in coordinating your team.  If there is any way we can improve your experience, please contact us at support@sportfast.com.
				</p>
				<br><br>
				<p style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size: 11px; margin: 0;'>
					This team will not be deleted from the database for another week.  If you believe this is an error, contact support@sportfast.com and we will get it sorted out.  Have fun out there!
				</p>
				";
				 
		$text = "Your " . strtolower($model->sport) . " team, " . $model->teamName . ", has been deleted. \n
				We hope Sportfast has been useful in coordinating your team.  If there is any way we can improve your experience, please contact us at support@sportfast.com. \n
				This team will not be deleted from the database for another week.  If you believe this is an error, contact support@sportfast.com and we will get it sorted out.  Have fun out there!
				";
		
		$message .= "<tr>
						<td>" . $top . "</td>
					 </tr>";
		
		//$text .= "\n Please visit the game page for more details: http://www.sportfast.com/games/" . $id . " \n";

				
		$message .= $this->mailEnd();
		/*
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: games@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
		*/

		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');		
		
		$mail = new PHPMailer;
		$mail->isSMTP();                                      
		$mail->Host = 'box774.bluehost.com';  				 
		$mail->SMTPAuth = true;                               
		$mail->Username = 'support@sportfast.com';                            
		$mail->Password = 'sportfast.9';                           
		//$mail->SMTPSecure = "ssl";
		
		$mail->From = 'support@sportfast.com';
		$mail->FromName = 'Sportfast';
		$mail->addAddress($email);  
		$mail->addReplyTo('donotreply@sportfast.com');
		
		$mail->isHTML(true);                                  
		
		$mail->Subject = $subject;
		
		$mail->Body    = $message;
		$mail->AltBody = $text;
		
		$mail->send();
		//mail($email, $subject, $message);
		/*
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: games@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
		*/
	}
	 
	
	/**
	 * mail new password
	 */
	public function forgotAction()
	{
		$email = $this->getRequest()->getPost('email');
		
		//$email = 'guttenberg.m@gmail.com'; for testing
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
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');	
		
		$text  = "A password reset on your Sportfast account has been requested.  Your new password is: \n";
		$text .= "\n \t " . $password;
		$text .= "\n \n You can set your password to something more meaningful under your account settings: http://www.sportfast.com/users/" . $user->userID . "/settings";
		
		$message  = $this->mailStart();
		$message .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>A password reset on your Sportfast account has been requested.  Your new password is:</p>
							<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size: 2em;font-weight:bold;'>" . $password . "</p>
							<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>You can set your password to something more meaningful under your <a href='http://www.sportfast.com/users/" . $user->userID . "/settings' style='font-family: Arial, Helvetica, Sans-Serif; color: #444;'>Account Settings</a>.</p>
						</td>
					 </tr>";
		$message .= $this->supportSignature();
		$message .= $this->mailEnd();
		
		$mail = new PHPMailer;
		$mail->isSMTP();                                      
		$mail->Host = 'box774.bluehost.com';  				 
		$mail->SMTPAuth = true;                               
		$mail->Username = 'support@sportfast.com';                            
		$mail->Password = 'sportfast.9';                           
		//$mail->SMTPSecure = "ssl";
		
		
		$mail->From = 'support@sportfast.com';
		$mail->FromName = 'Sportfast';
		$mail->addAddress($email);  
		$mail->addReplyTo('donotreply@sportfast.com');
		
		$mail->isHTML(true);                                  
		
		$mail->Subject = 'Password Reset';
		
		
		$mail->Body    = $message;
		$mail->AltBody = $text;
		
		if ($mail->send()) {
			$user->save(false);	
		}
		
		/*
		$subject  = 'Password Reset';
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: support@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";			
				
		mail($email, $subject, $message, $headers);
		*/
		
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
		$this->view->whiteBacking = false;
		
		$post = $this->getRequest()->getPost();
		
		$form = new Application_Form_Contact();
		
		if ($form->isValid($post)) {
			// Success
			$subject  = 'Contact Form';
			$message  = $post['question'] . '<br><br>Browser: ' . $post['browser'];
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
		$inactiveTeams = $this->getRequest()->getParam('inactiveTeams');
		$inactiveGames = $this->getRequest()->getParam('inactiveGames');
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');	
		
		foreach ($inactiveTeams as $array) {
			$team = $array['team'];
			$captain = $array['captain'];
			
			$subject  = 'Your ' . $team->sport . ' Team, ' . $team->teamName  . ', Will Be Deleted';
			$message  = $this->buildWarnInactiveTeamMessage($team);
			/*$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: support@sportfast.com\r\n";	 
			$headers .= "Reply-To: support@sportfast.com\r\n";	
			*/		
			
			$mail = new PHPMailer;
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($captain->username);  
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = $subject;
						
			$mail->Body    = $message['html'];
			$mail->AltBody = $message['text'];
			
			$mail->send();
			
			//mail($captain->username, $subject, $message['html'], $headers);
		}
		
		foreach ($inactiveGames as $array) {
			$game = $array['game'];
			$captain = $array['captain'];
			
			$subject  = 'Deleting Your ' . $game->sport . ' Game at ' . ucwords($game->parkName);
			$message  = $this->buildWarnInactiveGameMessage($game);
			/*$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: support@sportfast.com\r\n";	 
			$headers .= "Reply-To: support@sportfast.com\r\n";	
			*/		
			
			$mail = new PHPMailer;
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($captain->username);  
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = $subject;
						
			$mail->Body    = $message['html'];
			$mail->AltBody = $message['text'];
			
			$mail->send();
			
			//mail($captain->username, $subject, $message['html'], $headers);
		}
			
	}
	
	/**
	 * build message for warnInactive action
	 * @params ($array => array of user details (username, userID, firstName, lastActive))
	 */
	public function buildWarnInactiveUserMessage($array)
	{
		$output  = $this->mailStart();
								
		$output .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>We couldn't help but notice that you haven't visited our site in a while.  
							We're sure you have plenty of excellent excuses--I mean, \"reasons\", for your inactivity.  Just a heads up, 
							in order to keep our database up-to-date, we deactivate inactive users after a period of 60 days.</p>";
					
		$output .= "<br><p><span style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-weight:bold; font-size: 1.25em;'>
					Your account has been inactive for " . $array['lastActive'] . " days.  If you wish to keep your 
					account active, please <a href='http://www.sportfast.com/login' style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-weight:bold;font-size:1em;'>login</a> within the next couple days.</span></p>";
					
		$output .= "<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>If you don't mind your account becoming inactive, then you do not need to do anything.</p>";
		
		$output .= "<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>Thanks, and we hope to see you soon!</p>
						</td>
					</tr>";
		
		$output .= $this->supportSignature(true);
			
						
		$output .= $this->mailEnd();
					 
		
		return $output;
	}
	
	/**
	 * build message for warnInactive action
	 * @params ($array => array of user details (username, userID, firstName, lastActive))
	 */
	public function buildWarnInactiveTeamMessage($team)
	{
		$output  = $this->mailStart();
								
		$output .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size: 14px;'>
							Your " . $team->sport . " team, " . $team->teamName . ", has been inactive for over 30 days.  
							<br>
							<br>
							In order to keep our database up-to-date and accurate for our users, <strong>" . $team->teamName . " will be deleted on " . date('l, F jS', strtotime('+7 days')) . "</strong>.
							<br>
							<br>
							If you wish to keep this team, please click the link below.
							</p>
						</td>
					</tr>";
							
		$text = "Your " . $team->sport . " team, " . $team->teamName . ", has been inactive for over 30 days.  
					In order to keep our database clear of unused teams, " . $team->teamName . " will be deleted on " . date('l, F j', strtotime('+7 days')) . ".
					If you wish to keep this team, please click the link below.";
		
		$text .= "DO NOT DELETE " . $team->teamName . ": http://www.sportfast.com/mail/keep-team/" . $team->teamID;
					
		$output .= "<tr>
						<td height='30'></td>
					</tr>
					<tr>
						<td colspan='3' align='center'>
							<a href='http://www.sportfast.com/mail/keep-team/" . $team->teamID . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 1.5em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.5em;'>Do Not Delete " . strtoupper($team->teamName) . "</a>
						</td>
					</tr>";
					
		$output .= "<tr>
						<td height='30'></td>
					</tr>
					<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size: 14px;'>Please contact us if you have any questions or concerns.</p>
						</td>
					</tr>";
		
		$output .= $this->supportSignature(true);
			
						
		$output .= $this->mailEnd();
					 
		
		return array('html' => $output,
					 'text' => $text);
	}
	
	/**
	 * build message for warnInactive action
	 * @params ($array => array of user details (username, userID, firstName, lastActive))
	 */
	public function buildWarnInactiveGameMessage($game)
	{
		$output  = $this->mailStart();
								
		$output .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size: 14px;'>
							Your weekly " . $game->sport . " game on " . $game->getGameDays() . " has been canceled for past 4 weeks due to a lack of players (on Sportfast, at least).  
							<br>
							<br>
							In order to keep our database up-to-date and accurate for our users, <strong>this " . strtolower($game->sport) . " game will be deleted on " . date('l, F jS', strtotime('+7 days')) . "</strong>.
							<br>
							<br>
							If you wish to keep this game, please click the link below.
							</p>
						</td>
					</tr>";
							
		$text = "Your weekly " . $game->sport . " game on " . $game->getGameDays() . " has been canceled for the past 4 weeks due to a lack of players (on Sportfast, at least). 
					this " . strtolower($game->sport) . " game will be deleted on " . date('l, F jS', strtotime('+7 days')) . ".
					If you wish to keep this game, please click the link below.";
		
		$text .= "DO NOT DELETE GAME: http://www.sportfast.com/mail/keep-game/" . $game->gameID;
					
		$output .= "<tr>
						<td height='30'></td>
					</tr>
					<tr>
						<td colspan='3' align='center'>
							<a href='http://www.sportfast.com/mail/keep-game/" . $game->gameID . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 1.5em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.5em;'>Do Not Delete This Game</a>
						</td>
					</tr>
					<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/games/" . $game->gameID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view game</a>
						</td>
					 </tr>";
					
		$output .= "<tr>
						<td height='30'></td>
					</tr>
					<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size: 14px;'>Please contact us if you have any questions or concerns.</p>
						</td>
					</tr>";
		
		$output .= $this->supportSignature(true);
			
						
		$output .= $this->mailEnd();
					 
		
		return array('html' => $output,
					 'text' => $text);
	}
	
	/**
	 * inform users that they have a game (either 'teamGames' or 'games')
	 */
	public function upcomingGameAction()
	{
		$games = $this->getRequest()->getParam('games');
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');	
		
		foreach ($games['games'] as $game) {
			// Is subscribed game
			$adminMessage = '\n \n GameID: ' . $game->gameID . ' \n';
			foreach ($game->players->getAll() as $user) {
				
				if ($user->doNotEmail == '1') {
					continue;
				}
				
				$adminMessage .= '\n' . $user->fullName . ' ' . $user->username;
			
				$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
				
				$mail = new PHPMailer;
				$mail->isSMTP();                                      
				$mail->Host = 'box774.bluehost.com';  				 
				$mail->SMTPAuth = true;                               
				$mail->Username = 'support@sportfast.com';                            
				$mail->Password = 'sportfast.9';                           
				//$mail->SMTPSecure = "ssl";
				
				
				$mail->From = 'support@sportfast.com';
				$mail->FromName = 'Sportfast';
				$mail->addAddress($user->username);  
				$mail->addReplyTo('donotreply@sportfast.com');
				
				$mail->isHTML(true);                                  
				
				$mail->Subject = $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
				
				$body = $this->buildUpcomingSubscribedGameMessage($game, $user->userID);
				
				$mail->Body    = $body['html'];
				$mail->AltBody = $body['text'];
				
				$mail->send();
				/*
				$subject  = $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
				$message  = $this->buildUpcomingSubcribedGameMessage($game, $user->userID);
				$headers  = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
				$headers .= "From: games@sportfast.com\r\n";	 
				$headers .= "Reply-To: donotreply@sportfast.com\r\n";			
						
				mail($user->username, $subject, $message, $headers);
				*/
			}
			
			$subject  = 'ADMIN: ' . $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
			$message  = $body['html'];
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1" . "\r\n";
			$headers .= "From: support@sportfast.com\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com\r\n";	
						
			mail('guttenberg.m@gmail.com', $subject, $adminMessage, $headers);
		}
		
		if (isset($games['teamGames']['twoDays'])) {
			foreach ($games['teamGames']['twoDays'] as $team) {
				// Is team game that is happening in 2 days
				foreach ($team->players->getAll() as $user) {
					$game = $team->games->_attribs['games'][0];
					$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
					
					$mail = new PHPMailer;
					$mail->isSMTP();                                      
					$mail->Host = 'box774.bluehost.com';  				 
					$mail->SMTPAuth = true;                               
					$mail->Username = 'support@sportfast.com';                            
					$mail->Password = 'sportfast.9';                           
					//$mail->SMTPSecure = "ssl";
					
					
					$mail->From = 'support@sportfast.com';
					$mail->FromName = 'Sportfast';
					$mail->addAddress($user->username);  
					$mail->addReplyTo('donotreply@sportfast.com');
					
					$mail->isHTML(true);                                  
					
					$mail->Subject = $team->teamName . ' has a game at ' . $time . ' on ' . $game->gameDate->format('l');
					
					$body = $this->buildUpcomingTeamGameMessage($team, $game, $user->userID);
					
					$mail->Body    = $body['html'];
					$mail->AltBody = $body['text'];
					
					$mail->send();
					
					/*
					$subject  = $team->teamName . ' has a game at ' . $time . ' on ' . $game->gameDate->format('l');
					$message  = $this->buildUpcomingTeamGameMessage($team, $game, $user->userID);
					$headers  = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
					$headers .= "From: games@sportfast.com\r\n";	 
					$headers .= "Reply-To: donotreply@sportfast.com\r\n";			
							
					mail($user->username, $subject, $message, $headers);
					*/
				}
				
				$subject  = 'ADMIN: ' . $team->teamName . ' has a game at ' . $time . ' on ' . $game->gameDate->format('l');
				$message  = $body['html'];
				$headers  = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
				$headers .= "From: support@sportfast.com\r\n";	 
				$headers .= "Reply-To: donotreply@sportfast.com\r\n";	
							
				mail('guttenberg.m@gmail.com', $subject, $message, $headers);
			}
		}
		
		if (isset($games['teamGames']['today'])) {
			foreach ($games['teamGames']['today'] as $team) {
				// Is team game that is happening in today
				foreach ($team->players->getAll() as $user) {
					$game = $team->games->_attribs['games'][0];
					$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
					
					$mail = new PHPMailer;
					$mail->isSMTP();                                      
					$mail->Host = 'box774.bluehost.com';  				 
					$mail->SMTPAuth = true;                               
					$mail->Username = 'support@sportfast.com';                            
					$mail->Password = 'sportfast.9';                           
					//$mail->SMTPSecure = "ssl";
					
					
					$mail->From = 'support@sportfast.com';
					$mail->FromName = 'Sportfast';
					$mail->addAddress($user->username);  
					$mail->addReplyTo('donotreply@sportfast.com');
					
					$mail->isHTML(true);                                  
					
					$mail->Subject = 'In or out? ' . $team->teamName . ' has a game at ' . $time . ' today';
					
					$body = $this->buildUpcomingTeamGameMessage($team, $game, $user->userID);
					
					$mail->Body    = $body['html'];
					$mail->AltBody = $body['text'];
					
					$mail->send();
					
					/*
					$subject  = 'In or out? ' . $team->teamName . ' has a game at ' . $time . ' today';
					$message  = $this->buildUpcomingTeamGameMessage($team, $game, $user->userID);
					$headers  = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
					$headers .= "From: games@sportfast.com\r\n";	 
					$headers .= "Reply-To: donotreply@sportfast.com\r\n";	
							
							
					mail($user->username, $subject, $message, $headers);
					*/
				}
				
				$subject  = 'ADMIN: In or out? ' . $team->teamName . ' has a game at ' . $time . ' today';
				$message  = $body['html'];
				$headers  = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
				$headers .= "From: support@sportfast.com\r\n";	 
				$headers .= "Reply-To: donotreply@sportfast.com\r\n";	
							
				mail('guttenberg.m@gmail.com', $subject, $message, $headers);
			}
		}
			
	}

	public function testAction()
	{
		$message = "<html>
					<body><style>
					.ExternalClass * {line-height: 100%} 
					
					p {
						margin: 0;
					}
					
					p,div,span,li,ul,a {
						font-family: Arial, Helvetica, Sans-Serif;
						font-size: 14px;
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
					
					.darkest {
						color: #333;
					}
					
					.white {
						color: #fff;
					}
					
					.bold {
						font-weight: bold;
					}
					
					.larger-text {
						font-size: 1.25em;
					}
					
					.largest-text {
						font-size: 2.5em;
					}
					
					.smaller-text {
						font-size: .8em;
					}
					
					.center {
						width: 100%;
						text-align: center;
					}
					
					.green-button {
						padding: .2em 1.25em;
						background: #58bf12;
						color: #fff;
					}
					
					.dark-back {
						background: #333;
					}
					
					</style><table width='98%'>
						<tr><td style='display: block; clear: both;' align='center'>
						<table style='width:100%;max-width: 650px !important;'  border='0' cellpadding='0' cellspacing='0' align='center'>
								
								<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>You are currently subscribed to this basketball game.  Would you like to play?  <span style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>6 players are needed.</span></p>
						</td>
					</tr>
					 
					 <tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>
						 <td align='center' colspan='3'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'><span style='font-size:inherit;font-color:inherit;font-weight:normal'>Pickup</span> Basketball</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>Thursday at 11am</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>Miller Creek Middle School</p>
						 </td>
					 </tr><tr>
						 <td height='20px'></td>
					 </tr>
					 <tr>
						<td align='right' width='325'>
							<a href='http://www.sportfast.com/mail/add-user-subscribe-game/571/11/1' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.5em;'>in</a>
						</td>
						<td width='10'></td>
						<td align='left' width='322'>
							<a href='http://www.sportfast.com/mail/add-user-subscribe-game/571/11/0' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.15em;'>out</a>
						</td>
					 </tr><tr>
							 <td height='17px'></td>
						 </tr>
						 <tr>
							 <td align='center' colspan='3'>
								<a href='http://www.sportfast.com/mail/add-user-subscribe-game/571/11/2' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 1em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 7.2em;'>maybe</a>
							 </td>
						 </tr><tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/games/571' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view game</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='50px'></td>
					 </tr>
					<tr>
						<td>
							<p class='smaller-text' style='font-size:.8em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>To unsubscribe from this game, please click <a href='http://www.sportfast.com/mail/unsubscribe-game/571/11' style='font-size:1em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>here</a>.</p>
						</td>
					</tr>		</table>
						</td>
						</tr>
					</table>
					
					</body>
					</html>";
					
			require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');	
		
				
			$adminMessage .= '\n' . $user->fullName . ' ' . $user->username;
			
			//$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
				
			$mail = new PHPMailer;
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress('check@isnotspam.com');  
			$mail->addAddress('guttenberg.m@gmail.com');
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = 'Ultimate game at 4:30 on Wednesday';
			
			
			$mail->Body    = $message;
			//$mail->AltBody = $body['text'];
			
			$mail->send();
					
	}
			
	
	public function buildUpcomingSubscribedGameMessage($game, $userID) {
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		$message  = $this->mailStart();
		
		$inUrl = "http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "/1";
		$outUrl = "http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "/0";
		$maybeUrl = "http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "/2";
		
		$text  = "You are currently subscribed to this " . strtolower($game->sport) . " game.  Would you like to play? \n";
		$text .= "\n \t " . $game->sport;
		$text .= "\n \t " . $game->gameDate->format('l') . " at " . $time;
		$text .= "\n \t " . $game->park->parkName;
		$text .= "\n \n IN: " . $inUrl;
		$text .= "\n OUT: " . $outUrl;
		$text .= "\n MAYBE: " . $maybeUrl;
		$text .= "\n \n view game: http://www.sportfast.com/games/" . $game->gameID;
		$text .= "\n \n To unsubscribe from this game, remove it from the \"Games\" section of your account settings: http://www.sportfast.com/users/" . $userID . "/settings";
		
		$message .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>You are currently subscribed to this " . strtolower($game->sport) . " game.  Would you like to play?  <span style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>" . $game->minPlayers . " players are needed.</span></p>
						</td>
					</tr>
					 
					 <tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>
						 <td align='center' colspan='3'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->getGameTitle(true) . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->park->parkName . "</p>
						 </td>
					 </tr>"
					 . $this->buildConfirmedButtons($inUrl, $outUrl, $maybeUrl) .
					 "<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/games/" . $game->gameID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view game</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='50px'></td>
					 </tr>
					<tr>
						<td>
							<p class='smaller-text' style='font-size:.8em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>To unsubscribe from this game, please click <a href='http://www.sportfast.com/mail/unsubscribe-game/" . $game->gameID . "/" . $userID . "' style='font-size:1em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>here</a>.</p>
						</td>
					</tr>";
	
		$message .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $message;
		$returnArray['text'] = $text;
		
		return $returnArray;
	}

	public function buildUpcomingTeamGameMessage($team, $game, $userID) {
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		
		if ($game->gameDate->format('j') == date('j')) {
			// Is today
			$pre = "today and you have yet to tell your teammates if you're playing";
		} else {
			$pre = 'coming up';
		}
		
		$message  = $this->mailStart();
		
		$inUrl = "http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "/1";
		$outUrl = "http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "/0";
		$maybeUrl = "http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "/2";
		
		$text  = $team->teamName . " has a game " . $pre . ".  Are you in or out? \n";
		$text .= "\n \t vs. " . $game->opponent;
		$text .= "\n \t " . $game->gameDate->format('l') . " at " . $time;
		$text .= "\n \t vs. " . $game->locationName;
		$text .= "\n \n IN: " . $inUrl;
		$text .= "\n OUT: " . $outUrl;
		$text .= "\n MAYBE: " . $maybeUrl;
		$text .= "\n \n view team: http://www.sportfast.com/teams/" . $team->teamID;
		
		$message .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:1em;text-align:center;'><strong>" . $team->teamName . "</strong> has a game " . $pre . ".  Are you in or out?</p>
						</td>
					</tr>
					 
					 <tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>
						 <td colspan='3' align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>vs. " . $game->opponent . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->locationName . "</p>
						 </td>
					 </tr>
					 " . $this->buildConfirmedButtons($inUrl, $outUrl, $maybeUrl) . 
					 "<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/teams/" . $team->teamID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view team</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='30px'></td>
					 </tr>";
	
		$message .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $message;
		$returnArray['text'] = $text;
		
		return $returnArray;
	}
	
	/**
	 * add user to team game
	 
	public function addUserTeamGameAction()
	{
		$teamGameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$game = new Application_Model_Game();
		$game->teamGameID = $teamGameID;
		
		$fail = $game->addUserToGame($teamGameID, $userID);
		
	}
	*/
	
	/**
	 * send reminder email to users who signed up yesterday but never verified
	 */
	public function remindVerifyAction()
	{
		$users = $this->getRequest()->getParam('users');
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');
		
		$usernames = '';
		
		foreach ($users as $user) {
			
			$mail = new PHPMailer;
			
			$mail->isSMTP();                                      
			$mail->Host = 'box774.bluehost.com';  				 
			$mail->SMTPAuth = true;                               
			$mail->Username = 'support@sportfast.com';                            
			$mail->Password = 'sportfast.9';                           
			//$mail->SMTPSecure = "ssl";
			
			
			$mail->From = 'support@sportfast.com';
			$mail->FromName = 'Sportfast';
			$mail->addAddress($user->username);  
			$mail->addReplyTo('donotreply@sportfast.com');
			
			$mail->isHTML(true);                                  
			
			$mail->Subject = "Sportfast Registration Not Complete";
			
			$body = $this->buildRemindVerifyMessage($user);
			
			$mail->Body    = $body['html'];
			$mail->AltBody = $body['text'];
			
			$mail->send();
			
			$usernames .= '\n ' . $user->username;
		}
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: support@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com\r\n";	
		
		mail('guttenberg.m@gmail.com', 'ADMIN: Remind Verification', $usernames, $headers);
	}
	
	public function buildRemindVerifyMessage($user)
	{
		$output = $this->mailStart();
		$text = '';
		
		$output .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:1em;text-align:center;'>
								Your registration for Sportfast is not complete.  <br><br>To complete registration, please verify this email by <strong>following the link below:</strong>
							</p>
						</td>
					</tr>
					<tr height='40'>
						<td></td>
					</tr>
					<tr>
						<td align='center'>
							<a href='http://www.sportfast.com/signup/verify/" . $user->verifyHash . "' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.1em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>CLICK TO VERIFY</a>
						</td>
					</tr>
					<tr>
						<td height='40'></td>
					</tr>
					<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:.8em;'>Questions? Check out the <a href='http://www.sportfast.com/about/faq' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;text-decoration:underline'>FAQ</a> or contact us any time of day at support@sportfast.com.</p>
						</td>
					</tr>";
		
		$text .= "Your registration for Sportfast is not complete.  To complete registration, please verify this email by following the link below:";
		$text .= "\n \n To verify: http://www.sportfast.com/signup/verify/" . $user->verifyHash;
		
		$output .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $output;
		$returnArray['text'] = $text;
		
		return $returnArray;
	}
	
	
	/**
	 * add user to subscribe game if not already in it (from email)
	 */
	public function addUserSubscribeGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		$confirmed = $this->getRequest()->getParam('param3');
		
		$game = new Application_Model_Game();
		$game->gameID = $gameID;
		
		$auth = Zend_Auth::getInstance();
		
		if (!$auth->hasIdentity()) {
			// Log user in if not already
			$user = new Application_Model_User();
			$user->getUserBy('u.userID', $userID);
			$user->login();
			$auth->getStorage()->write($user);
		}
		
		$fail = $game->addUserToGame($userID, $confirmed);
		
		if ($fail) {
			// Failed to add, game is either full or user is already in game
			if ($auth->hasIdentity()) {
				$session = new Zend_Session_Namespace('addToGame');
				$session->fail = $fail;
			}
			
		} else {
			
			if (Zend_Auth::getInstance()->hasIdentity()) {
				$session = new Zend_Session_Namespace('addToGame');
				$session->fail = ($confirmed == '0' ? 'out' : 'added');
			}
		}
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			// Redirect to game page if logged in
			return $this->_redirect('/games/' . $gameID);
		}
		
		$this->view->gameID = $gameID;
		$this->view->fail = $fail;
		$this->view->confirmed = $confirmed;
		
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		
		
	}
	
	/**
	 * unsubscribe from game
	 */
	public function unsubscribeGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$game = new Application_Model_Game();
		$game->gameID = $gameID;
		
		$fail = $game->unsubscribe($userID);
		/*
		if ($fail) {
			// Failed to add, game is either full or user is already in game
			if (Zend_Auth::getInstance()->hasIdentity()) {
				$session = new Zend_Session_Namespace('addToGame');
				$session->fail = $fail;
			}
			
		} else {
		
			$notification = new Application_Model_Notification();
			$notification->action = 'join';
			$notification->type = 'game';
			$notification->actingUserID = $userID;
			$notification->gameID = $gameID;
			
			$notification->save();
			
			if (Zend_Auth::getInstance()->hasIdentity()) {
				$session = new Zend_Session_Namespace('addToGame');
				$session->fail = 'added';
			}
		}
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			// Redirect to game page if logged in
			return $this->_redirect('/games/' . $gameID);
		}
		*/
		
		$this->view->gameID = $gameID;
		$this->view->fail = $fail;
		
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		
		
	}
		
	/**
	 * add user to team if not already in it (from email)
	 */
	public function addUserTeamAction()
	{
		$teamID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$team = new Application_Model_Team();

		$team->teamID = $teamID;
		
		
		$fail = $team->addUserToTeam($userID);
		
		
		if ($fail) {
			// Failed to add, game is either full or user is already in game
			if (Zend_Auth::getInstance()->hasIdentity()) {
				$session = new Zend_Session_Namespace('addToTeam');
				$session->fail = $fail;
			}
			
		} else {
			/*
			$notification = new Application_Model_Notification();
			
			$notification->action = 'invite';
			$notification->type = 'team';
			$notification->receivingUserID = $userID;
			$notification->teamID = $teamID;
			
			$notification->delete();
			
			$notification->action = 'join';
			$notification->type = 'team';
			$notification->actingUserID = $userID;
			$notification->teamID = $teamID;
			
			$notification->save();
			*/
			
			if (Zend_Auth::getInstance()->hasIdentity()) {
				$session = new Zend_Session_Namespace('addToTeam');
				$session->fail = 'added';
			}
		}
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			// Redirect to game page if logged in
			return $this->_redirect('/teams/' . $teamID);
		}
		
		$this->view->teamID = $teamID;
		$this->view->fail = $fail;
		
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		
		//return $this->_redirect('/teams/' . $teamID);
	}	
	
	/**
	 * add user to team game if not already in it (from email)
	 */
	public function addUserTeamGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		$confirmed = $this->getRequest()->getParam('param3');
		
		
		$gamesMapper = new Application_Model_GamesMapper();
		//$gamesMapper->saveTeamGameConfirmation($userID, $gameID, '1');
		
		
		$teamID = $gamesMapper->getTeamIDFromTeamGameID($gameID);
		
		$game = new Application_Model_Game();
		$game->teamGameID = $gameID;
		$game->addUserToGame($userID, $confirmed);
		
		$this->view->teamID = $teamID;
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			return $this->_redirect('/teams/' . $teamID);
		}
		
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
	}
	
	/**
	 * add user to team game if not already in it (from email) INACTIVE
	 */
	public function removeUserTeamGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$gamesMapper = new Application_Model_GamesMapper();
		$gamesMapper->saveTeamGameConfirmation($userID, $gameID, '0');
		
		$teamID = $gamesMapper->getTeamIDFromTeamGameID($gameID);
		
		$this->view->teamID = $teamID;
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			return $this->_redirect('/teams/' . $teamID);
		}
		
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
	
	}
	
	/**
	 * remove team from to be deleted
	 */
	public function keepTeamAction()
	{
		$teamID = $this->getRequest()->getParam('id');
		
		$team = new Application_Model_Team();
		$team->teamID = $teamID;
		
		$team->remove = '0000-00-00';
		$team->setCurrent('lastActive');
		
		$team->save(false);
		
		return $this->_redirect('/teams/' . $teamID);
	}
	
	/**
	 * remove game from to be deleted
	 */
	public function keepGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		
		$game = new Application_Model_Game();
		
		$game->gameID = $gameID;
		$game->keepGame = date('Y-m-d', strtotime('+30 days'));
		$game->remove = '0000-00-00';
		
		$game->save(false);
		
		
		return $this->_redirect('/games/' . $gameID);
	}
	
	/**
	 * add user as member of game 
	 */
	public function addUserMemberAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$game = new Application_Model_Game();
		$game->gameID = $gameID;
		
		$game->addMemberToGame($userID);
		
		$auth = Zend_Auth::getInstance();
		
		if (!$auth->hasIdentity()) {
			// Log user in if not already
			$user = new Application_Model_User();
			$user->getUserBy('u.userID', $userID);
			$user->login();
			$auth->getStorage()->write($user);
		}
		
		return $this->_redirect('/games/' . $gameID);
	}
	
	/**
	 * set session for user wanting to be member of game 
	 */
	public function inviteUserMemberAction()
	{		
		$gameID = $this->getRequest()->getParam('id');
		$param2 = $this->getRequest()->getParam('param2');
		$email = urldecode($this->getRequest()->getParam('param3'));
		
		$user = new Application_Model_User();
		$exists = $user->getUserBy('u.username', $email);
		
		if ($exists) {
			echo $email;
			return;
			return $this->_redirect('/mail/add-user-member/' . $gameID . '/' . $user->userID);
		}
				
		$session = new Zend_Session_Namespace('signupInvite');
		$session->type = 'game';
		$session->id = $gameID;
		$session->email = ($email == '1' ? '' : $email); // 1 is default value of param3
		$session->confirmed = '3'; // Special confirmed value to show that they do not want to respond, but want to be added as member
		
		$this->_redirect('/games/' . $gameID);
	}
		

	/**
	 * inform users that a game was created for them
	 */
	public function gameCreatedAction()
	{
		$games = $this->getRequest()->getParam('games');
		
		require_once($_SERVER['DOCUMENT_ROOT'] . '/plugins/PHPMailer/class.phpmailer.php');	
		
		foreach ($games as $game) {
			$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
			
			
			/*
			$subject  = $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: games@sportfast.com\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com\r\n";
			*/
			
			foreach ($game->players->getAll() as $user) {
				
				if ($user->noEmail) {
					continue;
				}
				
				$mail = new PHPMailer;
				$mail->isSMTP();                                      
				$mail->Host = 'box774.bluehost.com';  				 
				$mail->SMTPAuth = true;                               
				$mail->Username = 'support@sportfast.com';                            
				$mail->Password = 'sportfast.9';                           
				//$mail->SMTPSecure = "ssl";
				
				
				$mail->From = 'support@sportfast.com';
				$mail->FromName = 'Sportfast';
				$mail->addAddress($email);  
				$mail->addReplyTo('donotreply@sportfast.com');
				
				$mail->isHTML(true);                                  
				
				$mail->Subject = $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
				
				$body = $this->buildGameCreatedMessage($game, $user->userID);
				
				$mail->Body    = $body['html'];
				$mail->AltBody = $body['text'];
				
				$mail->send();
				/*
				$message  = $this->buildGameCreatedMessage($game, $user->userID);
				mail($user->username, $subject, $message, $headers);
				*/
			}
			// Temp email admin to notify when game is created
			mail('guttenberg.m@gmail.com', 'ADMIN: ' . $subject, $this->buildGameCreatedMessage($game, $user->userID), $headers);
		}
	}
	
	public function buildGameCreatedMessage($game, $userID) 
	{
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		$message  = $this->mailStart();
		
		$inUrl = "http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "/1";
		$outUrl = "http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "/0";
		$maybeUrl = "http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "/2";
		
		$text  = "Sportfast created a " . strtolower($game->sport) . " game that you might be interested in.  Wanna play? \n";
		$text .= "\n \t " . $game->getGameTitle(true);
		$text .= "\n \t " . $game->gameDate->format('l') . " at " . $time;
		$text .= "\n \t " . $game->park->parkName;
		$text .= "\n \n IN: " . $inUrl;
		$text .= "\n OUT: " . $outUrl;
		$text .= "\n MAYBE: " . $maybeUrl;
		$text .= "\n \n view game: http://www.sportfast.com/games/" . $game->gameID;
		$text .= "\n \n To unsubscribe from email notifications when a game is created, visit your account settings and turn off email alerts: http://www.sportfast.com/users/" . $userID . "/settings";
		
		$message .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:1em;text-align:center;'>Sportfast created a <strong>" . strtolower($game->sport) . " game</strong> that you might be interested in.  Wanna play?</p>
						</td>
					</tr>
					 
					 <tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>
						 <td colspan='3' align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->getGameTitle(true) . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->park->parkName . "</p>
						 </td>
					 </tr>
					 " . $this->buildConfirmedButtons($inUrl, $outUrl, $maybeUrl) . 
					 "<tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/games/" . $game->gameID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #58bf12;font-size:1.25em;'>view game</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='30px'></td>
					 </tr>
					 ";

		$message .= $this->supportSignature();	
		$message .= "<tr>
					 	<td height='20px'></td>
					 </tr>
					 <tr>
						<td>
							<p class='smaller-text' style='font-size:.8em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>To unsubscribe from email notifications when a game is created, visit <a href='http://www.sportfast.com/users/" . $userID . "/settings' style='font-size:1em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>your account settings</a> and turn off email alerts.</p>
						</td>
					</tr>";
		$message .= $this->mailEnd();
		
		$returnArray = array();
		$returnArray['html'] = $message;
		$returnArray['text'] = $text;
		
		return $returnArray;
	}

	
	
	/**
	 * html header and body function (as well as standard styles) for email
	 */
	public function mailStart()
	{
		
		$output = '<html>
					<body>';
					
		$output .= $this->buildStyle();
		
		/*$output .= "<table width='98%'>
						<tr><td>
						<table width='650' border='0' cellpadding='0' cellspacing='0' align='center'>
						<tr>
							<td width='650' align='center'>
								<table width='650' align='left'>
								";*/
		
		$output .= "<table width='98%'>
						<tr><td style='display: block; clear: both;' align='center'>
						<table style='width:100%;max-width: 650px !important;'  border='0' cellpadding='0' cellspacing='0' align='center'>
								
								";
							
						
									 		
		return $output;
	}
	
	/**
	 * html header and body function (as well as standard styles) for email
	 */
	public function mailEnd()
	{
		$output = "		</table>
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
					.ExternalClass * {line-height: 100%} 
					
					p {
						margin: 0;
					}
					
					p,div,span,li,ul,a {
						font-family: Arial, Helvetica, Sans-Serif;
						font-size: 14px;
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
					
					.darkest {
						color: #333;
					}
					
					.white {
						color: #fff;
					}
					
					.bold {
						font-weight: bold;
					}
					
					.larger-text {
						font-size: 1.25em;
					}
					
					.largest-text {
						font-size: 2.5em;
					}
					
					.smaller-text {
						font-size: .8em;
					}
					
					.center {
						width: 100%;
						text-align: center;
					}
					
					.green-button {
						padding: .2em 1.25em;
						background: #58bf12;
						color: #fff;
					}
					
					.dark-back {
						background: #333;
					}
					
					";
					
		$output .= "</style>";
		
		return $output;
	}

		
	
	public function supportSignature($personalized = false)
	{
		$output  = "<tr>
						<td height='30px'></td>
					</tr>
					<tr>
						<td colspan = '3'>";
		if ($personalized) {
			$output .= "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;'>Marshall G</p>";
		}
		
		$output .= 			"<p class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d; margin: 0;'>Sportfast Support Team</p>
							<p class='smaller-text medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d; font-size: .9em; margin: 0;'>support@sportfast.com</p>
						</td>
					</tr>";
		
		return $output;
	}
	
	/**
	 * email users whether their game has been canceled or not (game happening in next 2 hours from CronController => updateGameStatusAction)
	 */
	public function gameStatusAction()
	{
		$games = $this->getRequest()->getParam('games');
		
		/* used for testing
		$games = array();
		$game = new Application_Model_Game();
		$game->sport = 'basketball';
		$game->gameID = '1';
		$game->totalPlayers = 9;
		$game->date  = '2013-07-10 14:00:00';
		$game->park->parkName = 'Mary Silveira Elementary';
		$user = array('userID' => 1,
					  'userName' => 'something@aol.com');
		$game->players->addUser($user);
		$games['canceled'] = array($game);
		*/
		
		// Email canceled users
		foreach ($games['canceled'] as $game) {
			/*if ($game->sendConfirmation != '1') {
				continue;
			}*/
			
			foreach ($game->players->getAll() as $user) {
				if ($game->userNotConfirmed($user->userID) && $game->gameOn == '2') {
					// Only email maybes and ins
					continue;
				}
				if (!$game->isEmailGameOn($user->userID)) {
					// Does not want game on emails
					continue;
				}
				
				$this->mailCancelGame($user->username, $game);
			}
			mail('guttenberg.m@gmail.com', 'ADMIN: Game Canceled', $game->sport);
		}
		
		// Email game on users
		foreach ($games['on'] as $game) {
			/*if ($game->sendConfirmation != '1') {
				continue;
			}*/
			$emailedUsers = array();
			foreach ($game->players->getAll() as $user) {
				if ($game->userNotConfirmed($user->userID) && $game->gameOn == '2') {
					// Only email maybes and ins
					continue;
				}
				if (!$game->isEmailGameOn($user->userID)) {
					// Does not want game on emails
					continue;
				}
				
				$this->mailGameOn($user->username, $game);
				
				$emailedUsers[] = $user->fullName;
			}

			mail('guttenberg.m@gmail.com', 'ADMIN: Game on', $game->sport . implode('\n',$emailedUsers));
		}
	}
	
	/**
	 * build in, out, and maybe buttons
	 */
	public function buildConfirmedButtons($inUrl, $outUrl, $maybeUrl = false)
	{
		$output = "<tr>
						 <td height='20px'></td>
					 </tr>
					 <tr>
						<td align='right' width='325'>
							<a href='" . $inUrl . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 52px;'>in</a>
						</td>
						<td width='10'></td>
						<td align='left' width='322'>
							<a href='" . $outUrl . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 42px;'>out</a>
						</td>
					 </tr>";
		
		if ($maybeUrl) {			
			$output .=	 "<tr>
							 <td height='17px'></td>
						 </tr>
						 <tr>
							 <td align='center' colspan='3'>
								<a href='" . $maybeUrl . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 1em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 110px;'>maybe</a>
							 </td>
						 </tr>";
		}
		
		return $output;
	}
	
	public function buildMemberButtons($gameID, $userID = false, $email = false)
	{
		if ($userID) {
			$addSrc = "http://www.sportfast.com/mail/add-user-member/" . $gameID . "/" . $userID;
			
		} elseif ($email) {
			$addSrc = "http://www.sportfast.com/mail/invite-user-member/" . $gameID . "/0/" . urlencode($email);
		}
		
		$output = "<tr>
						<td height='17px'></td>
					</tr>
                          <tr>
                            <td align='center' colspan='3'><p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; margin: 0;'><strong>OR</strong></p></td>
						 </tr>
                          <tr>
							 <td height='17px'></td>
						 </tr>
						 <tr>
							 <td align='center' colspan='3'>
								<a href='" . $addSrc . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 1em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1em;'>Don't respond, but I'm interested in receiving emails for this game</a>
							 </td>
						 </tr><tr>
						 <td height='15px'></td>
					 </tr>
					 <tr>
						 <td colspan='3'>
						 	<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; font-weight: bold;text-align:center'>
								You will be added as a member of this game and can opt-in for reminder emails each week.  <br>Any of the above options will add you as a member.  
							</p>
						 </td>
					 ";
					 
		return $output;
	}
	
	/**
	 * lower explanation of what sportfast is, how it works, etc
	 */
	public function sportfastExplanation($textOnly = false)
	{
		if (!$textOnly) {
			// HTML
			$output = "<tr>
							 <td height='40px'></td>
						 </tr>
						 <tr>
							 <td class='dark-back' style='background-color: #333;' bgcolor='#333' colspan='3'>
								<p class='bold white larger-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.25em; color: #fff; font-weight: bold; margin: 0;'>What is Sportfast?</p> 	
							</td>
						 </tr>
						 <tr>
							<td cellpadding='4' colspan='3'>
								<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>Sportfast is designed to simplify the way we find, organize, and manage our recreational sports.  
								It will help you find new pickup games, manage your old ones,
								and track your progress over time.  Our unique algorithms analyze users' age, skill, availability, and location to create competitive and enjoyable pickup games, as well as league teams, near you.  You'll always
								know who is going and how you match up against them, so you never need to feel unwelcome or out-matched.  If you love sports as much as we do, then you should look no further.</p>
							</td>
						</tr>
						<tr>
							 <td height='20px'></td>
						 </tr>
						<tr>
							 <td class='dark-back' style='background-color: #333;' bgcolor='#333' colspan='3'>
								<p class='bold white larger-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.25em; color: #fff; font-weight: bold; margin: 0;'>Do I need to pay?</p> 	
							</td>
						 </tr>
						 <tr>
							<td cellpadding='4' colspan='3'>
								<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>Nope!  There is no cost.  There aren't even ads!  We're in beta, so help us out by giving us your feedback!</p>
							</td>
						</tr>
						<tr>
							 <td height='20px'></td>
						 </tr>
						<tr>
							 <td class='dark-back' style='background-color: #333;' bgcolor='#333' colspan='3'>
								<p class='bold white larger-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.25em; color: #fff; font-weight: bold; margin: 0;'>What sports?</p> 	
							</td>
						 </tr>
						 <tr>
							<td cellpadding='4' colspan='3'>
								<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>Currently, we support basketball, soccer, football, volleyball, tennis, and ultimate frisbee.  Again, we're in beta, so we don't quite have everything that we want yet, but keep checking back for more!</p>
							</td>
						</tr>
						 <tr>
							<td colspan='3'>
								<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>You can read more about us <a href='http://www.sportfast.com/how' class='darkest' style='font-family: Arial, Helvetica, Sans-Serif; color: #444; margin: 0;'>on our website</a>.</p>
							</td>
						</tr>";
		} else {
			// Plain text
			$output  = "What is Sportfast? \n";
			$output .= "Sportfast is designed to simplify the way we find, organize, and manage our recreational sports.  It will help you find new pickup games, manage your old ones, and track your progress over time.  Our unique algorithms analyze users' age, skill, availability, and location to create competitive and enjoyable pickup games, as well as league teams, near you.  You'll always know who is going and how you match up against them, so you never need to feel unwelcome or out-matched.  If you love sports as much as we do, then you should look no further. \n";
			
			$output .= "\n How much does it cost? \n";
			$output .= "Nothing!  There is no cost.  There aren't even ads!  We're in beta, so help us out by giving us your feedback! \n";
			
			$output .= "\n What sports? \n";
			$output .= "Currently, we support basketball, soccer, football, volleyball, tennis, and ultimate frisbee.  Again, we're in beta, so we don't quite have everything that we want yet, but keep checking back for more! \n";

			$output .= "\n What sports? \n";
			$output .= "Currently, we support basketball, soccer, football, volleyball, tennis, and ultimate frisbee.  Again, we're in beta, so we don't quite have everything that we want yet, but keep checking back for more! \n";
			
			$output .= "\n You can read more about us at our website: http://www.sportfast.com/how";
		}
			
		return $output;
	}
	
		
		

}

