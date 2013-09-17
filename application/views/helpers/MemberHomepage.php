<?php
class Application_View_Helper_MemberHomepage
{	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * build member homepage for controller=>index action=>index and user logged in
	 * @return $output
	 */
	public function memberHomepage() 
	{
		
		$user = $this->_view->user;

		if ($this->_view->firstVisit) {
			// First visit
			$this->_view->placeholder('absolute')->captureStart();
			
			echo $this->_view->alert()->start('first-time', 'Welcome to your dashboard!');
			
			echo "<img src='/images/global/logo/logo/green/medium.png' class='left'/>";
			echo "<div class='left member-first-time-overview'>";
			echo "<p class='clear darkest heavy'>Your dashboard is your home while at Sportfast.  From it, you can: </p>
					<ul class='clear margin-top heavy green'>
						<li>Find games or teams</li> 
						<li>View your upcoming schedule</li> 
						<li>Access your profile</li></ul>";
			echo "</div>";
			
			
			echo "<p class='clear smaller-text member-first-time-sub larger-margin-top darkest'>We will notify you via email when a game is created matching your preferences.</p>";
			echo "<p class='clear larger-margin-top medium'>Then again, maybe you're here to   
					<a href='/create' class='darkest underline'> organize your own game or team.</a></p>";
			
			echo "<p class='button clear heavy member-first-time-button'>Continue to dashboard</p>";
			
			echo $this->_view->alert()->end();
			echo $this->_view->partial('partials/global/alertBack.phtml');
			
			$this->_view->placeholder('absolute')->captureEnd();
		}
		
		if ($this->_view->usersInArea && !$this->_view->rateGame) {
			// Not enough users in area, let user know
			$this->_view->placeholder('absolute')->captureStart();
			
			$newUsers = ($this->_view->usersInArea['newUsers'] == 0 ? mt_rand(0,1) : $this->_view->usersInArea['newUsers']);
			$totalUsers = $this->_view->usersInArea['totalUsers'];
			
			$users = ($newUsers == 1 ? 'user has' : 'users have');
			
			echo $this->_view->alert()->start('more-users', 'Welcome back!');
			
			echo "<p class='clear dark'>We are working hard to get our name out there, but it takes time.</p>";
			echo "<p class='clear width-100 center larger-text darkest heavy'>There are now 
					<span class='largest-text inherit'>" . $totalUsers . "</span> users in your area.</p>";
			echo "<p class='clear dark larger-margin-top'>We won't make any games until there are enough users to show up.</p>";
			echo "<p class='clear dark larger-margin-top'>Tell your friends and let's get some games together!</p>";
			
			echo $this->_view->alert()->end();
			echo $this->_view->partial('partials/global/alertBack.phtml');
			
			$this->_view->placeholder('absolute')->captureEnd();
		}

		
		$this->_view->placeholder('narrowColumn')->captureStart();
		
		$href = '/users/' . $user->userID;
        if ($user->getProfilePic('large') == '/images/users/profile/pic/large/default.jpg') {
			// No profile pic, send to upload profile pic page
			$href .= '/upload';
			$tooltip = 'Upload a picture';
			$overlay = "Picture needed";
		} else {
			$tooltip = 'View my profile';
			$overlay = '';
		}
			
        	echo "<a href='" . $href . "' class='left member-pic-container' tooltip='" . $tooltip . "'>
					<img src='" . $user->getProfilePic('large') . "' class='narrow-column-picture dropshadow dark-back' id='narrow-column-user-picture'/>
					<div class='member-narrow-column-pic-overlay black-back'></div>
					<div class='member-narrow-column-pic-overlay' id='member-narrow-column-pic-overlay-text'>
						<p class='clear width-100 center white heavy largest-text margin-top'>" . $user->shortName . "</p>
						<p class='clear width-100 center white heavy larger-text margin-top' id='member-narrow-column-pic-overlay-text-bottom'>" . $overlay . "</p>
					</div>
				  </a>";
           	echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Ratings'));
				echo $this->buildUserRatings();
			echo $this->_view->narrowcolumnsection()->end();
			
			$sections = array('my teams');
			echo $this->_view->narrowteamsection($user, $sections);
			
			
			/*$this->_view->narrowcolumnsection()->start(array('title' => 'My Teams'));
				if ($this->_view->user->hasValue('teams')) {
					echo 'teams!';
				} else {
					echo '<p class="medium clear-left">You have no teams.</p><a href="find/teams" class="medium smaller-text clear-right">Find a team</a>';
				}
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Groups'));
				if ($this->_view->user->hasValue('groups')) {
					echo 'groups!';
				} else {
					echo '<p class="medium clear-left">You have no groups.</p><a href="find/groups" class="medium smaller-text clear-right">Find a group</a>';
				}
			echo $this->_view->narrowcolumnsection()->end();
			*/
			
				echo "<div class='narrow-column-calendar-container left width-100'>";
				echo $this->_view->calendar()->createCalendar($this->_view->user->games->getAll(), true);
				echo "</div>";
			

		$this->_view->placeholder('narrowColumn')->captureEnd();
			

		$scheduleHeader = $this->buildScheduleHeader();
		$output 		= $this->_view->partial('partials/global/sectionHeaderBold.phtml',array('title'   => 'schedule',
																								'content' => $scheduleHeader)); 
		$output 	   .= $this->buildScheduleBody(); // Schedule content here
		
		$findHeader  = $this->buildFindHeader();																		
		$output     .= $this->_view->partial('partials/global/sectionHeaderBold.phtml',array('title'   => 'find',
																							 'content' => $findHeader)); 
		$output 	.= "<div id='gmap'></div>"; // Find content here
		$output     .= "<img src='/images/global/loading.gif' class='member-find-loading'/>
						<div id='member-find-body'>"
					 . $this->buildFindBody()
					 . "</div>";
		
		$newsfeedHeader = "<div class='right member-newsfeed-header medium'>" . $this->_view->user->city->city . "</div>";
		$output     .= $this->_view->partial('partials/global/sectionHeaderPlain.phtml',array('title'   => 'newsfeed',
																							  'content' => $newsfeedHeader)); 
		$newsfeed    = $this->buildNewsfeed();
		$output     .= $newsfeed;																			 
																		 																		 
		return $output;
	}
	
