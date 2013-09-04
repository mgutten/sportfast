<?php

class Application_View_Helper_NarrowTeamSection
{
	
	public $_view;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function narrowteamsection($userClass, $sections)
	{
		$output = '';
		
		foreach ($sections as $section) {
			// Build narrow column dropdowns
			$output .=  $this->_view->narrowcolumnsection()->start(array('title' => ucwords($section)));
			$section = str_replace('my ', '', $section); // Remove "my" from section
			$results = $userClass->$section->getAll();
				if (!($results)) {
					// No friends, teams, groups
					$output .= "<p class='light clear smaller-text'>No " . $section . " have been added</p>";
					$output .= "<a href='/find/" . $section . "' class='medium smaller-text clear-right'>Find " . $section . "</a>";
				} else {
					// Friends, teams, or groups exist
					$counter = 0;
					if ($section == 'players') {
						$url  = '/users';
						$name = 'shortName';
						$id   = 'userID';
						$class = 'box-img-container-tiny narrow-column-box-img dark-back';
						$imgClass = 'box-img-tiny animate-opacity';
						$text = '';
						$limit = 12;
					} else {
						// teams
						$url  = '/' . $section;
						$single = rtrim($section,'s');
						$name = $single . 'Name';
						$id   = $single . 'ID';
						$class = 'narrow-column-selectable';
						$imgClass = '';
						$limit = 5;
					}
					
					$tooltip = '';
						foreach ($results as $result) {
							if ($counter >= $limit) {
								$diff = count($results) - $counter;
								$output .=  "<a href='users/" . $userClass->userID . "/" . $section . "' class='medium smaller-text clear-right margin-top'>" . $diff . " other " . $section . "</a>";
								break;
							}
							
							if (strlen($result->$name) > 15) {
								// This name will be limited
								$tooltip = "tooltip = '" . $result->$name . "'";
							} 

							
							if ($section == 'players') {
								// Friends section, add tooltip
								$tooltip = "tooltip = '" . $result->$name . "'";
							} else {
								// Not friends, add name
								$text  = "<p class='left dark'>" . $result->getLimitedName($name, 16) . "</p>";
							} 
							
							$output .=  "<a href='" . $url . "/" . $result->$id . "' class='" . $class . " left' " . $tooltip . ">";
							$output .=  	"<img src='" . $result->getProfilePic('tiny') . "' class='left " . $imgClass . "'/>";
							$output .= 	$text;
							
							if ($section == 'teams') {
								
								if ($result->isCaptain($userClass->userID)) {
									//$output .= "<img src='/images/global/match/small/great.png' class='narrow-column-team-captain'/>";
									if ($userClass->userID == $this->_view->user->userID) {
										// your
										$pronoun = 'You';
									} else {
										$pronoun = 'They';
									}
									$output .= "<span class='narrow-column-team-captain green-back white' tooltip='" . $pronoun . " are captain'>C</span>";
								}
							}
							$output .=  "</a>";
							$counter++;
						}
						
						$output .=  "<a href='/users/" . $userClass->userID . "/" . $section . "' class='medium smaller-text clear-right margin-top'>view all</a>";
				}
			$output .=  $this->_view->narrowcolumnsection()->end();
		
		}
		
		return $output;
	}
}
