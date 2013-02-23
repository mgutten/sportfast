// signup.js
var date = new Date();
// array to store usable, copyable availability-calendars from other sports
var copyAvailability = new Array();

$(function()
{
	
	/* update narrow column name value onkeyup */
	$('#firstName,#lastName').keyup(function()
	{
		var firstOrLast = true;
		if ($(this).attr('id') == 'lastName') {
			firstOrLast = false;
		}
		
		var regexp = /^[a-zA-Z]+-*[a-zA-Z]*$/g;
		var isValid = $(this).isValid({regex: regexp})
		
		changeInputBackground($(this),isValid);
		updateNarrowColumnName($(this).val(), firstOrLast);
		testDrop($(this));
	})
	
	
	/* update age narrow column */
	$('#dobDay,#dobMonth,#dobYear').keyup(function()
	{
		var value = $(this).val();
		// eliminate all non-integer values
		var newValue = value.replace(/[^\d+]/g,'');
	
		if(newValue !== value) {
			// There was a non-integer value
			$(this).val(newValue);
			value = newValue;
		}
			
		var isValid = $(this).isValid({minLength: 2, maxLength: 2});
		
		changeInputBackground($(this), isValid);
		
		if(($('#dobDay').val().length + $('#dobMonth').val().length + $('#dobYear').val().length) < 6) {
			return;
		}
		
		var month = $('#dobMonth').val();
		var day = $('#dobDay').val();
		var year = $('#dobYear').val();
		
		var age  = findAge(month,day,year);
		var str  = age + ' years old';
		
		updateNarrowColumnGeneric('age',str);
	})
	
	
	/* update dimensions narrow column */
	$('#heightFeet,#heightInches,#weight').keyup(function()
	{
		var indicator = $('#' + $(this).attr('id') + '-indicator');
		var heightInches,heightFeet,weight;
		var value = $(this).val();
		
		// eliminate all non-integer values
		var newValue = value.replace(/[^\d+]/g,'');
	
		if(newValue !== value) {
			// There was a non-integer value
			$(this).val(newValue);
			value = newValue;
		}
		
		
		if (value.length < 1) {
			// A value is present
			indicator.css('opacity',0);
		} else {
			// No value present
			indicator.css('opacity',1);
		}
		
		
		if ($(this).is('#heightInches')) {		
			// Limit value to within real constraints
			$(this).limitVal(0,12);
			var isValid = $(this).isValid({regex: 'num', minLength: 1,maxLength: 2});
			
			changeInputBackground($(this),isValid);
		}
		if ($(this).is('#heightFeet')) {		
			// Limit value to within real constraints
			$(this).limitVal(4,7);
			
			var isValid = $(this).isValid({regex: 'num', minLength: 1,maxLength: 1});

			changeInputBackground($(this),isValid);
		}
		
		if ($('#heightFeet').val() > 0 && $('#heightInches').val() > 0) {
			// Both height and feet are filled out
			var feet   = $('#heightFeet').val();
			var inches = $('#heightInches').val();
			var str = feet + "' " + inches + "\"";
			updateNarrowColumnGeneric('height', str)
		}
		if ($(this).is('#weight')) {
			// Dealing with weight		
			var isValid = $(this).isValid({regex: 'num', minLength: 2,maxLength: 3});

			changeInputBackground($(this),isValid);
			
			var str = (value.length < 1 ? 'N/A' : value + ' lb');
			updateNarrowColumnGeneric('weight',str);
		}
		
	
	})
	
	
	/* change color of sex icon onclick */
	$('.signup-sex-img').click(function() 
	{
		$('.signup-sex-selected').removeClass('signup-sex-selected');
		$(this).addClass('signup-sex-selected');
		var sex = $(this).attr('tooltip');
		updateNarrowColumnGeneric('sex', sex);
		
		$('#sex').val(sex);
		
	})
	
	/* check email validation */
	$('#email').keyup(function()
	{
		var value   = $(this).val();
		var regex	= /^\S+@\S+\.\S+$/;
		var isValid = $(this).isValid({regex: regex});
		
		changeInputBackground($(this), isValid);
	});
	
	
	/* validate signup password and reenter password */
	$('#signupPassword,#signupReenterPassword').keyup(function()
	{
		var value   = $.trim($(this).val());
		var isValid = $(this).isValid({minLength: 8, maxLength: 12});
		
		changeInputBackground($(this),isValid);
		
		if (($.trim($('#signupReenterPassword').val()).length > 0) &&
			($('#signupPassword').val() !== $('#signupReenterPassword').val())) {
			// Passwords do not match
			isValid = false;			
		}  else if($.trim($('#signupReenterPassword').val()).length < 1) {
			// There is no value for reenter password, do not turn red
			return;
		}
		
		changeInputBackground($('#signupReenterPassword'), isValid);	
		
	})
	
	
	/* validate street address */
	$('#streetAddress').keyup(function()
	{
		var value   = $(this).val();
		var regex	= /\w+/;
		var isValid = $(this).isValid({regex: regex, minLength: 1});
		
		changeInputBackground($(this), isValid);
	})
	
	
	/* validate zipcode */
	$('#zipcode').keyup(function()
	{
		var value   = $(this).val();
		var regex	= 'num';
		var isValid = $(this).isValid({regex: regex, minLength: 5, maxLength: 5});
		
		changeInputBackground($(this), isValid);
	})
	
	
	/* user checkbox to allow no street address */
	$('#noAddress').change(function()
	{
		var checked = $(this).prop('checked');
		
		if (checked) {
			$('#noAddress-text').css({top  : $('#streetAddress').offset().top + 4,
									  left : $('#streetAddress').offset().left + 10})
								.show();
			$('#streetAddress-container').css({opacity:0,
											   position: 'relative',
											   zIndex:-2})
		} else {
			$('#streetAddress-container').css({opacity:1,
											   position: 'static'})
			$('#noAddress-text').hide();
		}
	});
	
	
	/* change color of sex icon onclick */
	$('.sport-icon-large').click(function() 
	{
		var sport = $(this).attr('tooltip').toLowerCase();
		if($(this).is('.selected-green')) {
			// Already selected, return
			return;
		}
		
		// Not currently selected
		removeOld = false;
		// Make background green
		toggleGreenBackground($(this), removeOld);
		// Show "X"
		$(this).parent().children('.signup-sports-remove').css('opacity',1);
		// Remove/add narrow column sport
		toggleNarrowColumnSport(sport);
		// Should narrow column be expanded?
		testDrop($(this));
		
		// Change hidden input for this sport to active
		$('#' + sport + 'Active').val(true);

		// Hide any old dropdowns
		// Uncomment to only allow one form at a time (remove else from above)
		//$('.animate-hidden-selected.signup-sports-form').hide();
		
		var hiddenEle = $('#signup-sports-form-' + sport);
		var down      = false;
		animateNotShow(hiddenEle, down);
		
	})
	
	
	/* remove sport on remove click */
	$('.signup-sports-remove').click(function()
	{
		var sportIcon = $(this).next('img');
		if (!sportIcon.is('.selected-green')) {
			// sportIcon is not selected, return
			return;
		}
		// Change image to grey
		toggleGreenBackground(sportIcon, false);
		// Hide "x"
		$(this).css('opacity',0);
		var sport = sportIcon.attr('tooltip').toLowerCase();
		// Animate form to hidden
		var hiddenEle = $('#signup-sports-form-' + sport)
		animateNotShow(hiddenEle, true);
		// Hide narrow column img
		toggleNarrowColumnSport(sport);
		// Change hidden input for this sport to active
		$('#' + sport + 'Active').val(false);
		

		
	})
	
	
	/* slider */
	var width = $('.signup-skill-slider').width();
	// strackbar requires element to not be hidden (offset().left)
	$('.signup-sports-form').show();
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
	$('.signup-sports-form').hide();
												 
	
	/* handle onclick for sports-form elements */
	$('.signup-sports-position,.signup-sports-often,.signup-sports-what,.signup-sports-type').children('.signup-sports-selectable').click(function() 
	{
		var greenEles  = $(this).parent().children('.signup-sports-selectable.green-bold');
		var countGreen = greenEles.length;
		var limit      = 20;
		var removeOld  = false;
		if ($(this).parent().is('.signup-sports-position')) {
			// Position selectable was clicked
			limit = 2;
		} else if ($(this).parent().is('.signup-sports-often')) {
			limit = 1;
			removeOld = true;
			
		}
		
		if (countGreen > limit) {
			// With added selection we are over the limit
			$(this).removeClass('green-bold');
			if (removeOld) {
				greenEles.removeClass('green-bold');
				$(this).addClass('green-bold');
			}
		}
		
		var sectionEle = $(this).parent();
		updateSportHiddenInputSelectable(sectionEle);
	})
	
	
	/* availability calendar section */
	$('.availability').click(function()
	{
		toggleGreenBackground($(this), false);
		
		var dayEle = $(this).parents('.availabilty-calendar-container');
		updateAvailabilityHiddenInput(dayEle);
	})
	
	
	$('input[type=text],input[type=password]').blur(function()
	{
		// When focusout of input, check its value
		$(this).trigger('keyup');
	})
	
		
	/* test all inputs onload */
	$('.input-container').children('input').each(function()
	{
		if( $(this).val() !== '') {
			// Input has value, run test
			$(this).trigger('keyup');
		}
	})
	
	
});



