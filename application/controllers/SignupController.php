<?php

class SignupController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = 'right';
		$signupSession = new Zend_Session_Namespace('signup');

		
		// Create general signup form
		$form = new Application_Form_Signup();
		if (!empty($signupSession)) {
			// Session not empty, populate form
			foreach($signupSession as $key => $val) {
				$form->populate(array($key => $val));
			}
		}
		
		$messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $messages = $messenger->getMessages('signupError');
		if (!empty($messages[0])) {
			// There were errors sent from validation
			$this->view->errors = $messages[0];
		}
			
		
        $this->view->form = $form;
		
		// Create hidden element form
		$sportForm = new Application_Form_SignupSportForm();
		$this->view->signupSportForm = $sportForm;
		
		// Retrieve all available sports, positions, and types
		$sports = new Application_Model_Sports();
		$allSports = $sports->getAllSportsInfo();
		$this->view->sports = $allSports;
		
		$sportNames = array();
		
		foreach (array_keys($allSports) as $sport) {
			
			$sportNames[] = array('text' => ucwords($sport),
							    'outerClass' => 'copyAvailability-' . strtolower($sport));
		}
		
		array_unshift($sportNames, array('text' => 'None',
										 'outerClass' => 'copyAvailability-none'));
		
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->copyAvailabilityDropdown = $dropdown->dropdown('copyAvailabilityDropdown',
																	$sportNames,
																				 'None'); 
		
		/* testing retrieving all of user's information 
		$user = new Application_Model_User();
		$user->userID = '11';
		$user->getUserSportsInfo();
		echo $user->getSport('football')->getPosition('quarterback')->positionAbbreviation;
		*/
		
	
		
    }
	
	public function testAction()
	{
		/* HOW TO CREATE A DROPDOWN */
        $dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->dropdown = $dropdown->dropdown('cat',array(array('text'  => 'Basketball',
																				   'image' => '/images/global/sports/icons/small/basketball.png',
																				   'color' => 'medium'),
																			 'Football'));
																			 
		$this->view->dropdown2 = $dropdown->dropdown('meow',array(array('text'  => 'Basketball',
																				   'image' => '/images/global/sports/icons/small/basketball.png',
																				   'color' => 'medium'),
																			 'Football',
																			 'Tennis',
																			 'Any'));
																			 
		$typeModel = new Application_Model_SportPosition();
		$typeModel->positionAbbreviation = 'WR';
		$typeModel->sportID  = '2';
		$typeModel->save();
	}
	
	public function validateAction()
	{
		
		$fail = false;
		$form = new Application_Form_Signup();
		$request = $this->getRequest();
	
		if ($request->isPost()) {
			$post = $request->getPost();

			if (!$form->isValid($post)) {
				// Form failed validation, redirect back with error
				Zend_Session::namespaceUnset('signup');
				$signupSession = new Zend_Session_Namespace('signup');
				foreach ($request->getPost() as $key => $val) {
					$signupSession->$key = trim($val);
				}
				
				$errors = array();
				foreach ($form->getMessages() as $section => $errorType) {
					foreach ($errorType as $val) {
						$errors[$section] = str_replace('Value', ucwords($section), $val);
					}
				}
				$this->_helper->FlashMessenger->addMessage($errors, 'signupError');
				
				$this->_redirect('/signup');
				return;
			} else {
				// Form is valid
				$user 	  = new Application_Model_User();
				$userInfo = $user->getAttribs();
				
				// Get user location
				if ($post['noAddress']) {
					// No address provided
					$location = new Application_Model_Location();
					$location->getLocationByZipcode($post['zipcode']);
					$user->userLocation = $location;
				} else {
					// userLocation is set, user latitude and longitude is stored in userLocation
					$location = new Application_Model_Location();
					$location->location = $post['userLocation'];
					$user->userLocation = $location;
				}

				
				// Set city obj for user
				$user->cityID = $user->getCity()
								     ->getCityFromZipcode($post['zipcode'])
									 ->cityID;
				
				// Set verifyHash for user
				$user->verifyHash = md5($_POST['email']);
				
				// Set lastRead to curdate
				$user->setLastReadCurrent();
				
				// Set joined
				$user->setCurrent('joined');
				
				// Convert dob inputs to db format
				$post['dobYear'] = ($post['dobYear'] < date('y') ? '20' : '19') . $post['dobYear'];
				$post['dob'] = $post['dobYear'] . '-' . $post['dobMonth'] . '-' . $post['dobDay'];
				
				foreach ($post as $key => $val) {
					// Update all of User's attribs with appropriate post data
					if ($key == 'signupPassword') {
						// Hash password
						$user->password = $user->hashPassword($val);
						continue;
					}
					
					if ($key == 'userLocation') {
						// Already set location above
						continue;
					}
					
					if ($key == 'email') {
						$key = 'username';
					}
					if (isset($userInfo[$key])) {
						// Key is in userInfo array
						$user->$key = trim(strtolower($val));
					}
				}
				
				$sports = new Application_Model_Sports();
				$sportsArray = $sports->getAllSportsInfo();
				
				$ratings = new Application_Model_Ratings();
				$ratingArray = $ratings->getAvailableRatings('user', 'skill');
				
				foreach ($sportsArray as $sport => $section) {
					if ($post[$sport] !== 'true') {
						// Sport was not selected
						continue;
					}
					
					$sportModel = $user->getSport($sport);
					
					$sportModel->often = $post[$sport . 'Often'];
					$sportModel->sport = $sport;
					
					// Convert rating from slider (0-6) to meaningful rating (64-100)
					//$sportModel->skillInitial = $sportModel->convertSliderToRating($post[$sport . 'Rating']);
					$value = $ratingArray[$ratings->skillRatings[$post[$sport . 'Rating']]]['value'];
					$sportModel->skillInitial =  $value + ($value < 99 ? mt_rand(-1,1) : 0); 
					$sportModel->skillCurrent = $sportModel->skillInitial;
					
					$sportModel->sportsmanship = 80;
					$sportModel->attendance	   = 100;
					
					$formats = explode(',', $post[$sport . 'What']);
					
					foreach	($formats as $format) {
						// Loop through and create user format selection (e.g. Pickup, League, Weekend Tournament)
						 $formatModel = $sportModel->getFormat($format);
						 $formatModel->format = strtolower($format);

					}

					
					if (!empty($post[$sport . 'Type'])) {
						// Type is set
						$types = explode(',', $post[$sport . 'Type']);
						//$typeNames = array();
						foreach ($types as $type) {	
							// Loop through types and create models
							$split = explode('_', strtolower($type));
							$typeName = $split[0];
							$typeSuffix = $split[1];
							
							$typeModel = $sportModel->setType($typeName);
							$typeModel->typeName   = $typeName;
							$typeModel->typeSuffix = $typeSuffix;
							/*
							$type = strtolower($type);
							if (!empty($sportsArray[$sport]['type'][$type])) {
								// $type is typeName
								$typeNames[] = $type;
								//$typeModel   = $sportModel->getType($type);
								//$typeModel->typeName = $type;
							} else {
								// $type is typeSuffix
								$typeSuffixes[] = $type;
							}
							*/
							
						}
						
						/*
						foreach ($typeNames as $typeName) {
							foreach ($typeSuffixes as $typeSuffix) {
								// Create new type model foreach typeName/typeSuffix combo
								$typeModel = $sportModel->getType($typeName);
								$typeModel->typeName   = $typeName;
								$typeModel->typeSuffix = $typeSuffix;
							}
						}
						*/
						
					} else {
						// No type set, create type for base type of "pickup"
						$typeModel = $sportModel->getType('pickup');
						$typeModel->typeName = 'pickup';
					}
					
					
					if (!empty($post[$sport . 'Position'])) {
						// Position is set
						$positions = explode(',', $post[$sport . 'Position']);
						foreach ($positions as $position) {	
							// Loop through types and create models
							$positionModel = $sportModel->getPosition($position);
							$positionModel->positionAbbreviation = $position;
						}

					} else {
						// No position set, create base position "null" for sportID
						$positionModel = $sportModel->getPosition('null');
					}
					
					
					for ($i = 0; $i <= 6; $i++) {
						if (empty($post[$sport . 'Availability' . $i])) {
							// Day has no availabilities saved
							continue;
						}
						$hours = explode(',', $post[$sport . 'Availability' . $i]);
												
						foreach ($hours as $hour) {
							$availabilityModel = $sportModel->setAvailability($i, $hour);
							$availabilityModel->day  = $i;
							$availabilityModel->hour = $hour;
						}
						
					}
					
				}
				
				
				$user->save(true);
				
				
				$subject  = 'Sportfast Account Verification';
				$message  = "<html>
								<head>
								</head>
								<body>
									<table width='98%'>
										<tr><td>
										<table width='650' border='0' cellpadding='0' cellspacing='0' align='center'>
										<tr>
											<td width='650' align='center'>
												<table width='650' align='left'>
													<tr>
														<td>
															<p style='font-family: Arial, Helvetica, Sans-Serif; color: #333;'><span style='font-family: Arial, Helvetica, Sans-Serif; color: #333;font-weight:bold'>Thank you for joining the Sportfast team!  We'll get you up and running in no time.</span>
															<br><br>  You're almost done.  To verify your account, please click on the link below.</p>
														</td>
													</tr>
													<tr>
														<td height='30'></td>
													</tr>
													<tr>
														<td align='center'>
						     								<a href='http://www.sportfast.com/signup/verify/" . $user->verifyHash . "' style='text-decoration: none; font-family: Arial, Helvetica, Sans-Serif; font-size: 2.1em; font-weight: bold; color: #fff; background-color: #58bf12; padding: .2em 1.25em;'>CLICK TO VERIFY</a>
							    						</td>
													</tr>
													<tr>
														<td height='30'></td>
													</tr>
                                                  	<tr>
														<td>
                                                            <p style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;font-size:.8em;'>Questions? Check out the <a href='http://www.sportfast.com/about/faq' style='font-family: Arial, Helvetica, Sans-Serif; color: #8d8d8d;text-decoration:underline'>FAQ</a> or contact us anytime of day at support@sportfast.com.</p>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										</table>
										</td></tr>
									</table>
								</body>
						     </html>";
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";	 
				$headers .= "From: support@sportfast.com \r\n";
				
				
				$this->view->whiteBacking = false;
				$this->view->username = $user->username;
				if(mail($user->username, $subject, $message, $headers)) {
					$this->view->success = true;
					//echo 'An email verification has been sent to ' . $user->username . '.';
				} else {
					$this->view->success = false;
					mail('support@sportfast.com', 'Signup Mail Failure', 'Mail could not be sent to: ' . $user->username, $headers);
					//echo 'An error occured.  Email could not be sent to ' . $user->username . '.';
				}
				
			} 
		} 
		
		
		if (!empty($post['fileName'])) {
			// User has uploaded an image
			
			$userID = $user->userID;
			
			// Save profile pic to permanent location
			//$src = PUBLIC_PATH . $_POST['fileName'];
			$fileInfo = array();
			$fileInfo['src']   = $_POST['fileName'];
			$fileInfo['fileX'] = $_POST['fileX'];
			$fileInfo['fileY'] = $_POST['fileY'];
			$fileInfo['fileHeight'] = $_POST['fileHeight'];
			$fileInfo['fileWidth'] = $_POST['fileWidth'];
			
			$images = Zend_Controller_Action_HelperBroker::getStaticHelper('CreateImages');

			$images->createimages($fileInfo, $userID);
		}
		
	}
	
	public function verifyAction()
	{
		$verifyHash = $this->getRequest()->getParam('verifyHash');
		
		$user = new Application_Model_User();
		
		$verified = $user->verify($verifyHash);
		
		
		if ($verified) {
			// Success
			$auth = Zend_Auth::getInstance();
			
			// if success, $verified stores userID
			$user->getUserBy('u.userID',$verified['userID']);
			$user->login();
			
			$auth->getStorage()->write($user);
			
			if ($verified['already']) {
				// User has already been verified, redirect w/o notifications
				$this->_redirect('/');
			}
			
			$session = new Zend_Session_Namespace('first_visit');
			$session->firstVisit = true;
			
			$notification = new Application_Model_Notification();
			$notification->receivingUserID = $user->userID;
			$notification->action = 'message';
			$notification->details = 'sportfast';
			$notification->cityID = $user->city->cityID;
			$notification->save();
			
			$notification = new Application_Model_Notification();
			$notification->actingUserID = $user->userID;
			$notification->action = 'join';
			$notification->details = 'sportfast';
			$notification->cityID = $user->city->cityID;
			$notification->save();
			
			$session = new Zend_Session_Namespace('signupInvite');
			if ($session->id) {
				// User was invited from email invite from fellow user, create notification for them of invite
				if ($session->type == 'game') {
					$typeModel = new Application_Model_Game();
					$typeModel->gameID = $session->id;
					$captains = $typeModel->getGameCaptains();
					$actingUserID = $captains[0];
				} else {
					$typeModel = new Application_Model_Team();
					$typeModel->teamID = $session->id;
					$captains = $typeModel->getTeamCaptains();
					$actingUserID = $captains[0];
				}
				
				$typeID = $session->type . 'ID';
				$notification = new Application_Model_Notification();
				$notification->receivingUserID = $user->userID;
				$notification->actingUserID = $actingUserID;
				$notification->action = 'invite';
				$notification->type = $session->type;
				$notification->$typeID = $session->id;
				$notification->cityID = $user->city->cityID;
				$notification->save();
				
				Zend_Session::namespaceUnset('signupInvite');
			}
			
			$messages = new Application_Model_Messages();
			$messageGroupID = $messages->messageGroupExists($user->userID, 0);
			
			$message = new Application_Model_Message();
			$message->messageGroupID = $messageGroupID;
			$message->sendingUserID = 0;
			$message->receivingUserID = $user->userID;
			$message->message = file_get_contents(PUBLIC_PATH . '/txt/welcome.txt');
			
			$message->save();
			
			
			$this->_redirect('/');
		} else {
			// Failure
			$this->view->narrowColumn = false;
		}
	}
	
	
	public function createUserAction()
	{
		for ($x = 0; $x < 10; $x++) {
		$user 	  = new Application_Model_User();
		$userInfo = $user->getAttribs();
		

		// userLocation is set, user latitude and longitude is stored in userLocation
		$location = new Application_Model_Location();
		$longitude = array('upper' => 38.113759,
						   'lower' => 37.936479);
		$latitude = array('upper' => -122.601637,
						   'lower' => -122.498984);
		$point = $this->getPoint($latitude, $longitude); 
		$location->location = 'POINT(' . $point['longitude'] . ' ' . $point['latitude'] . ')';
		$user->userLocation = $location;
		

		
		// Set city obj for user
		$cities = $user->getCity()->getZipcodesWithin($latitude, $longitude);

		$user->cityID = $cities[mt_rand(0, (count($cities) - 1))];
		
		
		// Set lastRead to curdate
		$user->setLastReadCurrent();
		
		// Set joined
		$user->setCurrent('joined');
		
		// Convert dob inputs to db format
		$user->dob = $this->getDOB(mt_rand(18, 55));
		$user->username = $this->generateUsername();
		
		$sex = mt_rand(1, 10);
		
		if ($sex < 8) {
			$sex = 'm';
		} else {
			$sex = 'f';
		}
		
		$user->sex = $sex;
		$user->weight = mt_rand(110, 200);
		$user->height = mt_rand(65, 76);
		
		$user->firstName = $this->getFirstName($sex);
		$user->lastName = $this->getLastName();
		$user->active = 1;
	
		
		$sports = new Application_Model_Sports();
		$sportsArray = $sports->getAllSportsInfo();
		
		$numSports = mt_rand(1, 4);
		
		$sports = array_keys($sportsArray);
		
		shuffle($sports);
		
		for ($c = 0; $c < $numSports; $c++) {
			$sport = $sports[$c];
			
			$sportModel = $user->getSport($sport);
			
			$often = array('0', '2', '7', '30');
			$sportModel->often = $this->random($often);
			$sportModel->sport = $sport;
			
			// Convert rating from slider (0-6) to meaningful rating (64-100)
			$sportModel->skillInitial = $sportModel->convertSliderToRating(mt_rand(0, 5)); 
			$sportModel->skillCurrent = $sportModel->skillInitial;
			
			$sportModel->sportsmanship = 80;
			$sportModel->attendance	   = 100;
			
			$formatOptions = array('pickup', 'league', 'weekend tournament');
			shuffle($formatOptions);
			$numFormats = mt_rand(1,2);
			for ($i = 0; $i < $numFormats; $i++) {
				$formats[] = $formatOptions[$i];
			}
			
			foreach	($formats as $format) {
				// Loop through and create user format selection (e.g. Pickup, League, Weekend Tournament)
				 $formatModel = $sportModel->getFormat($format);
				 $formatModel->format = strtolower($format);

			}

			
			if ($sport == 'tennis') {
				// Type is set
				$typeNames = array('singles', 'doubles');
				
				$typeSuffixes = array('rally', 'match');
				
				
				foreach ($typeNames as $typeName) {
					foreach ($typeSuffixes as $typeSuffix) {
						// Create new type model foreach typeName/typeSuffix combo
						$typeModel = $sportModel->getType($typeName);
						$typeModel->typeName   = $typeName;
						$typeModel->typeSuffix = $typeSuffix;
					}
				}
			} else {
				// No type set, create type for base type of "pickup"
				$typeModel = $sportModel->getType('pickup');
				$typeModel->typeName = 'pickup';
			}
			
			
			if (in_array('league', $formats) && $sport != 'tennis') {
				// Position is set
				
				$positions = array_keys($sportsArray[$sport]['position']);
				
				$position = $this->random($positions);
				

				$positionModel = $sportModel->getPosition($position);
				$positionModel->positionAbbreviation = $position;
				

			} else {
				// No position set, create base position "null" for sportID
				$positionModel = $sportModel->getPosition('null');
			}
			
			$availability = array();
			
			for ($i = 0; $i < 5; $i++) {
				$day = mt_rand(0, 6);
				if (isset($availability[$day])) {
					continue;
				}
				$availability[$day] = array();
				
				for ($d = 0; $d < 7; $d++) {
					
					$hour = mt_rand(8, 19);
					if (in_array($hour, $availability[$day])) {
						continue;
					}
					$availability[$day][] = $hour;
				}
			}
			

										
			foreach ($availability as $day => $inner) {
				foreach ($inner as $hour) {
					$availabilityModel = $sportModel->setAvailability($day, $hour);
					$availabilityModel->day  = $day;
					$availabilityModel->hour = $hour;
				}
			}
				
			
			
		}
		
		$user->save(true);
		}
	}
	
	/**
	 * create fake users that will be used to fill games
	
	public function createFakeUserAction()
	{
		for ($x = 0; $x < 10; $x++) {
			$user 	  = new Application_Model_User();
			$user->fake = true;
			
	
			// userLocation is set, user latitude and longitude is stored in userLocation
			$location = new Application_Model_Location();
			$longitude = array('upper' => 38.113759,
							   'lower' => 37.936479);
			$latitude = array('upper' => -122.601637,
							   'lower' => -122.498984);
			$point = $this->getPoint($latitude, $longitude); 
			$location->location = 'POINT(' . $point['longitude'] . ' ' . $point['latitude'] . ')';
			$user->userLocation = $location;
			
	
			
			// Set city obj for user
			$cities = $user->getCity()->getZipcodesWithin($latitude, $longitude);
	
			$user->cityID = $cities[mt_rand(0, (count($cities) - 1))];
			
			
			// Set lastRead to curdate
			$user->setLastReadCurrent();
			
			// Set joined
			$user->setCurrent('joined');
			
			// Convert dob inputs to db format
			$user->dob = $this->getDOB(mt_rand(18, 55));
			$user->username = $this->generateUsername();
			
			$sex = mt_rand(1, 10);
			
			if ($sex < 10) {
				$sex = 'm';
			} else {
				$sex = 'f';
			}
			
			$user->sex = $sex;
			$user->weight = mt_rand(110, 200);
			$user->height = mt_rand(65, 76);
			
			$user->firstName = $this->getFirstName($sex);
			$user->lastName = $this->getLastName();
			$user->active = 1;
		
			
			$sports = new Application_Model_Sports();
			$sportsArray = $sports->getAllSportsInfo();
			
			//$numSports = mt_rand(1, 4);
			
			$sports = array_keys($sportsArray);
			
			//shuffle($sports);
			
			for ($c = 0; $c < count($sports); $c++) {
				$sport = $sports[$c];
				
				$sportModel = $user->getSport($sport);
				
				$often = array('0', '2', '7', '30');
				$sportModel->often = $this->random($often);
				$sportModel->sport = $sport;
				
				// Convert rating from slider (0-6) to meaningful rating (64-100)
				$sportModel->skillInitial = $sportModel->convertSliderToRating(mt_rand(0, 5)); 
				//$sportModel->skillCurrent = $sportModel->skillInitial;
				
				$sportModel->sportsmanship = 80;
				$sportModel->attendance	   = 100;
				
				$formatOptions = array('pickup', 'league');
				
				//$numFormats = mt_rand(1,2);
				for ($i = 0; $i < count($formatOptions); $i++) {
					$formats[] = $formatOptions[$i];
				}
				
				foreach	($formats as $format) {
					// Loop through and create user format selection (e.g. Pickup, League, Weekend Tournament)
					 $formatModel = $sportModel->getFormat($format);
					 $formatModel->format = strtolower($format);
	
				}
	
				
				if ($sport == 'tennis') {
					// Type is set
					$typeNames = array('singles', 'doubles');
					
					$typeSuffixes = array('rally', 'match');
					
					
					foreach ($typeNames as $typeName) {
						foreach ($typeSuffixes as $typeSuffix) {
							// Create new type model foreach typeName/typeSuffix combo
							$typeModel = $sportModel->getType($typeName);
							$typeModel->typeName   = $typeName;
							$typeModel->typeSuffix = $typeSuffix;
						}
					}
				} else {
					// No type set, create type for base type of "pickup"
					$typeModel = $sportModel->getType('pickup');
					$typeModel->typeName = 'pickup';
				}
				
				
				if (in_array('league', $formats) && $sport != 'tennis') {
					// Position is set
					
					$positions = array_keys($sportsArray[$sport]['position']);
					
					$position = $this->random($positions);
					
	
					$positionModel = $sportModel->getPosition($position);
					$positionModel->positionAbbreviation = $position;
					
	
				} else {
					// No position set, create base position "null" for sportID
					$positionModel = $sportModel->getPosition('null');
				}
				
				$availability = array();
	
				for ($d = 0; $d < 7; $d++) {
					for ($h = 8; $h < 22; $h++) {
						$availabilityModel = $sportModel->setAvailability($d, $h);
					}
				}					
				
			}
			
			$user->save(true);
			}
	}
	 */
	
	public function getPoint($latitude, $longitude)
	{
		$longitude = mt_rand($longitude['lower'] * 10000, $longitude['upper'] * 10000)/10000;
		
		$latLow = 1;
		if ($latitude['lower'] < 0) {
			// Negative 
			$latLow = -1;
		}

		$latitude = mt_rand(abs($latitude['lower']) * 10000, abs($latitude['upper']) * 10000)/10000;
		
		$latitude = $latitude * $latLow;
		
		return array('latitude' => $latitude,
					 'longitude' => $longitude);
	}
	
	public function getDOB($age)
	{
		$month = mt_rand(1,12);
		$day = mt_rand(1, 28);
		$year = date('Y', strtotime('-' . $age . ' years'));
		
		$month = ($month < 10 ? '0' . $month : $month);
		$day = ($day < 10 ? '0' . $day : $day);
		
		return $year . '-' . $month . '-' . $day;
	}
	
	public function generateUsername()
	{
		$string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$username = '';
		for ($i = 0; $i < 8; $i++) {
			$username .= $string[mt_rand(0, (strlen($string) - 1))];
		}
		
		$username .= '@';
		$ext = array('gmail.com',
					 'aol.com');
					 
		$username .= $ext[mt_rand(0,(count($ext) - 1))];
		
		return $username;
	}
	
	public function getFirstName($sex)
	{
		if ($sex == 'm') {
			$first = array('john', 'james', 'jonathan', 'tyler', 'scott', 'jimmy', 'jason', 'chris', 'brendan', 'brad', 'marcus', 'zach', 'will', 'daniel', 'anthony', 'david', 'tom', 'paul', 'george', 'steven', 'jeff');
		} else {
			$first = array('lily', 'abby', 'mary', 'cierra', 'claire', 'emily', 'jennifer', 'iris', 'robin', 'lorie', 'lucy', 'lauren', 'susan', 'lisa', 'karen', 'helen', 'sharon', 'angela');
		}
				
		$firstName = $first[mt_rand(0, (count($first) - 1))];
		
		return $firstName;
	}
	
	public function getLastName()
	{
		$last = array('smith', 'johnston', 'williams', 'jones', 'brown', 'davis', 'wilson', 'anderson', 'white', 'martin', 'garcia', 'clark', 'lewis', 'lee', 'walker', 'hall', 'allen',' bell', 'cox', 'bailey', 'howard');
		return $last[mt_rand(0, (count($last) - 1))];
	}
	
	public function random($array)
	{
		return $array[mt_rand(0, (count($array) - 1))];
	}
		
		


}


