// Success page for create game or team

var selectedUsers = new Array();
var typing;

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
		if (e.keyCode == 8) {
			// Do nothing on backspace
			return;
		}
		
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
		searchDatabase($(this).val(), populateSearchResultsInvite, limit, $('#create-userName-results-container'));
	});
	
	$('#inviteSearchAlert').keyup(function(e)
	{
		if (e.keyCode == 8) {
			// Do nothing on backspace
			return;
		}
		
		if ($('.profile-invite-result').length > 0 && 
			((e.keyCode >= 37 && e.keyCode <= 40) || e.keyCode == 13)) {
							  
			  var ele;
			  if ($('.profile-invite-result.selected').length > 0) {
				  
				  // Already result selected
				  if (e.keyCode == 40) {
					  // down 
					  if ($('.profile-invite-result.selected').next('.profile-invite-result').length > 0) {
						  ele = $('.profile-invite-result.selected').next('.profile-invite-result')
					  } else {
						  // There is no next ele, return
						  return;
					  }
				  }
				  if (e.keyCode == 38) {
					  // up
					  ele = $('.profile-invite-result.selected').prev('.profile-invite-result');
				  }
				  if (e.keyCode == 13) {
					  // enter key, redirect to this
					  $('.profile-invite-result.selected').trigger('click');
				  }
				  
			  } else if (e.keyCode == 40 && $('.profile-invite-result').length > 0) {
				  // No result already selected
				  ele = $('.profile-invite-result').first();
			  } 
				
			  
			  $('.profile-invite-result').removeClass('selected');
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
		searchDatabase($(this).val(), populateSearchResultsInvite, limit, $('#invite-alert-results'));
	});
	
	$('#profile-invite-emails').focus(function()
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
	
	$('.emails').keyup(function()
	{
		convertEmails($(this).val());
		
		clearTimeout(typing);
		typing = setTimeout(function() {
			inputEmail();
		}, 1200);
		
	})
	
	$('#profile-invite-emails-outer-container').click(function()
	{
		$('.emails').focus();
	});
	
	/* remove email from list */
	$(document).on('click', '.invite-email-x', function() {
		clearTimeout(typing);
		
		$(this).parents('.invite-email-container').remove();
		updateNumRecipients();
	})
	
	/* edit parsedEmail */
	$(document).on('click', '.invite-email', function() {
		clearTimeout(typing);
				
		inputEmail();

		$('.emails').focus();
		$('.emails').val($(this).text());
		
		$(this).parents('.invite-email-container').remove();
		updateNumRecipients();
		
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
	
	$('#invite-form').submit(function(e)
	{
		convertEmails($('.emails').val());
		
		setEmails();
	})
	
})

/**
 * populate invite button's search results
 * @params (results => returned results from ajax,
 *			container => $(obj) that will receive the results)
 */
function populateSearchResultsInvite(results, container) 
{
	var output = '';
	
	if (results.length < 1) {
		// No results
		output += "<div class='header-search-result dark-back medium'>No results found</div>";
	} else {
		// Results found
		var limit = (results.length > 5 ? 5 : results.length);
		var src, classy;
		
		if (container.is('#create-userName-results-container')) {
			// Add player to reserve list or invite list
			classy = 'create-userName-result';
		} else if (container.is('#invite-alert-results')) {
			classy = 'profile-invite-result';
		}
		
		for (i = 0; i < limit; i++) {
					
			output += "<div class='clear " + classy + " userName-search-result pointer animate-darker' userID='" + results[i]['id'] + "' username = '" + results[i]['name'] + "'>\
							<img src='/images/users/profile/pic/small/" + results[i]['id'] + ".jpg' onerror=\"this.src='/images/users/profile/pic/small/default.jpg'\" class='left' />\
							<div class='larger-indent left'>\
								<p class='larger-text left darkest heavy'>" + results[i]['name'] + "</p>\
								<p class='clear light'>" + results[i]['city'] + "</p>\
							</div>\
						</div>";
			
			
						
		}
	}
	
	container.html(output);
	
	if ($('#inviteSearchBar').is(':focus') && $('#inviteSearchBar').val().length >= 3) {
		// Search bar has focus and val is greater than 2 (protect against accidently overfire due to ajax delay
		container.show();
	}
	
	
}

/**
 * set emails to hidden input
 */
function setEmails()
{
	var str = '';
	var counter = 0;
	
	$('.invite-email').each(function()
	{
		if (counter != 0) {
			str += ',';
		}
		
		str += $(this).text();
		counter++;
	})
	
	$('#emails').val(str);
}

function inputEmail()
{
	var val = $('.emails').val();
	$('.emails').val(val + ' '); // Trigger attempted input of current name
	
	var output = convertEmails($('.emails').val());
	
	if (output) {
		// It was submitted
		$('.emails').val('');
	} else {
		$('.emails').val(val);  // Bring back to original
	}
}

/**
 * full process of parsing emails and converting to html
 */
function convertEmails(text)
{
	
	var emails = parseEmails(text);
	  
	var output = convertParsedEmails(emails);
	
	
	if (output) {
		updateParsedEmails(output);
	}
	
	updateNumRecipients();
	
	
	$('#profile-invite-parsedEmails').scrollTop($('#profile-invite-parsedEmails')[0].scrollHeight);
	
	return output;
	
}

/**
 * update number of recipients in list
 */
function updateNumRecipients()
{
	if ($('.invite-email').length > 0) {
		// Hide overlay
		$('#invite-numEmails-container').css('opacity', 1);
		$('#input-overlay-emailsTextArea').css('visibility', 'hidden');
	} else {
		$('#invite-numEmails-container').css('opacity', 0);
		$('#input-overlay-emailsTextArea').css('visibility', 'visible');
	}
	
	$('#invite-numEmails').text($('.invite-email').length);
}

/**
 * parse emails into separate, easy-to-see
 * @params(text => text of emails)
 */
function parseEmails(text)
{ 
	var emails = text.match(/([a-zA-Z0-9.(\+)_-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)(?=(>)?(\,|\s)+)/gi); // Array of emails
	
	return emails;
		
}
	
/**
 * convert array of emails to proper html
 */
function convertParsedEmails(emails)
{ 
	if (emails == null) {
		return false;
	}
	
	var output = '';
	for (i = 0; i < emails.length; i++) {
		output += "<div class='invite-email-container clear'>\
						<p class='medium clear invite-email light-back'>" + emails[i] + "</p>\
						<p class='medium left light-back invite-email-x pointer' tooltip='Remove'>x</p>\
					</div>";
				 
	}
	
	return output;
		
}	

/**
 * take html of parsed emails and place in DOM
 */
function updateParsedEmails(output)
{
	$('#profile-invite-emails').val('');
	var ele = $('#profile-invite-parsedEmails').children('.invite-email-container').last();
	if (ele.length > 0) {
		// There is already an email, place after
		ele.after(output);
	} else {
		$('#profile-invite-parsedEmails').prepend(output);
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

	
