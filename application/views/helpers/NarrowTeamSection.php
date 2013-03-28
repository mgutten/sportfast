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
					$output .=  "<p class='light clear smaller-text'>No " . $section . " have been added</p>";
				} else {
					// Friends, teams, or groups exist
					$counter = 0;
					if ($section == 'players') {
						$url  = '/users';
						$name = 'shortName';
						$id   = 'userID';
						$class = 'box-img-container-tiny narrow-column-box-img animate-opacity';
						$imgClass = 'box-img-tiny';
						$text = '';
					} else {
						$url  = '/' . $section;
						$single = rtrim($section,'s');
						$name = $single . 'Name';
						$id   = $single . 'ID';
						$class = 'narrow-column-selectable';
						$imgClass = '';
						
					}
					$tooltip = '';
						foreach ($results as $result) {
							if ($counter >= 5) {
								$diff = count($results) - $counter;
								$output .=  "<a href='users/" . $userClass->userID . "/" . $section . "' class='medium smaller-text clear-right'>" . $diff . " other " . $section . "</a>";
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
							$output .=  "</a>";
							$counter++;
						}
				}
			$output .=  $this->_view->narrowcolumnsection()->end();
		
		}
		
		return $output;
	}
}
