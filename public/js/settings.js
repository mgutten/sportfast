// JavaScript Document
var confirmAction;
var changes;

$(function()
{
	
	$('.settings-tab').click(function()
	{
		$('.settings-tab-selected').removeClass('settings-tab-selected');
		$(this).addClass('settings-tab-selected');
		
		var value = $(this).text().replace(/ /g,'-').toLowerCase() + '-container';
		
		$('.settings-container').hide();
		$('#' + value).show();
	})
	
	
	$('#account-info-container').find('input').change(function()
	{
		changed = 'info';
		$(this).trigger('click');
	})
	
	
	$('#info-save-changes').click(function()
	{
		var fail = false;
		$('#account-info-container').find('input').each(function() {
			if ($(this).is('.input-fail')) {
				fail = true;
			}
		})
		
		if (!fail) {
			// Success
			$(this).parents('form').submit();
		}
		
	})
	
	/* remove subscribed game */
	$('.find-result-container').find('.settings-x').click(function(e)
	{
		e.preventDefault();
	
		var subscribe = 0;
		var typeID = $(this).parents('.find-result-container').attr('gameID');
		
		confirmAction = function () {
				var detailsEle = $('#details-ele');
				var userID = detailsEle.attr('actingUserID');
				var idType = 'gameID';

				subscribeToType(userID, idType, typeID, subscribe);
				changedAlert = true;
				//reloadPage();
		}
		
		populateConfirmActionAlert('unsubscribe');
	})
	
	$('.settings-sport-icon').click(function()
	{
		$('.signup-sports-hidden').hide();
		
		var sport = $(this).attr('sport');
		var lowerEle = $('#signup-sports-hidden-' + sport);
		
		lowerEle.show();
		
		if ($(this).is('.settings-user-sport')) {
			return false;
		}
		
		lowerEle.find('.signup-sports-form').addClass('animate-hidden-selected');
		
		$('#' + sport + 'Active').val(true);
		
		$(this).addClass('green-back');
		changed = 'sports';
	})
	
	
	$('.selectable-text').click(function()
	{
		changed = 'sports';
	});

	
	$('.signup-sports-what').find('.selectable-text').click(function()
	{
		if ($(this).text().toLowerCase() == 'league w/ refs' && $(this).is('.green-bold')) {
			// Show positions
			$(this).parents('.signup-sports-form').find('.signup-sports-position').show();
		} else if ($(this).text().toLowerCase() == 'league w/ refs' && !$(this).is('.green-bold')) {
			// Hide positions
			$(this).parents('.signup-sports-form').find('.signup-sports-position').hide();
		}
	})
	
	/* remove sport */
	$('.settings-sport-remove').click(function()
	{
		var parent = $(this).parents('.signup-sports-hidden');
		var sport  = parent.children('.signup-sports-form').attr('sport');
		var iconEle = $('#settings-sports-icon-' + sport);
		
		if (parent.is('.user-sport')) {
			confirmAction = function() {
				var detailsEle = $('#details-ele');
				var actingUserID = detailsEle.attr('actingUserID');
				
				removeSportFromUser(actingUserID, sport);
			}
			
			populateConfirmActionAlert('remove ' + sport + ' from your account', "<p class='clear width-100 center medium larger-margin-top'>This will remove you from all currents games in this sport.</p>");
			
			return;
		}
		
		iconEle.removeClass('green-back').addClass('medium-back');
		parent.hide();
		parent.children('.signup-sports-form').removeClass('signup-sports-form');
		
	})
	
	/* save sports */
	$('#sports-save-changes').click(function()
	{
		var submitFormSectionEle;
		// Test all the sports sections for completeness
		if (submitFormSectionEle = submitFormTestSports()) {	
			$('.signup-sports-hidden').hide();	
			submitFormSectionEle.parents('.signup-sports-hidden').show();
			return false;
		}
		$('#sports-form').submit();
	})

	
	
	/* availability calendar section */
	$('.availability').mousedown(function()
	{		
		changed = 'sports';
	})
	
	
	/* show all inner forms to undo changes in signup.js to display */
	$('.signup-sports-form').show();
	
	$('.user-sport').each(function()
	{
		var sectionEle = $(this).children('.signup-sports-form');
		var sport = sectionEle.attr('sport');
		
		$(this).find('.signup-sports-form').addClass('animate-hidden-selected');
		
		$('#' + sport + 'Active').val(true);
		
		$(this).find('.signup-sports-form-section').each(function()
		{
			if ($(this).is('.signup-sports-availability')) {
				return;
			}
			updateSportHiddenInputSelectable($(this));
		})
		
		if ($('#' + sport + 'What').val().toLowerCase().search('league') != -1) {
			// League is selected, show position category
			$('#signup-sports-position-' + sport).show();
		}
		
		$(this).find('.availability-calendar-day-container').each(function()
		{
			updateAvailabilityHiddenInput($(this));
		})
	})
	
	$(document).bind('click.changed',function() {
		
		var button;
		if (changed == 'sports') {
			button = $('#sports-save-changes-container');
		} else if (changed == 'info') {
			button = $('#info-save-changes-container')
		}
		
		button.show();
	})
		
})


/**
 * ajax remove sport from user
 */
function removeSportFromUser(userID, sport)
{

	var options = {userID: userID,
				   sport: sport}
	
	$.ajax({
		url: '/ajax/remove-sport-from-user',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			reloadPage();
		}
	})
}

/**
 * custom callback function from slider to update hidden input skill
 * @params(sliderEle => slider element
 *		   value     => new value of slider)
 */
function updateSkillHiddenInput(sliderEle, value) 
{
	
	var sport		   = getSportName(sliderEle);
	var hiddenEle 	   = $('#' + sport + 'Rating');
	
	hiddenEle.val(value);
		
	populateSliderText (sliderEle, value);
}

/** return sport name for element within step2(Sports)
 * @params (ele => any element that is within step2(Sports) form
 */
function getSportName(ele) 
{
	var sportFormEle = $(ele).parents('.signup-sports-form');
	var sportTitle   = sportFormEle.find('.signup-sports-title');
	var sport        = sportTitle.text().toLowerCase();
	
	return sport;
}

function buildSliders()
{
	/* sliders */
	var width = $('.signup-skill-slider').width();
	// strackbar requires element to not be hidden (offset().left)
	$('.signup-sports-hidden,#sports-container').show();
	$('.signup-skill-slider').strackbar({callback: updateSkillHiddenInput, 
										 defaultValue: 2,
										 minValue: 0,
										 maxValue: 6,
										 sliderHeight: 4,
										 sliderWidth: width,
										 style: 'style1', 
										 animate: false, 
										 ticks: false, 
										 labels: false, 
										 trackerHeight: 20, 
										 trackerWidth: 19 })
	// Hide element again
	$('.signup-sports-hidden,#sports-container').hide();
}