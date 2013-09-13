// success page for create game or team

var selectedUsers = new Array();

$(function()
{
	/* prevent form submit on enter */
	$('#userName').keydown(function(e)
	{
		if (e.keyCode == 13) {
			// Enter, return false
			e.stopPropagation();
			e.preventDefault();
		}
	})
	
	$('#userName').keyup(function(e)
	{
		
		if ($('.create-userName-result').length > 0 && 
			((e.keyCode >= 37 && e.keyCode <= 40) || e.keyCode == 13)) {
							  
			  var ele;
			  if ($('.create-userName-result.selected').length > 0) {
				  
				  // Already result selected
				  if (e.keyCode == 40) {
					  // down 
					  if ($('.create-userName-result.selected').next('.create-userName-result').length > 0) {
						  ele = $('.create-userName-result.selected').next('.create-userName-result')
					  } else {
						  // There is no next ele, return
						  return;
					  }
				  }
				  if (e.keyCode == 38) {
					  // up
					  ele = $('.create-userName-result.selected').prev('.create-userName-result');
				  }
				  if (e.keyCode == 13) {
					  // enter key, redirect to this
					  $('.create-userName-result.selected').trigger('click');
				  }
				  
			  } else if (e.keyCode == 40 && $('.create-userName-result').length > 0) {
				  // No result already selected
				  ele = $('.create-userName-result').first();
			  } 
				
			  
			  $('.create-userName-result').removeClass('selected');
			  ele.addClass('selected');
		}
		
		if (e.keyCode >= 37 && e.keyCode <= 40) {
			// Arrow keys
			return false;
		}
		
		
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
	
	$('#create-success-addNote').click(function()
	{
		$(this).hide();
		$('#create-success-removeNote').show();
		$('#note-container').show();
	})
	
	$('#create-success-removeNote').click(function()
	{
		$(this).hide();
		$('#create-success-addNote').show();
		$('#note-container').hide();
	})
	
	
	$(document).on('mouseenter', '.create-userName-list-item-container', function()
	{
		$(this).children('.remove-user').show();
	})
	.on('mouseleave', '.create-userName-list-item-container', function()
	{
		$(this).children('.remove-user').hide();
	})
		
	
	$(document).on('click','.create-userName-result',function()
	{
		addUserToList($(this).attr('userID'), $(this).attr('username'));
		
		$('#create-userName-results-container').html('');
		
		$('#userName').val('')
					  .focus();
		
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
	
	var output = "<div class='clear create-userName-list-item-container margin-top' userID='" + userID + "'><p class='left red heavy pointer hidden hidden remove-user margin-top'>X</p><img src='/images/users/profile/pic/tiny/" + userID + ".jpg' onerror=\"this.src='/images/users/profile/pic/tiny/default.jpg'\" class='indent left' /><p class='create-userName-list-item left margin-top darkest indent'>" + name + "</p></div>";
	
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

	
