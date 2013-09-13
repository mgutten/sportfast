<?php

class Application_View_Helper_ProfileNewsfeed
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function profilenewsfeed()
	{
		return $this;
	}
	
	/**
	 * @params ($messages => messages model)
	 */
	public function create($messages) {
		
		if (!$messages->hasValue('read')) {
			// No messages, do not display anything
			return false;
		}
		$output = "<div class='profile-newsfeed clear width-100'>";
		$memberHomepage = $this->_view->getHelper('memberhomepage');
		
		foreach ($messages->read as $message) {
			//$output .= "<div class='newsfeed-container left width-100'>";

			if ($message->hasValue('notification')) {
				// Is notification
				$output .= $memberHomepage->createNotification($message->notification, 'small');
			} else {
				// Is message
				$idType = ($message->hasValue('teamMessageID') ? 'teamMessageID' : 'gameMessageID');
				$output .= "<div class='newsfeed-notification-container clear'>
								<a href='/users/" . $message->userID . "' class='left'>" . $message->getBoxProfilePic('small') . "</a>";
				$output .= "<div class='profile-message-container left' messageID='" . $message->$idType . "'>";
				$output .= 		"<p class='light left'>" . $message->getUserName() . " said...</p>";
				$output .=		"<p class='newsfeed-notification-time light smaller-text'>" . $message->getTimeFromNow() . "</p>";
				$output .=		"<p class='light-back darkest rounded-corners profile-message clear'>" . $message->message . "</p>";
				if ($message->userID == $this->_view->user->userID) {
					// Allow user to delete own comments
					$output .= 		"<p class='light right larger-text profile-delete-message pointer hidden'  tooltip='Delete'>x</p>";
				}
				
				$output .= "</div>";
				$output .= "</div>";
			}
			
			//$output .= "</div>";
			
		}
		
		
		$output .= "</div>";
		
		return $output;
	}
}
