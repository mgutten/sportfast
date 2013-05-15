<?php

class Application_View_Helper_Calendar
{
	public $month;
	public $today;
	public $firstDay;
	public $lastDay;
	public $lastMonthDays;
	public $scheduledDays;
	public $date;
	public $curDate;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function calendar()
	{
		return $this;
	}

	
   public function setImportantDates($selectedMonth = false, $selectedYear = false) {
		  
		  if (!$selectedMonth) {
			  // No month set, set to current month
			  $this->month = date('m');
		  } else {
			  $this->month = ($selectedMonth < 9 ? '0' . $selectedMonth : $selectedMonth);
		  }

		  //if it is current month, set 'today'
		  //so we can use it later
		  if(!$selectedMonth) {
			  $month = $selectedMonth;
			  $this->today = date('j');
		  }
		  //else if we are in next month, then
		  //set today at 0 so other functions 
		  //recognize that we are in the future
		  elseif($selectedMonth == date('n')+1){
			  $month = $selectedMonth;
			  $this->today = 0;
		  }
		  //else we are in a past month and so
		  //set today at high number so fns know
		  //that the selected month is in past
		  else{
			  $this->today = 100;
			  $month = $selectedMonth;
		  }
		  
		  //if we have changed year, then get calendar
		  //for corresponding year
		  if (!$selectedYear) {
			  // This year
			  $year = date('Y');
			  $this->year = date('y');
		  } else {
			  // Other year
			  $year = date('Y', strtotime($selectedYear . ' years'));
			  $this->year = date('y', strtotime($selectedYear . ' years'));
		  }
			  
		  //$this->date  = $date = DateTime::createFromFormat('jnY H:i:s', $this->today . $this->month . $year . ' 00:00:00');
		  $this->date     = $date = DateTime::createFromFormat('d-m-Y H:i:s', '01-' . $this->month . '-' . $year . ' 00:00:00');		  
		  $this->firstDay = $date->format('w');
		  $this->lastDay  = $date->format('t');
		  
		  //find the prior months number of days
		  $lastMonth = new DateTime();
		  $this->lastMonthDays = $lastMonth->modify('-1 month')->format('t');		
			
	}
	
	public function setScheduledDays($eventsArray, $tooltips = true) {
			
			if (empty($eventsArray)) {
				return false;
			}
			foreach ($eventsArray as $event){
				if (isset($event->_attribs['date'])) {
					// Date var
					$date = $event->_attribs['date'];
				} elseif (isset($event->_attribs['dateHappened'])) {
					// DateHappened var
					$date = $event->_attribs['dateHappened'];
				} else {
					// No date given
					continue;
				}
				
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
				$eventDate = $date->format('mjy');
				$curDate = new DateTime('now');
				
				if ($tooltips) {
					if (!empty($event->_attribs['teamGameID'])) {
						// Is team game
						$tooltip  = '<p class="left darkest">vs. ' . $event->opponent . '</p>';
						
						$extraTooltip = '';
						if ($date->format('U') < $curDate->format('U')) {
							// Past event
							$extraTooltip = '<p class="left green">&nbsp;' . $event->fullWinOrLoss . '</p>';
						}
						$tooltip .= $extraTooltip;
						$tooltip .= '<p class="clear dark">' . $event->getDay() . ' at ' . $event->getHour() . '</p>';
						$tooltip .= '<p class="clear dark">' . $event->locationName . '</p>';
						$url = '/teams/' . $event->teamID;
						$winOrLoss = $event->winOrLoss;
					} elseif (!empty($event->_attribs['gameID'])) {
						// Is game
						$tooltip  = '<p class="left darkest heavy">' . $event->sport . ' Game</p>';
						$tooltip .= '<p class="clear dark">' . $event->getDay() . '</p>';
						$tooltip .= '<p class="clear dark">' . $event->getHour() . '</p>';
						$tooltip .= '<p class="clear dark">' . $event->park->parkName . '</p>';
						$url = '/games/' . $event->gameID;
						$winOrLoss = false;
					}
					$this->scheduledDays[$eventDate] = array($tooltip, $url, $winOrLoss);
				} else {
					// No tooltips wanted, store necessary info into element itself (if need for game, not teamgame, then add code)
					$winOrLoss = " winOrLoss='" . $event->winOrLoss . "'";
					$opponent  = " opponent='" . $event->opponent . "'";
					$time	   = " time='" . $date->format('g:ia') . "'";
					$location  = " location='" . $event->locationName . "'";
					$id		   = " typeID='" . $event->teamGameID . "'";
					$type	   = " type='teamGame'";
					$address   = " address='" . $event->streetAddress . "'";
					$leagueLocationID = " leagueLocationID='" . $event->leagueLocationID . "'";
					
					$this->scheduledDays[$eventDate] = array($opponent, $time, $winOrLoss, $location, $id, $type, $address, $leagueLocationID);
				}
				
			}
	}