	/**
	 * build schedule header section
	 * @return $output
	 */
	public function buildScheduleHeader()
	{
		$output = '<div id="member-schedule-days-container">';
		$schedule = $this->_view->userSchedule;
		$firstDayEvent = false;
		
		for ($i = 0; $i < 7; $i++) {
			// Create the days in reverse order (float:right)
			$curDay  = strtotime('+' . $i . ' days');
			$day  	 = date('l', $curDay);
			$date 	 = date('d', $curDay);
			$dayOfWeek = date('w', $curDay);
			
			if ($day == 'Sunday' ||
				$day == 'Saturday' ||
				$day == 'Thursday') {
					// Show 2 letters
					$dayShort = substr($day,0,2);
			} else {
					// Show one letter
					$dayShort = substr($day,0,1);
			}
			
			$class = '';
			if ($day == date('l') && isset($schedule[$dayOfWeek])) {
				// Today and event scheduled
				$displayDay = $day;
				$class		= ' light-back';
				$firstDayEvent = true;
			} elseif ($day == date('l', strtotime('+1 day')) && !$firstDayEvent) {
				// Tomorrow, show abbreviated names
				$displayDay = $day;
				$class		= ' light-back';
			} else {
				$displayDay = $dayShort;
			}
			
			$subClass = ''; // Class for inner paragraphs
			if (isset($schedule[$dayOfWeek])) {
				// Day has event
				$subClass .= 'bold darkest';
			}
			
			$output .= "<div class='member-schedule-day-container pointer " . $class . "'>
							<p class='member-schedule-day-subtext light smaller-text " . $subClass . "'>" . $date . "</p>
							<p class='member-schedule-day light center " . $subClass . "' fullDay='" . $day . "' shortDay='" . $dayShort . "'>" . $displayDay . "</p>
						</div>";
		}
		
		$output .= "</div>";
		
		return $output;
	}
	
