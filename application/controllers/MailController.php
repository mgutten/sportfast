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
			$message  = $this->buildInviteGameMessage($this->view->user, $typeModel);
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: " . $this->view->user->username . "\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
					
	
			mail($email, $subject, $message, $headers);
		}
		
		
		
		$this->_redirect('/' . $types . '/' . $typeID);
			
	}
	
	public function buildInviteGameMessage($actingUser, $typeModel)
	{
		$output = $this->mailStart();
		$type = ($typeModel instanceof Application_Model_Game ? 'game' : 'team');
						
		if ($type == 'game') {
			
			$time = ($typeModel->gameDate->format('i') > 0 ? $typeModel->gameDate->format('g:ia') : $typeModel->gameDate->format('ga'));
			$main = "<td align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->sport . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->park->parkName . "</p>
						 </td>";
			
			//$intro = "Sportfast will allow us to see who is going, to find players if we need them, to receive any updates, and to track stats on ourselves as well as our game.";
			$intro = array("see who is going",
						   "receive any game-related updates",
						   "track your own stats as well as our game's",
						   "find local pickup games for different sports");
			
			$id = $typeModel->gameID;
		} else {
			// Team
			$main = "<td align='center'>
							<p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->teamName . "</p>
							<p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $typeModel->sport . " Team</p>
						</td>";
						
			//$intro = "Sportfast will allow everyone to quickly say whether they're \"in\" or \"out\" for our next game, to see our upcoming schedule, to receive automatic reminders for upcoming games, and to track our progress over the season.";
			$intro = array("see who is \"in\" or \"out\" for our next game",
						   "view our upcoming schedule",
						   "receive automatic reminders for upcoming games",
						   "track our progress over the season",
						   "find local pickup games for different sports");
			
			$id = $typeModel->teamID;
		}				
		
		$output .= "<tr><td><p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; margin: 0;'>I've moved our " . $typeModel->sport . " " . $type . " to Sportfast so we can organize easier.  With Sportfast, you can:</p>
						<ul class='bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; font-weight: bold;'>";
		//<br><br>" . $intro . "  It's designed specifically to help organize, find, and manage recreational sports.				
		foreach ($intro as $point) {
			$output .= "<li style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333;'>" . $point . "</li>";
		}
		$output .= "</ul>
						<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #333; margin: 0;'>Signup is required, but it only takes a minute <span style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d; margin: 0;'>(it's free)</span>, and it's going to make our lives much easier.
						<br><br>I'll see you out there!
						<br>" . $actingUser->shortName . "</p>
					</td></tr>";
					
	
		$output .= "<tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>";
		$output .= 		$main;			 
		
		$output .=	"</tr>
					 <tr>
						 <td height='20px'></td>
					 </tr>
					 <tr>
						<td align='center'>
							<a href='http://www.sportfast.com/mail/invite-user-" . $type . "/" . $id . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>Join</a>
						</td>
					 </tr>
					 <tr>
						 <td height='10px'></td>
					 </tr>
					 <tr>
						 <td>
						 	<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; color: #58bf12; font-weight: bold;text-align:center'>You will not receive any more reminders regarding this " . $type . " or be able to view its details unless you join. 
								There really is no catch or obligation, we built this because we love sports!</p>
						 </td>
					 </tr>
					 <tr>
						 <td height='40px'></td>
					 </tr>
					 <tr>
						 <td class='dark-back' style='background-color: #333;' bgcolor='#333'>
							<p class='bold white larger-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.25em; color: #fff; font-weight: bold; margin: 0;'>What is Sportfast?</p> 	
						</td>
					 </tr>
					 <tr>
					 	<td cellpadding='4'>
							<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>Sportfast is designed to simplify the way we find, organize, and manage our recreational sports.  
							It will help you find new pickup games, manage your old ones,
							and track your progress over time.  Our complex algorithms analyze users' age, skill, availability, and location to create competitive and enjoyable pickup games, as well as league teams, near you.  You'll always
							know who is going and how you match up against them, so you never need to feel unwelcome or out-matched.  If you love sports as much as we do, then you should look no further.</p>
						</td>
					</tr>
					<tr>
						 <td height='20px'></td>
					 </tr>
					<tr>
						 <td class='dark-back' style='background-color: #333;' bgcolor='#333'>
							<p class='bold white larger-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.25em; color: #fff; font-weight: bold; margin: 0;'>How much does it cost?</p> 	
						</td>
					 </tr>
					 <tr>
					 	<td cellpadding='4'>
							<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>It's free!  Totally and utterly free.  There aren't even ads!  We're in beta, so help us out by giving us your feedback!</p>
						</td>
					</tr>
					<tr>
						 <td height='20px'></td>
					 </tr>
					<tr>
						 <td class='dark-back' style='background-color: #333;' bgcolor='#333'>
							<p class='bold white larger-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.25em; color: #fff; font-weight: bold; margin: 0;'>What sports?</p> 	
						</td>
					 </tr>
					 <tr>
					 	<td cellpadding='4'>
							<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>Currently, we support basketball, soccer, football, volleyball, tennis, and ultimate frisbee.  Again, we're in beta, so we don't quite have everything that we want yet, but keep checking back for more!</p>
						</td>
					</tr>
					 <tr>
					 	<td>
							<p class='medium smaller-text' style='font-family: Arial, Helvetica, Sans-Serif; font-size: .8em; color: #8d8d8d; margin: 0;'>You can read more about us <a href='http://www.sportfast.com/how' class='darkest' style='font-family: Arial, Helvetica, Sans-Serif; color: #444; margin: 0;'>on our website</a>.</p>
						</td>
					</tr>";
		
						
		$output .= $this->mailEnd();
					 
		
		return $output;
	}
	
	/**
	 * "join" button clicked from invite to join sportfast email (want to join this game)
	 */
	public function inviteUserGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');

		$session = new Zend_Session_Namespace('signupInvite');
		$session->type = 'game';
		$session->id = $gameID;
		
		$this->_redirect('/signup');
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
		
		$time = ($model->gameDate->format('i') > 0 ? $model->gameDate->format('g:ia') : $model->gameDate->format('ga'));
		
		$subject  = $sport . ' Game Canceled';
		$message  = $this->mailStart();
		
		$message .= "<tr>
						<td align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $model->sport . " Game Canceled</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $model->gameDate->format('l') . " at " . $time . "</p>
						 </td>
					 </tr>";

		$message .= "<tr>
						<td height='20'></td>
					</tr>
					<tr>
						<td align='center'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1em; color: #333; margin: 0;'>Please visit the <a href='http://www.sportfast.com/games/" . $id . "' style='font-family: Arial, Helvetica, Sans-Serif; color: #333;margin: 0;'>game page</a> for more details.</p>
						</td>
					</tr>";
		
		if ($model->cancelReason != '') {
			
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
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: games@sportfast.com\r\n";	 
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
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		
		$subject  = $game->sport . ' Game On';
		$message  = $this->mailStart();
		
		$message .= "<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333; margin: 0;'>Your " . strtolower($game->sport) . " game is on!  See you out there!</p>
					 <tr>
					 	<td height='30'></td>
					 </tr>
					 <tr>
					 	<td align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>Today at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->park->parkName . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->totalPlayers . " players</p>
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
		
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: games@sportfast.com\r\n";	 
		$headers .= "Reply-To: donotreply@sportfast.com" . "\r\n";
				
				
		mail($email, $subject, $message, $headers);
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
		
		$user->save(false);	
		
		$subject  = 'Password Reset';
		$message  = $this->mailStart();
		$message .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>A password reset on your Sportfast account has been requested.  Your new password is:</p>
							<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size: 2em;font-weight:bold;'>" . $password . "</p>
							<br><p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>You can set your password to something more meaningful under your <a href='/users/" . $user->userID . "/settings' style='font-family: Arial, Helvetica, Sans-Serif; color: #444;'>Account Settings</a>.</p>
						</td>
					 </tr>";
		$message .= $this->supportSignature();
		$message .= $this->mailEnd();
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: support@sportfast.com\r\n";	 
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
								
		$output .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>We couldn't help but notice that you haven't visited our site in a while.  
							We're sure you have plenty of excellent excuses--I mean, \"reasons\", for your inactivity.  Just a heads up, 
							in order to keep our database up-to-date, we must deactivate inactive users after a period of 60 days.</p>";
					
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
	 * inform users that they have a game (either 'teamGames' or 'games')
	 */
	public function upcomingGameAction()
	{
		$games = $this->getRequest()->getParam('games');
		
		foreach ($games['games'] as $game) {
			// Is subscribed game
			foreach ($game->players->getAll() as $user) {
				
				if ($user->doNotEmail) {
					continue;
				}
				$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
				
				$subject  = $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
				$message  = $this->buildUpcomingSubcribedGameMessage($game, $user->userID);
				$headers  = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
				$headers .= "From: info@sportfast.com\r\n";	 
				$headers .= "Reply-To: donotreply@sportfast.com\r\n";			
						
				mail($user->username, $subject, $message, $headers);
			}
		}
		
		foreach ($games['teamGames'] as $team) {
			// Is team game
			foreach ($team->players->getAll() as $user) {
				$game = $team->games->_attribs['games'][0];
				$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
				
				$subject  = $team->teamName . ' has a game at ' . $time . ' on ' . $game->gameDate->format('l');
				$message  = $this->buildUpcomingTeamGameMessage($team, $game, $user->userID);
				$headers  = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
				$headers .= "From: info@sportfast.com\r\n";	 
				$headers .= "Reply-To: donotreply@sportfast.com\r\n";			
						
				mail($user->username, $subject, $message, $headers);
			}
		}
			
	}
	
	public function buildUpcomingSubcribedGameMessage($game, $userID) {
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		$message  = $this->mailStart();
		
		$message .= "<tr>
						<td>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'>You are currently subscribed to this " . strtolower($game->sport) . " game.  Would you like to play?  <span style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>" . $game->minPlayers . " players are needed.</span></p>
						</td>
					</tr>
					 
					 <tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>
						 <td align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->sport . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->park->parkName . "</p>
						 </td>
					 </tr>
					 <tr>
						 <td height='20px'></td>
					 </tr>
					 <tr>
					 	<td align='center'>
							<a href='http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>in</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center'>
							<a href='http://www.sportfast.com/games/" . $game->gameID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:1.25em;'>view game page</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='50px'></td>
					 </tr>
					<tr>
						<td>
							<p class='smaller-text' style='font-size:.8em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>To unsubscribe from this game, visit <a href='http://www.sportfast.com' style='font-size:1em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>your account</a> and go to the Games section under your Account Settings.</p>
						</td>
					</tr>";
	
		$message .= $this->mailEnd();
		
		return $message;
	}

	public function buildUpcomingTeamGameMessage($team, $game, $userID) {
		
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		
		$message  = $this->mailStart();
		
		$message .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:1em;'>" . $team->teamName . " has a game coming up.  Are you in or out?</p>
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
					 <tr>
						 <td height='20px'></td>
					 </tr>
					 
					<tr>
						<td align='right' width='320'>
							<a href='http://www.sportfast.com/mail/add-user-team-game/" . $game->teamGameID . "/" . $userID . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>in</a>
						</td>
						<td width='10'></td>
						<td align='left' width='320'>
							<a href='http://www.sportfast.com/mail/remove-user-team-game/" . $game->teamGameID . "/" . $userID . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>out</a>
						</td>
					</tr>
					 
					 <tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/teams/" . $team->teamID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:1.25em;'>view team page</a>
						</td>
					 </tr>
					 <tr>
					 	<td height='30px'></td>
					 </tr>";
	
		$message .= $this->mailEnd();
		
		return $message;
	}
	
	/**
	 * add user to subscribe game if not already in it (from email)
	 */
	public function addUserSubscribeGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$game = new Application_Model_Game();
		
		$game->addUserToGame($gameID, $userID);
		
		return $this->_redirect('/games/' . $gameID);
	}
	
	/**
	 * add user to team game if not already in it (from email)
	 */
	public function addUserTeamGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$gamesMapper = new Application_Model_GamesMapper();
		$gamesMapper->saveTeamGameConfirmation($userID, $gameID, '1');
		
		$teamID = $gamesMapper->getTeamIDFromTeamGameID($gameID);
		
		return $this->_redirect('/teams/' . $teamID);
	}
	
	/**
	 * add user to team game if not already in it (from email)
	 */
	public function removeUserTeamGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$userID = $this->getRequest()->getParam('param2');
		
		$gamesMapper = new Application_Model_GamesMapper();
		$gamesMapper->saveTeamGameConfirmation($userID, $gameID, '0');
		
		$teamID = $gamesMapper->getTeamIDFromTeamGameID($gameID);
		
		return $this->_redirect('/teams/' . $teamID);
	}
	
	/**
	 * inform users that a game was created for them
	 */
	public function gameCreatedAction()
	{
		$games = $this->getRequest()->getParam('games');
		
		foreach ($games as $game) {
			$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
			$subject  = $game->sport . ' game at ' . $time . ' on ' . $game->gameDate->format('l');
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: games@sportfast.com\r\n";	 
			$headers .= "Reply-To: donotreply@sportfast.com\r\n";
			
			foreach ($game->players->getAll() as $user) {
				
				if ($user->noEmail) {
					continue;
				}
				$message  = $this->buildGameCreatedMessage($game, $user->userID);
				mail($user->username, $subject, $message, $headers);
			}
		}
	}
	
	public function buildGameCreatedMessage($game, $userID) 
	{
		$time = ($game->gameDate->format('i') > 0 ? $game->gameDate->format('g:ia') : $game->gameDate->format('ga'));
		$message  = $this->mailStart();
		
		$message .= "<tr>
						<td colspan='3'>
							<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-size:1em;'>We created a " . strtolower($game->sport) . " game that you might be interested in.  Wanna play?</p>
						</td>
					</tr>
					 
					 <tr>
						 <td height='30px'></td>
					 </tr>
					 <tr>
						 <td colspan='3' align='center'>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->getGameTitle() . "</p>
							 <p class='largest-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 2.5em; color: #333; font-weight: bold; margin: 0;'>" . $game->gameDate->format('l') . " at " . $time . "</p>
							 <p class='larger-text bold' style='font-family: Arial, Helvetica, Sans-Serif; font-size: 1.75em; color: #333; font-weight: bold; margin: 0;'>" . $game->park->parkName . "</p>
						 </td>
					 </tr>
					 <tr>
						 <td height='20px'></td>
					 </tr>
					 
					<tr>
						<td align='center'>
							<a href='http://www.sportfast.com/mail/add-user-subscribe-game/" . $game->gameID . "/" . $userID . "' class='green-button largest-text bold' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.2em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>in</a>
						</td>
					</tr>
					 
					 <tr>
					 	<td height='10px'></td>
					 </tr>
					 <tr>
					 	<td align='center' colspan='3'>
							<a href='http://www.sportfast.com/games/" . $game->gameID . "' class='medium' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:1.25em;'>view game page</a>
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
							<p class='smaller-text' style='font-size:.8em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>To stop receiving notifications when a game is created, visit <a href='http://www.sportfast.com' style='font-size:1em;font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;'>your account</a> and go to your Account Settings.</p>
						</td>
					</tr>";
		$message .= $this->mailEnd();
		
		return $message;
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
								<table width='650' align='left'>
								";
							
						
									 		
		return $output;
	}
	
	/**
	 * html header and body function (as well as standard styles) for email
	 */
	public function mailEnd()
	{
		$output = "				</table>
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
	
		
		

}

