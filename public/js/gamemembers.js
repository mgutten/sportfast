// JavaScript Document
var membersArray = new Array();
var memberTimeout;

$(function()
{
	$(document).off('click.invite', '.invite-search-result,.profile-invite-result');
	
	/* add member to game */
	$(document).on('click.addMember','.profile-invite-result',function()
	{
		
		var classy;
		var searchBar;
		
		classy = $('.profile-invite-result');
		searchBar = $('#inviteSearchAlert');
		
		classy.remove();
		 
		searchBar.val('')
				 .focus();
		
		var detailsEle = getDetailsEle();
		var actingUserID = detailsEle.attr('actingUserID');
		var idType = detailsEle.attr('idType');
		var typeID = detailsEle.attr(idType);
		var receivingUserID = $(this).attr('userID');
		var action = 'invite';
		var type   = idType.replace(/ID/, '');
		var details;	
		
		addMemberToGame(typeID, receivingUserID);	
		
		
		return false; // prevent bug that closes animated div after selecting a name from the results
	})
	
	$('#inviteSearchAlert').keyup(function(e)
	{
		clearTimeout(memberTimeout);
		
		memberTimeout = setTimeout(function() {
			$('.profile-invite-result').each(function()
			{
				if ($(this).attr('member')) {
					return;
				}
				
				if ($.inArray('' + $(this).attr('userID'), membersArray) != -1) {
					$(this).append("<p class='right medium heavy'>MEMBER</p>");
					$(this).attr('member', 'true');
				}
			})
		}, 400);
	})
				

	
})

/**
 * invite user to type (game or team)
 * @params (gameID => gameID,
 *			userID => userID of member being added
 */
function addMemberToGame(gameID, userID)
{
	
	var options = {gameID: gameID,
				   userID: userID};
	   
	$.ajax({
		url:'/ajax/add-member-to-game',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			var str = 'Member added <br><span class="white">(reload to view changes)</span>';
			if (data != '') {
				// Is already a member
				str = 'This user is already a member';
			}
			showConfirmationAlert(str);
		}
	})
}