	/**
	 * build schedule body section
	 * @return $output
	 */
	public function buildScheduleBody()
	{
		$output   = "<div id='member-schedule-body-container'>";
		$curDay   = date('w');
		$schedule = $this->_view->userSchedule; 
		$firstDayEvent = false;
		$dayNum = 1;
		
		for ($i = $curDay; $i < ($curDay + 7);$i++) {
			if ($i > 6) {
				// 7 days in week, maintain order of days as well as count
				$b = $i - 7;
			} else {
				$b = $i;
			}
			
			$display = '';
			$date  	 = date('l', strtotime('+' . ($i - $curDay) . ' days'));
			
			if ($i == $curDay) {
				// Today
				$dateCombo = 'today';
			} elseif ($i - $curDay == 1) {
				// Tomorrow
				$dateCombo = 'tomorrow';
			} else {
				$dateCombo = 'this ' . $date;
			}
			
			if (isset($schedule[$b])) {
				// Event for today
				if ($i == $curDay) {
					// Today
					$display = " style='display:block'";
					$firstDayEvent = true;
				} elseif ($i == date('w', strtotime('+1 day')) && !$firstDayEvent) {
					// Tomorrow				
					$display = " style='display:block'";
				} elseif ((date('w', strtotime('+1 day')) == 0) && !$firstDayEvent && $dayNum == 2) {
					// Special case for Saturday - Sunday switch
					$display = " style='display:block'";
				}
				$games   = $schedule[$b];
				$output .= "<div class='member-schedule-day-body-container' " . $display . ">";
				$output .= "<div class='member-schedule-day-body-pagination-container'>";
				if (count($games) > 1) {
					// More than one game
					for ($c = 1; $c <= count($games); $c++) {
						$class = 'pagination-page pointer medium member-schedule-pagination';
						if ($c == 1) {
							$class .= ' light-back';
						}
						$output .= "<p class='" . $class . "'>" . $c . "</p>";
					}
				}
				$output .= "</div>";
				$output .= "<div class='member-schedule-day-body-outer-container'>
							<div class='member-schedule-day-body-inner-container'>";
				foreach ($games as $game) {
					$inClass  = '';
					$outClass = '';
					$existingID = '';
					$type   = '';
					$typeID = '';
					$teamID = " teamID=''";

					if ($game->confirmed == '1') {
						// User is confirmed, change class of in button
						$inClass = 'inner-shadow member-schedule-button-selected';
						$existingID = " existingID='update'";
					}
					
					if ($game->confirmed == '0') {
						// User is not confirmed
						$outClass = 'inner-shadow member-schedule-button-selected';
						$existingID = " existingID='update'";
					}
					
					if ($game->isPickup()) {
						$confirmClass = '';
						if ($game->isGameOn()) {
							// Enough players for game
							$confirm = 'GAME ON';
							$confirmClass = ' heavy';
						} else {
							$confirm = $game->getPlayersNeeded() . " needed";
						}
						
						if ($game->canceled) {
							$canceled = '<img class="left larger-indent" src="/images/global/canceled.png" tooltip="This game has been canceled. Reason: ' . $game->getCancelReason(true) . '"/>';
						} else {
							$canceled = '';
						}
						
						$output .= "<a href='/games/" . $game->gameID . "' class='member-schedule-day-body-game-container schedule-container'>";
						$output .= "<div class='member-schedule-day-body-game-left-container'>";
						$output .= "<p class='left bold darkest largest-text'>" . $game->getGameTitle() . '</p>' . $canceled;
						$output .= "<p class='clear'>" . $game->getDay() . "</p>";
						$output .= "<p class='clear'>" . $game->getHour() . "</p>";
						$output .= "<p class='clear medium'>" . $game->getPark()->parkName . "</p>";
						$output .= "</div>";
						$output .= "<div class='member-schedule-day-body-players-container darkest bold'>";
						$output .= "<p class='member-schedule-day-body-players largest-text center'>" . $game->totalPlayers . "/" . $game->rosterLimit . "</p>";
						$output .= "<p class='member-schedule-day-body-players-text center clear larger-text'>players</p>";
						$output .= "<p class='center clear green smaller-text member-schedule-day-body-players-confirmed margin-top " . $confirmClass . "'>" . $confirm . "</p>";
						$output .= "</div>";
						$type	 = " type='pickupGame'";
						$typeID  = " typeID='" . $game->gameID . "'";
					} elseif ($game->isTeamGame()) {
						$team    = $this->_view->user->teams->teamExists($game->teamID); // Not being used?
						$teamID  = " teamID='" . $game->teamID . "'";
						$output .= "<a href='/teams/" . $game->teamID . "' class='member-schedule-day-body-game-container schedule-container'>";
						$output .= "<div class='member-schedule-day-body-game-left-container'>";
						$output .= "<p class='darkest larger-text'>vs. <span class='heavy'>" . $game->getLimitedName('opponent', 25) . "</span></p>";
						$output .= "<p class='clear medium'>" . $game->teamName . "</p>";
						$output .= "<p class='clear darkest'>" . $game->getDay() . "</p>";
						$output .= "<p class='clear darkest'>" . $game->getHour() . "</p>";
						$output .= "<p class='clear medium'>" . $game->locationName . "</p>";
						$output .= "</div>";
						$output .= "<div class='member-schedule-day-body-team-players-container darkest bold'>";

						$output .= "<p class='center clear darkest member-schedule-day-body-players-confirmed' tooltip='" . $game->countConfirmedPlayers() . " players are going to this game'>
										<span class='confirmed larger-text heavy member-teamGame-confirmed left width-100 center'>" . $game->countConfirmedPlayers() . "</span> 
										<span class='clear darkest width-100 heavy center'>confirmed</span>
									</p>";
						$output .= "</div>";
						$type	 = " type='teamGame'";
						$typeID	 = " typeID='" . $game->teamGameID . "'";
						
						$output .= "<div class='member-schedule-day-body-game-right-container' " . $type . $typeID . $existingID . $teamID . ">";
						$output .= "<p class='darkest center'>Are you in, or are you out?</p>";
						$output .= "<p class='button larger-text member-schedule-in schedule-in " . $inClass . "'>in</p>";
						$output .= "<p class='button larger-text member-schedule-out schedule-out " . $outClass . "'>out</p>";
						$output .= "</div>";
					}

					
					$output .= "</a>";
				}
				$output .= "</div></div></div>";
				
			} else {
				if ($i == date('w', strtotime('+1 day')) && !$firstDayEvent) {
					// Show tomorrow by default
					$display = " style='display:block'";
				}
				$output .= "<p class='member-schedule-day-none member-schedule-day-body-container light larger-text center' " . $display . ">You have no games scheduled for " . $dateCombo . ".</p>";
			}
			$dayNum++;
			
		}
		$output .= '</div>';
		
		
		return $output;
	}
		
		
	
