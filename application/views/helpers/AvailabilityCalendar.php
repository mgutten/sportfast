<?php

class Application_View_Helper_AvailabilityCalendar
{
	
	public $_view;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function availabilitycalendar()
	{
		return $this;
	}
	
	/**
	 * create availability calendar
	 * @params ($sport => array of sport values,
	 *			$userSport => this user's Sport Model for $sport
	 */
	public function create($sport, $userSport = false)
	{
		$output    = "<div class='availabilty-calendar-container' id='availability-calendar-container-" . $sport . "'>";
		$startHour = 8;
		
		$output  .= $this->createLabels();
		
		
		$days     = array('Su','M','T','W','Th','F','Sa');
		$daysLong = array('Su' => 'Sunday',
						  'M'  => 'Monday',
						  'T'  => 'Tuesday',
						  'W'  => 'Wednesday',
						  'Th' => 'Thursday',
						  'F'  => 'Friday',
						  'Sa' => 'Saturday');
		$outerCounter = 1;
		foreach ($days as $day) {
			$counter = $startHour;
			$output .= "<div class='availability-calendar-day-container' day='" . $day . "' id='" . $sport . "-availability-calendar-day-container-" . $day . "' >";
			$output .= "<p class=' medium availability-calendar-day pointer'>" . $day . "</p>";
			
			for ($i = 0; $i < 4; $i++) {
				// Create 4 sections
				$class   = 'availability-calendar-section';
				if ($day == end($days)) {
					// Last column, do prevent extended border-top
					$class .= ' availability-calendar-section-last';
				}
				if ($i == 3) {
					$class .= ' availability-calendar-section-bottom';
				}
				
				$output .= "<div class='" . $class . "'>";
				
				for ($b = 0; $b < 4; $b++) {
					// Each with 4 divs
					$class = 'availability pointer';
					if ($b == 0) {
						// First div of each section, prevent white margin-top
						$class .= ' availability-first';
					}
					if ($outerCounter % 2 == 0) {
						// Even
						$class .= ' availability-light-background';
					}
					
					if ($userSport) {
						if (isset($userSport->availabilities[$outerCounter - 1][$counter])) {
							// User has selected this previously
							$class .= ' selected-green';
						}
					}
					if ($counter > 21) {
						continue;
					}
					
					$tooltipHourStart = $this->convertTime($counter);
					$tooltipHourEnd   = $this->convertTime($counter+1);
					$tooltip = '<p class="center bold medium">' . $daysLong[$day] . '</p>' . $tooltipHourStart . ' - ' . $tooltipHourEnd;
					
					$output .= "<div class='" . $class . "' tooltip = '" . $tooltip . "' hour='" . $counter . "' id='" . $sport . "-" . $day . "-" . $counter . "'></div>";
					$counter++;
				}
				$output .= "</div>";
			}
			$output .= "</div>";
			$outerCounter++;
		}
		
		
		//$output .= $this->createLabels('right');
		

		$output .= "</div>";
						
		return $output;
	}
	
	
	public function convertTime($militaryTime)
	{
		$time = $militaryTime;
		if ($time > 11) {
			// We are past 11am
			$timePeriod = 'pm';
			if ($time !== 12) {
				// If it is not 12, adjust to non-military time
				$time = $time - 12;
			}
		} else {
			$timePeriod = 'am';
		}
		
		return $time.$timePeriod;
	}

		
		
	
	public function createLabels($side = 'left')
	{ 
		$style = '';
		if ($side == 'right') {
			$style = ' style="float:left;" ';
		}
		$output  = "<div class='availability-calendar-label-container'>";
		
		$output .= "<p class='availability-calendar-label-hour medium' $style>8</p>";
		$output .= "<p class='availability-calendar-label-time light' $style>AM</p>";
		$output .= "<p class='availability-calendar-label-hour medium' $style>noon</p>";
		$output .= "<p class='availability-calendar-label-time light' $style>PM</p>";
		$output .= "<p class='availability-calendar-label-hour medium' $style>4</p>";
		$output .= "<p class='availability-calendar-label-time light' $style>PM</p>";
		$output .= "<p class='availability-calendar-label-hour medium' $style>8</p>";
		$output .= "<p class='availability-calendar-label-time light' $style>PM</p>";
		//$output .= "<p class='availability-calendar-label-hour medium' $style>12</p>";
		
		$output .= "</div>";
		
		return $output;
	}


}