	/**
	 * create calendar of variable width based on events array passed in
	 * @params ($eventsArray => array of event objects(games or team games),
	 *			$days		 => should show day of week at top? (su, m, t, etc),
	 *			$tooltips	 => should show tooltips on mouseover of event day?,
	 *			$selectedMonth => what month (1 for january, 12 for december) is selected,
	 *			$selectedYear  => move forward or back years (+1 or -1 etc), 
	 *			$numberedDays  => add number of day to top corner of calendar day,
	 *			$changeMonth   => should user be allowed to change month?)
	 */
    public function createCalendar($eventsArray, $days = false, $tooltips = true, $selectedMonth = false, $selectedYear = false, $numberedDays = false, $changeMonth = false) {
		
		$this->setScheduledDays($eventsArray, $tooltips);
		$this->setImportantDates($selectedMonth, $selectedYear);
		
		$output = '';
				
		$leftArrow = '';
		$rightArrow = '';
		if ($changeMonth || $selectedMonth) {
			// Allow user to change month
			$leftArrow .= "<img src='/images/global/arrows/left/medium.png' class='left margin-top pointer calendar-left-arrow' id='calendar-left-arrow'/>";
			$rightArrow .= "<img src='/images/global/arrows/right/medium.png' class='right margin-top pointer calendar-right-arrow' id='calendar-right-arrow'/>";
		}
		$output .= "<div class='calendar-container left width-100' id='calendar-container-" . $this->date->format('n') . "'>";
		$output .= "<div class='calendar-month-container left'>";
		$output .= 		"<div class='calendar-arrow-container left'>" . $leftArrow . "</div>";
		$output .= 		"<p class='calendar-month-name center dark left' monthID='" . $this->date->format('m') . "' yearID='" . $this->date->format('Y') . "'>" . $this->date->format('F') . "</p>";
		$output .= 		"<div class='calendar-arrow-container right'>" . $rightArrow . "</div>";
		$output .= "</div>";
		
		if ($days) {
			$days     = array('Su','M','T','W','Th','F','Sa');
			$daysLong = array('Su' => 'Sunday',
							  'M'  => 'Monday',
							  'T'  => 'Tuesday',
							  'W'  => 'Wednesday',
							  'Th' => 'Thursday',
							  'F'  => 'Friday',
							  'Sa' => 'Saturday');
			$output .= "<div class='calendar-days-container clear width-100'>";

			foreach ($days as $day) {
				$class = 'light';
				if ($daysLong[$day] == date('l')) {
					// Today
					$class = 'darkest';
				}
				$output .= 		"<p class='" . $class . " left calendar-day-name width-100 center'>" . $day . "</p>";
			}
			$output .= "</div>";
		}
		
		//create calendar
		$c = 1;
		//subtract 1 to deal with offset created by
		//date function's array (0-6 = days of week)
		$lastMonthDay = $this->firstDay - 1;
		$nextMonthDay = 1;
		$month = $this->month;
		$future = false;

		//create 5 weeks of month
		for($i = 0; $i <= 5; $i++) {
			//create 7 days of week

			for($b=0; $b<7; $b++) {
				
				$blank = false;
				$class = 'calendar-day';
				$inner = '';
				$tooltip = '';
				
				//if it's the first week and before first
				//day or after last day of month, create blanks
				if(($i == 0 && $b < $this->firstDay) || $c > $this->lastDay){
					//if its the month before then use last months days
					if($i == 0){
						$day = $this->lastMonthDays - $lastMonthDay;
						$lastMonthDay--;
						$class .= ' calendar-last-month';
					}
					//else its next month and should start at 1
					else {
						$day = $nextMonthDay;
						$nextMonthDay++;
						$class .= ' calendar-next-month';
						if ($nextMonthDay == 2) {
							// Only increase month once
							$month = $month + 1;
							$month = ($month <= 9 ? '0' . $month : $month);
						}

					}
					
					$blank = true;
					$class .= ' calendar-transparent';
						
				} else {
					// Within current month
					if (!$future && ($c != $this->today)) {
						// Past day that is not today
						$class .= ' calendar-past';
					} else {
						$class .= ' calendar-selectable';
					}
					$day = $c;
				}
				
				//if first day of week, start it on a new line		
				if ($b==0) {
					$class .= ' clear';
				}

				$daysNumberedClass =  'dark left smaller-text indent';

				if ($c == $this->today && $day < $this->lastMonthDays) {
					$future = true;
					$class .= ' calendar-today';
					$daysNumberedClass .= ' heavy darkest';
					$tooltip = 'tooltip="Today"';
					//$inner  = '<p class="width-100 left center light smaller-text arial">' . date('D') . '</p>';
				}
				
				if ($numberedDays) {
					// Add number to days
					$inner = "<p class='" . $daysNumberedClass . "'>" . $day . "</p>";
				}
				
				//if we are at the end of the month, stop giving
				//ids to the blank days
				if($blank)
					$id = '';
				else
					$id = $c;
					
					
				//if running day has a reserved activity, show name
				//format for array from reserved_activities fn is monthday(eg. 0619)
				if(!empty($this->scheduledDays[$month . $day . $this->year])){
					$eventArray = $this->scheduledDays[$month . $day . $this->year];

					$class  .= ' calendar-dark animate-darker';
					if (strpos($eventArray[1], '/games/') !== false) {
						// Show special class for pickup games
						$class .= ' calendar-green';
					}
					
					$innerTooltip = $eventArray[0];
					if ($tooltips) {
						// Set tooltip
						$tooltip = "tooltip='" . $innerTooltip . "'";
					} else {
						// Display custom attributes as stored from setScheduledDays
						$tooltip = implode(' ', $eventArray);
						$eventArray[1] = ''; // prevent url from being used below
						if (preg_match("/ winOrLoss='([a-zA-Z || ?]+)'/", $eventArray[2], $matches)) {;
							$eventArray[2] = $matches[1];
						}
					}
					
					
					if ($c < $this->today) {
						// Event happened before today, make more transparent
						$class .= ' calendar-more-transparent';
						$inner  = '<p class="left width-100 center margin-top white calendar-old-event">' . $eventArray[2] . '</p>';					
					} elseif ($c > $this->lastDay) {
						// Next month scheduled game
						$class .= ' calendar-no-select';
					}
					
					$output .= "<a href='" . $eventArray[1] . "' class='".$class."' id='calendar-".$id."' " . $tooltip . ">";					
					$output .= $inner;
					$output .= "</a>";
				} else {

					$output .= "<div class='".$class."' id='calendar-".$id."' " . $tooltip . ">";					
					$output .= $inner;
					$output .= "</div>";
				}
				
				
				//if we are dealing with a blank day, do not 
				//increment our running day var $c
				if($blank === true)
					continue;

				$c++;
			}
			
		}
		
		$output .= "</div>";
		
		if ($changeMonth) {
			$curMonth = $this->month;
			$lastMonth = $curMonth - 1;
			$year = false;
			if ($lastMonth < 1) {
				$year = -1;
				$lastMonth = 12;
			}
			$output .= $this->createCalendar($eventsArray, $days, $tooltips, $lastMonth, $year, $numberedDays, false);
			
			$nextMonth = $curMonth + 1;
			$year = false;
			if ($nextMonth > 12) {
				$year = 1;
				$nextMonth = 1;
			}
			$output .= $this->createCalendar($eventsArray, $days, $tooltips, $nextMonth, $year, $numberedDays, false);
			
		}
		
		return $output;
	}
}