/**
 * animate dropdown of narrow column signup vals
 * @params(ele => #firstName,#lastName)
 */
function testDrop(ele)
{
		var id = $.trim(ele.parents('.signup-section-container').find('.signup-section-title').text().toLowerCase()).replace(/ /g,'-');
		var narrowColumnEle = $('#narrow-column-' + id);
		var hiddenEle	    = narrowColumnEle.find('.narrow-column-body');
		
		if (hiddenEle.is('.animate-hidden-selected')) {
			return;
		}
		

		if (hiddenEle.innerHeight() > 5) {
			// Body of narrow column has values in it
			narrowColumnEle.children('.narrow-column-header').trigger('click');
		}
}


/**
 * simultaneously update input and narrow column values for firstname and lastname
 * @params(value => new value
 *		   firstOrLast => true=first name, false=last name)
 */
function updateNarrowColumnName (value, firstOrLast)
{
	var ele;
	if (firstOrLast) {
		// First name being changed
		ele = $('#account-info-first-name');
	} else {
		// Last name being changed
		ele = $('#account-info-last-name');
		// Only show first letter of last name
		value = (value[0] ? value[0] : '');
	}
	
	ele.text(value).show();


}


/**
 * find the age given month, day, and year of birth
 * @params(month => mm,
 *		   day 	 => dd,
 *		   year  => yy)
 */
