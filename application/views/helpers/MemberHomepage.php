<?php
class Application_View_Helper_MemberHomepage
{	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function memberHomepage() 
	{
		$user = $this->_view->user;
		$this->_view->placeholder('narrowColumn')->captureStart();
            
        	echo "<img src='" . $user->getProfilePic('large') . "' class='narrow-column-picture dropshadow' id='narrow-column-user-picture'/>";
           	echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Ratings'));
				echo "Rating information will go here";
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Teams'));
				echo "Team information will go here";
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Groups'));
				echo "Group information will go here";
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Schedule'));
				echo "Schedule information will go here";
			echo $this->_view->narrowcolumnsection()->end();

		$this->_view->placeholder('narrowColumn')->captureEnd();
			
			
		$scheduleHeader = $this->buildScheduleHeader();
		$output 		= $this->_view->partial('partials/global/sectionHeaderBold.phtml',array('title'   => 'schedule',
																								'content' => $scheduleHeader)); 
		$output 	   .= ''; // Schedule content here
		
		$findHeader  = $this->buildFindHeader();																		
		$output     .= $this->_view->partial('partials/global/sectionHeaderBold.phtml',array('title'   => 'find',
																							 'content' => $findHeader)); 
		$output 	.= ''; // Find content here																					 
																							 
		return $output;
	}
	
	public function buildScheduleHeader()
	{
		$output = '';
		
		for ($i = 6; $i >= 0; $i--) {
			// Create the days in reverse order (float:right)
			$day = date('l', strtotime('-' . $i . ' days'));
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
			if ($day == date('l')) {
				// Today!
				$displayDay = $day;
				$class		= ' light-back';
			} else {
				// Not today, show abbreviated names
				$displayDay = $dayShort;
			}
			
			$output .= "<div class='member-schedule-day-container pointer " . $class . "'>
							<p class='member-schedule-day-subtext medium smaller-text'>08</p>
							<p class='member-schedule-day medium center' fullDay='" . $day . "' shortDay='" . $dayShort . "'>" . $displayDay . "</p>
						</div>";
		}
		
		return $output;
	}
	
	public function buildFindHeader()
	{
		$output  = "<div class='member-find-looking-container'><p class='arial bold medium' id='member-find-looking'>Looking for: </p>";
		
		$output  .= $this->_view->lookingDropdownSport;
		$output  .= $this->_view->lookingDropdownType;
		
		
		$output .= "</div>";
		return $output;
	}
			
}