	/**
	 * build find header section
	 * @return $output
	 */
	public function buildFindHeader()
	{
		$output  = "<div class='member-find-looking-container'><p class='arial bold medium' id='member-find-looking'>Looking for: </p>";
		
		$output  .= $this->_view->lookingDropdownSport;
		$output  .= $this->_view->lookingDropdownType;
		$output  .= $this->_view->lookingDropdownTime;
		
		
		$output .= "</div>";
		return $output;
	}
	
	/**
	 * build find body section
	 * @return $output
	 */
	public function buildFindBody()
	{
		$output = "<div class='member-find-lower-outer-container'><div class='member-find-lower-outer-inner-container'>";
		$matches  = $this->_view->matches;
		
		$counter    = 0;
		$totalMatches = 1;
		$totalPages = 1;
		$totalGames = 0;
		$matchesPerPage = 4;
		$numberOfPages  = 3;
		if (empty($matches)) {
			// No matches 
			$output  = "<p class='medium larger-text member-find-none center'>No matches found</p>";
			$output .= "<a href='/find' class='light center member-find-none-search'><img src='/images/global/body/magnifying_medium.png' style='margin:0 2px -4px 0;'/>Broader search</a>";
			return $output;
		}
			
		foreach ($matches as $match) {
			if ($totalMatches > ($matchesPerPage * $numberOfPages)) {
				// Met limit of number of pages
				break;
			}
			if ($counter == 0) {
				// Counter was reset/first round, create inner container
				$output .= "<div class='member-find-lower-inner-container'>";
			} 
			if ($counter == $matchesPerPage) {
				// Number of games/teams per "page" is met, start new
				$output .= "</div><div class='member-find-lower-inner-container'>";
				$counter = 0;
				$totalPages++;
			}
			
			if ($match instanceof Application_Model_Game) {
				// Match is a game
				$type     = 'Game';
				$typeClass= 'bold';
				$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $match->date);
				$newDate  = $dateTime->format('m n');
				$day      = $match->getDay('D');
				$hour	  = $match->getHour();
				$dateDesc = $dateTime->format('M j');
				$id		  = $match->gameID;
				$location = $match->getLimitedParkName(22);
				$gameIndex= $totalGames;
				$dateHTML = "<div class='member-find-game-date-day'>" . $day . "</div>&nbsp; 
								<div class='member-find-game-date-hour'>" . $hour . "</div>";
				$totalGames++;
			} elseif ($match instanceof Application_Model_Team) {
				// Match is a team
				$type	  = 'Team';
				$dateHTML = $match->getLimitedName('city', 10);
				$location = $match->getLimitedName('teamName',25);
				$id		  = $match->teamID;
				$dateDesc = $match->city;
				$marker   = '';
				$gameIndex= '';
				$typeClass= '';
			}
				
			$output .= "<a class='member-find-game-container member-" . strtolower($type) . "' href='/" . strtolower($type) . "s/" . $id . "' gameIndex='" . $gameIndex . "'>";
			$output .= "<div class='left member-find-game-number-container'><p class='member-find-game-number green-back white arial bold'>" . $totalMatches . "</p></div>";
			$output .= "<p class='member-find-game-sport darkest bold'>" . $match->sport . "</p>";
			$output .= "<p class='member-find-game-type darkest " . $typeClass . "'>" . $type . "</p>";
			$output .= "<div class='member-find-game-date medium' tooltip='" . $dateDesc . "'>" . $dateHTML . "</div>";
			$output .= "<p class='member-find-game-players darkest bold'>" . $match->totalPlayers . "/" . $match->rosterLimit . "</p>";
			$output .= "<img src='" . $match->getMatchImage() . "' class='member-find-game-match' tooltip='" . $match->getMatchDescription() . "'/>";
			$output .= "<p class='member-find-game-park medium'>" . $location . "</p>";
			$output .= "<img src='/images/global/body/single_arrow.png' class='member-find-game-arrow'/>";
			
			$output .= "</a>";
						
			$counter++;
			$totalMatches++;
			
			
		}
		