function findAge(month, day, year)
{
	//convert month to array format
	month--;

	var birthDate = new Date(year, month, day);
	var today = date;
	
	var age = today.getFullYear() - birthDate.getFullYear();
	
	var m = today.getMonth() - birthDate.getMonth();
	
	if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
	
    return age;

}


/**
 * update narrow column generic for Account Info
 * @params(section => what section (str)
 		   str	   => value to input)
 */
function updateNarrowColumnGeneric(section, str)
{
	var ele 	    = $('#account-info-' + section);
	var priorLength = $.trim(ele.text()).length;
	
	ele.text(str)
	   .hide();	
	
	if (!ele.is('.no-clear') && priorLength < 1) {
		animateNarrowColumnBody(ele); 
	} else {
		ele.show();
	}
						  		 
}


/**
 * toggle sport icon in narrow column shown and not
 * @params(sport => name of sport)
 */
function toggleNarrowColumnSport(sport)
{
	var ele 	    = $('#signup-narrow-column-sport-' + sport);
	ele.toggle();
						  		 
}


/**
 * animate body div down before fading values in
 * @params(ele => element that has been expanded)
 */
function animateNarrowColumnBody(ele)
{

	var parentEle  = ele.parents('.narrow-column-body');	
	var bodyHeight = parentEle.height();

	var newHeight  = bodyHeight + ele.height();
	
	if (parentEle.css('display') == 'none') {
		ele.show();
		return false;
	}
	
	parentEle.animate({height: newHeight}, {duration: 400, complete: function() {
																		ele.fadeIn('fast')
																	}
	})
						  		 
}



/**
 * change input background color based on validity
 * @params(sex => male or female)
 */
function changeInputBackground(ele, isValid)
{
	
	if (!isValid) {
		// Failed validity test
		ele.removeClass('input-success').addClass('input-fail');
	} else {
		// Correct input
		ele.removeClass('input-fail').addClass('input-success');
	}

}

/**
 * update hidden element for each sport
 * @params(sectionEle => parent container ele for values that have been changed)
 */
function updateSportHiddenInputSelectable(sectionEle)
{
	var values = new Array();
	var selectedChildren = sectionEle.children('.signup-sports-selectable.green-bold');
	var value;
	
	selectedChildren.each(function()
	{
		// Replace any internal tags and their content with blank
		value = $.trim($(this).html().replace(/[<]+.*[\/>]/, ''))
		values.push(value);
	})

	var idStart            = sectionEle.attr('id').replace(/signup-sports-/,'');
	var sport   = getSportName(sectionEle);
	var hiddenInputSection = idStart.replace(/-\w+/,'');

	hiddenInputSection	   = capitalize(hiddenInputSection)
	
	var combinedId		   = sport + hiddenInputSection;
	var hiddenInput        = $('#' + combinedId);
	
	
	hiddenInput.val(values);
	
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



/**
 * custom callback function from slider to update hidden input skill
 * @params(dayEle => day container element)
 */
function updateAvailabilityHiddenInput(dayEle) 
{
	
	var values = new Array();
	var selectedChildren = dayEle.find('.availability.selected-green');
	var value;
	var sport  = getSportName(dayEle);
	
	if (selectedChildren.length > 0) {
		// Timeslots are selected
		selectedChildren.each(function()
		{
			// Replace any internal tags and their content with blank
			value = $(this).attr('hour');
			values.push(value);
		})

		if ($.inArray(sport, copyAvailability) == -1) {
			copyAvailability.push(sport);
			showCopyableAvailabilityCalendars();
		}
		
	} else {
		// No timeslots selected
		var index = $.inArray(sport, copyAvailability);
		if (index > -1) {
			delete copyAvailability[index];
			showCopyableAvailabilityCalendars();
		}
	}
		
	
	var day	   = dayEle.attr('day');
	var id     = sport + 'Availability' + day;
	var hiddenInput = $('#' + id);
	
	hiddenInput.val(values);

	
}


/** display all copyable availability calendars which have values
 */
function showCopyableAvailabilityCalendars()
{
	var output = '';
	var sport;
	var limit = Object.keys(copyAvailability).length;
	output += '<select class="availability-calendar-copyable medium pointer">\
			   <option>Choose</option>';
	for (i = 0; i < limit; i++) {
		
		sport   = copyAvailability[i];
		output += '<option>' + capitalize(sport) + '</option>';
			   
	}
	
	output += '</select>';
	
	$('.signup-sports-availability-copy-option-container').html(output);
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


