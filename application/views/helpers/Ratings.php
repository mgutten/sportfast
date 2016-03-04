<?php

class Application_View_Helper_Ratings
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function ratings()
	{
		return $this;
	}
	
	public function loopParkRatings($ratings)
	{
		$output = '';
		foreach ($ratings as $rating) {
			$user = $rating->user;
			$width = $rating->getStarWidth('quality') . '%';
			
			$output .= "<div class='rating-container width-100 clear'>";
			$output .= 		"<a href='/users/" . $user->userID . "' class='left'><img src='" . $user->getProfilePic('small') . "' class='left' /></a>";
			$output .=		"<div class='left rating-right'>";
			$output .=			"<p class='smaller-text left light'>" . $user->shortName . " played " . strtolower($rating->sport) . " here...</p>";
			$output .=			"<p class='smaller-text right light'>" . $rating->getTimeFromNow() . "</p>";
			$output .=			$this->_view->ratingstar('small', $width, $this->_view->currentURI);
			$output .=			"<p class='clear larger-margin-top medium'>" . $rating->getQuotedComment() . "</p>";
			$output .=		"</div>";
			$output .= "</div>";
		}

		
		return $output;
	}
	
	public function loopUserRatings($ratings)
	{
		$output = '';
		$availableRatings = new Application_Model_RatingsMapper();
		$availableSkillRatings = $availableRatings->getAvailableRatings('user', 'skill');
		$availableSportsmanshipRatings = $availableRatings->getAvailableRatings('user', 'sportsmanship');
		
		foreach ($ratings as $rating) {
			$width = $rating->getStarWidth('quality') . '%';
			
			$class = '';
			
			if ($rating->date->format('U') > $this->_view->lastRating && $this->_view->isUser) {
				// New rating
				$class = 'light-back';
			}
			
			if ($rating->attendance == '1') {
				$main  =	$this->_view->ratingbar($rating->skillRatingName, $availableSkillRatings, 'skill');
				$main .=	$this->_view->ratingbar($rating->sportsmanshipRatingName, $availableSportsmanshipRatings, 'sportsmanship');
				$main .=	"<p class='clear larger-margin-top medium'>" . $rating->getQuotedComment() . "</p>";
			} else {
				// No show
				$main  = "<p class='clear red larger-text heavy'>Did Not Show</p>";
				if ($this->_view->isUser) {
					$main .= "<p class='clear smaller-text red'>If you must miss a game, be sure to leave that game before game time.</p>";
				}
			}
			
			if ($rating->incorrect == '1') {
				// has been flagged
				$flagClass = 'clear-right';
				$flagText = 'awaiting response';
			} else {
				$flagClass = 'clear-right pointer flag-incorrect';
				$flagText = 'flag as incorrect';
			}

			$output .= "<div class='rating-container width-100 clear " . $class . "'>";
			$output .= 		"<img src='/images/users/profile/pic/small/default.jpg' class='left' />";
			$output .=		"<div class='left rating-right'>";
			$output .=			"<p class='smaller-text left light'>Anonymous rated...</p>";
			$output .=			"<p class='smaller-text right light'>" . $rating->getTimeFromNow() . "</p>";
			//$output .=			$this->_view->ratingstar('small', $width, $this->_view->currentURI);
			$output .=			"<p class='clear dark heavy larger-text' tooltip='Anonymous rated this as your best skill.'>" . ucwords($rating->skiller) . "</p>";
			$output .=			$main;
			
			$output .=			($this->_view->isUser  ? "<div class='" . $flagClass . "' userRatingID='" . $rating->userRatingID . "'><p class='light clear-right smaller-text action'>" . $flagText . "</p><img src='/images/global/flag.png' class='right'/></div>" : '');
			$output .=		"</div>";
			$output .= "</div>";
		}

		
		return $output;
	}
			
	

}