		// End game section
		$output .= "</div></div></div>";
		
		// Num pages
		$output .= "<div class='pagination-pages-outer-container'><div class='pagination-pages-inner-container'>";
		for ($i = 1; $i <= $totalPages; $i++) {
			$class = 'pagination-page pointer medium member-find-pagination';
			if ($i == 1) {
				$class .= ' light-back';
			}
			$output .= "<p class='" . $class . "'>" . $i . "</p>";
		}
		
		$output .= "</div></div>";
		
		$output .= "<a href='/find' class='member-find-view-more medium'>view more</a>";
		
		return $output;
	}
	
	
	/**
	 * build main newsfeed section
	 * @return $output
	 */
	public function buildNewsfeed()
	{
		$newsfeed = $this->_view->newsfeed;
		$output   = '<div class="notifications-container">';
		if (!$newsfeed->hasValue('read')) {
			// No newsfeed available
			$output .= "<p class='medium larger-text center width-100 newsfeed-no-activity left'>No recent activity</p>";
			$output .= "</div>";
		} else {
			foreach ($newsfeed->read as $notification) {
				$output .= $this->createNotification($notification);
			}
			$output .= "</div>";
			$output .= "<p class='button' id='notifications-load'>Load more</p>";
			$output .= "<p class='medium clear width-100 center' id='notifications-none'>No more activities found.</p>";		
		}
		
		return $output;
	}
	
	/**
	 * create html for notification
	 * @params(notification => notification ele)
	 */
	 public function createNotification($notification, $size = 'tiny')
	 {
		  $output = '';
		  $preWrapper  = "<a href='" . $notification->getFormattedURL() . "' class='left  box-img-container-" . strtolower($size) . "'>";
		  $postWrapper = "</a>";
		  $class	   = 'box-img-' . strtolower($size);
		  $currentID = false;
		  if ($notification->_attribs['pictureType'] == 'sports') {
			  // Sport icon to be shown, wrap in container
			  $preWrapper = "<a href='" . $notification->getFormattedURL() . "' class='notification-sports-img-container-" . $size . "'>";
			  $class = '';
			  //$postWrapper = "</a>";
		  } 
		  if ($this->_view->game) {
			  //On game page, change notifications to "this game..."
			  $currentID     = $notification->gameID;
		  }
		  
		  
		  $output .= "<div class='newsfeed-notification-container'>";
		  $output .= 	$preWrapper . "<img src='" . $notification->getPicture($size) . "' class='newsfeed-notification-img " . $class . "' />" . $postWrapper;
		  $output .= 	"<div class='newsfeed-notification-text-container'>";
		  $output .= 		"<p class='left newsfeed-notification-text'>" . $notification->getFormattedText($currentID) . "</p>
		  					 <span class='newsfeed-notification-time light smaller-text'>" . $notification->getTimeFromNow() . "</span>";
		  $output .= 	"</div>
					  </div>";
					  
		  return $output;
	 }
	 
	 /**
	 * build right narrow column ratings dropdown
	 * @return $output
	 */
	public function buildUserRatings()
	{
		$output = '';
		$iconsOutput    = '';
		$ratingsOutput = '<div id="member-narrow-rating-lower-container">';
		$sports = $this->_view->user->sports;
		
		$ratingOrder = array('skillCurrent' => 'skill',
							 'sportsmanship' => 'sprtmn',
							 'attendance'	 => 'attnd');
		$counter = 0;
		foreach ($sports as $sport) {
			$class = '';
			if ($counter == 0) {
				// First sport is selected initially
				$class = 'green-back';
			}
			$iconsOutput   .= "<img src='" . $sport->getIcon('small', 'outline') . "' class='medium-background member-narrow-rating-icon pointer " . $class . "' />";
			$ratingsOutput .= "<div class='member-narrow-rating-container'>";
			$ratingsOutput .= "<p class='width-100 clear center darkest'>" . $sport->sport . "</p>";
			$ratingsOutput .= "<a href='/users/" . $this->_view->user->userID . "/ratings/" . strtolower($sport->sport) . "' class='width-100 clear center green bold jumbo-text'>" . $sport->getOverall() . "</a>";
			$ratingsOutput .= "<div class='width-100 clear'>";
			
			foreach ($ratingOrder as $rating => $label) {
				// Create individual rating breakdown
				$ratingsOutput .= "<div class='rating-individual-container'>";
				$ratingsOutput .= "<p class='green smaller-text width-100 center clear rating-label'>" . $label . "</p>";
				$ratingsOutput .= "<p class='green bold larger-text width-100 center clear'>" . $sport->$rating . "</p>";
				$ratingsOutput .= "</div>";
			}
			
			/*$ratingsOutput .= "<div class='rating-individual-container'>";
			$ratingsOutput .= "<p class='green smaller-text width-100 center clear'>skill</p>";
			$ratingsOutput .= "<p class='green bold larger-text width-100 center clear'>" . $sport->skillCurrent . "</p>";
			$ratingsOutput .= "</div>";*/
			$ratingsOutput .= "</div></div>";
			
			$counter++;
		}
		
		$ratingsOutput .= "</div>";
		
		$ratingsOutput .= "<p class='right smaller-text medium larger-margin-top action why-ratings pointer'>What are these ratings?</p>";
		
		$output .= $iconsOutput . $ratingsOutput;
		
		return $output;
	}
			
}