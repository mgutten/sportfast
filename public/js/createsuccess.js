// success page for create game or team

var selectedUsers = new Array();

$(function()
{
	
	$('#userName').keyup(function()
	{
		if ($(this).val().length < 3) {
			return;
		}
		
		var limit = new Array('users');
		searchDatabase($(this).val(), populateSearchResultsInvite, limit);
	})
	
	$('#create-success-emails').focus(function()
	{
		if ($('#create-send-invites').css('display') == 'none') {
			// Send button is hidden, show
			$('#create-send-invites').css({'opacity': 0,
										   'display': 'block'})
									 .animate({'opacity': 1}, 300);
		}
	})
	
	
	$('#create-send-invites').click(function()
	{
		buildUserIDs();
		$(this).parents('form').submit();
	})
		
	
	$(document).on('click','.create-userName-result',function()
	{
		addUserToList($(this).attr('userID'), $(this).attr('username'));
		
		if ($('#create-send-invites').css('display') == 'none') {
			// Send button is hidden, show
			$('#create-send-invites').css({'opacity': 0,
										   'display': 'block'})
									 .animate({'opacity': 1}, 300);
		}
	})
	
	$(document).on('click','.remove-user',function()
	{
		var userID = $(this).parent().attr('userID');
		var index = $.inArray(userID, selectedUsers);
		
		selectedUsers.splice(index, 1);
		
		$(this).parent().remove();
		
	})
	
})

/**
 * populate invite button's search results
 * @params (results => returned results from ajax)
 */
function populateSearchResultsInvite(results) 
{
	var output = '';
	
	if (results.length < 1) {
		// No results
		output += "<div class='header-search-result dark-back medium'>No results found</div>";
	} else {
		// Results found
		var limit = (results.length > 5 ? 5 : results.length);
		var src;
		for (i = 0; i < limit; i++) {
					
			output += "<div class='clear create-userName-result pointer animate-darker' userID='" + results[i]['id'] + "' username = '" + results[i]['name'] + "'>\
							<img src='/images/users/profile/pic/small/" + results[i]['id'] + ".jpg' onerror=\"this.src='/images/users/profile/pic/small/default.jpg'\" class='left' />\
							<div class='larger-indent left'>\
								<p class='larger-text left darkest heavy'>" + results[i]['name'] + "</p>\
								<p class='clear light'>" + results[i]['city'] + "</p>\
							</div>\
						</div>";
			
			
						
		}
	}
	
	$('#create-userName-results-container').html(output);
	
	if ($('#inviteSearchBar').is(':focus') && $('#inviteSearchBar').val().length >= 3) {
		// Search bar has focus and val is greater than 2 (protect against accidently overfire due to ajax delay
		$('#create-userName-results-container').show();
	}
	
	
}

/**
 * add user to list of people to invite
 */
function addUserToList(userID, name)
{
	if ($.inArray(userID, selectedUsers) > -1) {
		// User has been set already
		return false;
	}
	
	var output = "<div class='clear create-userName-list-item-container' userID='" + userID + "'><p class='left red heavy pointer remove-user'>X</p><p class='create-userName-list-item left darkest indent'>" + name + "</p></div>";
	
	selectedUsers.push(userID);
		
	$('#userList').append(output);
}

/**
 * store all userids in hidden input before submit form
 */
function buildUserIDs()
{
	var userIDs = selectedUsers.join();
	
	$('#userIDs').val(userIDs);
}

	
