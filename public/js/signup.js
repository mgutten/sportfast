// signup.js
var date = new Date();
// array to store usable, copyable availability-calendars from other sports
var copyAvailability = new Array();
var jcropAPI;
var days = new Array('Su','M','T','W','Th','F','Sa');
var oftenConversion = new Array('30','7','2','0');

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
		
		if ($(this).is('#dobMonth') && $(this).val() !== '0') {
			$(this).limitVal(1,12);
		}
		
		if ($(this).is('#dobDay') && $(this).val() !== '0') {
			$(this).limitVal(1,32);
		}
			
		var isValid = $(this).isValid({minLength: 2, maxLength: 2, number:true});
		
		changeInputBackground($(this), isValid);
		
		if(($('#dobDay').val().length + $('#dobMonth').val().length + $('#dobYear').val().length) < 6) {
			return;
		}
		
		var month = $('#dobMonth').val();
		var day = $('#dobDay').val();
		var year = $('#dobYear').val();
		
		var age  = findAge(month,day,year);
		var str  = age + ' years old';
		
		$('#age').val(age);
		
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
			$(this).limitVal(0,11);
			var isValid = $(this).isValid({number: true, minLength: 1,maxLength: 2});
			
			changeInputBackground($(this),isValid);
		}
		if ($(this).is('#heightFeet')) {		
			// Limit value to within real constraints
			$(this).limitVal(4,7);
			
			var isValid = $(this).isValid({number:true, minLength: 1,maxLength: 1});

			changeInputBackground($(this),isValid);
		}
		
		if ($('#heightFeet').val() > 0 && $('#heightInches').val().length > 0) {
			// Both height and feet are filled out
			var feet   = $('#heightFeet').val();
			var inches = $('#heightInches').val();
			var str = feet + "' " + inches + "\"";
			
			$('#height').val(feetToInches(feet,inches));
			
			updateNarrowColumnGeneric('height', str)
		}
		if ($(this).is('#weight')) {
			// Dealing with weight		
			var isValid = $(this).isValid({number: true, minLength: 2,maxLength: 3});

			changeInputBackground($(this),isValid);
			
			var str = (value.length < 1 ? 'N/A' : value + ' lb');
			updateNarrowColumnGeneric('weight',str);
		}
		
	
	})
	
	
	/* change color of sex icon onclick */
	$('.signup-sex-img').mousedown(function() 
	{
		if ($(this).parent().is('.signup-sex-container-fail')) {
			$(this).parent().removeClass('signup-sex-container-fail');
		}
		
		$('.signup-sex-selected').removeClass('signup-sex-selected');
		$(this).addClass('signup-sex-selected');
		var sex = $(this).attr('tooltip');
		updateNarrowColumnGeneric('sex', sex);
		
		$('#sex').val(sex[0]);
		
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
		
		var isValid = $(this).isValid({number: true, minLength: 5, maxLength: 5});
			
		
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
		if ($(this).is('.selected-green')) {
			// Already selected, return
			return;
		}
		if ($('#signup-sports-what').is('.red')) {
			// No selection was made previously, now there is, undo red title
			$('#signup-sports-what').removeClass('red')
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
		var sport = sportIcon.attr('tooltip').toLowerCase();		
		var index = $.inArray(sport, copyAvailability);
		if (index > -1) {
			copyAvailability.splice(index,1);
			createSportsCopyableDropdown();
		}
		
		// remove selected status from the associated form
		$('#signup-sports-form-' + sport).removeClass('animate-hidden-selected');
		
		// Change image to grey
		toggleGreenBackground(sportIcon, false);
		// Hide "x"
		$(this).css('opacity',0);
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
	$('.availability').mousedown(function()
	{
		// Make text unselectable while mouse is down
		makeTextSelectable(false);

		$('.availability').bind('mouseenter.availability',function()
		{
			// If mousedown still and mouseenter availability, toggle
			toggleGreenBackground($(this), false);
		
			var dayEle = $(this).parents('.availability-calendar-day-container');
			updateAvailabilityHiddenInput(dayEle);
		})
		
		toggleGreenBackground($(this), false);
		
		var dayEle = $(this).parents('.availability-calendar-day-container');
		updateAvailabilityHiddenInput(dayEle);
	})
	
	$(document).mouseup(function()
	{
		// Remove bound mouseenter created by mousedown event on availability
		$('.availability').unbind('mouseenter.availability');
		// Make text selectable again
		makeTextSelectable(true);
	});
	
	
	/* copy one sport's availability to another */
	$(document).on('click','#sports-copyable>.dropdown-menu-hidden-container>.dropdown-menu-options-container>.dropdown-menu-option-container',function()
	{
		var sport  	     = $(this).children('p').text().toLowerCase();
		var receivingEle = $(this).parents('.signup-sports-availability').children('.availabilty-calendar-container');
		var copiedEle	 = $('#availability-calendar-container-' + sport);
		
		copyAvailabilities(receivingEle, copiedEle);
		
	})
	
	
	/* signup alert for import picture */
	$('#signup-import-main-upload-button').click(function()
	{
		$('.alert-black-back,#signup-import-alert-container').show();
	})
	
	$('#signup-import-alert-accept').click(function()
	{
		$('.alert').hide();
	});
	
	$('#profilePic').change(function()
	{
		if (this.files[0].size > 6291456) {
			alert('File size is too large. There is a strict 6MB limit.');
			return false;
		}
		$(this).parents('form').submit();
	})
	
	/* ajax submit of import profile pic */
	$('#upload-profile-pic').ajaxForm({success: function(data) {
													if (data == 'errorFormat') {
														alert('The file you submitted is in the wrong format. Please select a jpg, png, or gif.');
														return false;
													} else if(data == 'errorUpload') {
														alert('An error occurred, please try again later.');
														return false;
													}
													if (jcropAPI) {
														jcropAPI.destroy()
													}
													$('#fileName').val(data);
													$('#signup-import-main-img,.narrow-column-picture').attr('src',data)
													$('#signup-import-alert-img').attr('src',data)
																				 .maintainRatio()
																				 .Jcrop({aspectRatio: 1.26,
																				 		 setSelect: [0,0,200,200],
																						 onSelect: updateProfilePic
																						 },function(){
       																						jcropAPI = this;
																							})
																							
													$('#signup-import-alert-accept').show();
																				 
													
												}
	})
	
	
	
	
	$('input[type=text],input[type=password]').blur(function()
	{
		// When focusout of input, check its value
		$(this).trigger('keyup');
	})
	
		
	/* test all inputs onload */
	$('.input-container').children('input').each(function()
	{
		if( $(this).val() !== '' && !$(this).is('.input-fail')) {
			// Input has value and input is not initiated with fail (ie failed zend form validation), run test
			$(this).trigger('keyup');
		}
	})
	
    
	/* FINAL JAVASCRIPT VALIDATION BEFORE SUBMIT FORM */
	$('#signup-finish').click(function()
	{
		var scrollToEle = new Array();
		// Trigger each inputs keyup to add/remove input-fail class
		$('input[type=password],input[type=text]').trigger('keyup');
		var fail = false;
		
		$('input[type=password],input[type=text]').each(function()
		{
			if (fail) {
				// An input already failed
				return;
			}
			if ($(this).is('.input-fail')) {
				// This input failed, scroll to show
				scrollToEle.push($(this).parents('.signup-section-container'))
				fail = true;
			}
		})
		
		fail = true;
		// Test that sex is selected
		$('.signup-sex-img').each(function()
		{			
			if ($(this).is('.signup-sex-selected')) {
				// One sex is selected, fail is false
				fail = false;
			}
		})
		
		if (fail) {
			$('.signup-sex-img').parent().addClass('signup-sex-container-fail');
			if (scrollToEle.length < 1) {
				// Same scroll spot for sex and inputs, only push if not already in array
				scrollToEle.push($('.signup-sex-img').parents('.signup-section-container'));	
			}
		}
		
		var submitFormSectionEle;
		// Test all the sports sections for completeness
		if (submitFormSectionEle = submitFormTestSports()) {
			scrollToEle.push(submitFormSectionEle)
		}
		
		
		if ($('#agree').prop('checked') == false) {
			// Have not checked the agree box
			$('#agree').siblings('.checkbox-text').addClass('red');
			scrollToEle.push($('#agree'))
		}
		
		if (scrollToEle.length > 0) {
			// Something failed along the way, scroll to the first failed element
			$('html, body').animate({scrollTop: scrollToEle[0].offset().top - 20}, 1000);
		} else {
			// All inputs are clear and ready, submit the form!
			$('#signupForm').submit();
		}
		
			
	})
 
	
});




/**
 * form is being submitted, test all selected sports for completeness
 * @params(coords => coordinates of jcrop)
 */
function submitFormTestSports()
{
	var scrollToEle = false;
	var signupSportsSelected = $('.signup-sports-form.animate-hidden-selected');
	
	if (signupSportsSelected.length < 1) {
		// No sports are selected
		$('#signup-sports-what').addClass('red');
		return scrollToEle = $('#signup-section-container2');
	}
	
	signupSportsSelected.each(function()
	{
		var sport = getSportName($(this).children());
		var section;
		
		if (!loopSportsSections($(this))) {
			scrollToEle = $(this)
			return false;
		}
		
	})
	
	return scrollToEle;
	
}


/**
 * loop through each section of sport's form and test values
 * @params(formEle => .signup-sports-form)
 */

function loopSportsSections(formEle) {
	
	var sport    = getSportName(formEle.children());
	var sections = new Array('position','type','what','often','availability');
	var section;
	var scrollToEle;
		
	
	for (i = 0; i < sections.length; i++) {
		
		if ((section = formEle.find('.signup-sports-' + sections[i])).length > 0) {
			// Category is present, test for selected values
			
			if (!testIfValues(section, sport)) {
				scrollToEle = formEle;
			}
		}
	}
	
	if (scrollToEle) {
		return false;
	}
	
	return true;
}

/**
 * test sports form section for values
 * @params(section => section element,
 *	   	   sport   => what sport form we are in (str))
 */
function testIfValues(section, sport)
{
	var sectionTitle = section.attr('class').replace(/signup-sports-form-section/,'');
	sectionTitle     = capitalize($.trim(sectionTitle.replace(/signup-sports-/,'')));
	var id           = sport + sectionTitle //e.g. basketballRating
	var hiddenInput  = $('#' + id);
	var titleEle     = section.children('.signup-sports-form-section-title');
	var fail		 = false;
	
	if (sectionTitle == 'Availability') {
		// Special case for availability section
		fail = true;
		for (i = 0; i < 6; i++) {
			hiddenInput = $('#' + id + i) // e.g. basketballAvailabilitySu
			if (hiddenInput.val().length > 0) {
				// At least one availability has value
				fail = false;
			}
		}
		
		if (fail) {
			titleEle.addClass('red');
			return false;
		} else {
			titleEle.removeClass('red');
			return true;
		}		
		
	}

	if (sectionTitle == 'Type' && sport == 'tennis') {
		// Require tennis to have at least one typeName and typeSuffix selected
		var value = hiddenInput.val();
		if ((value.indexOf('Doubles') >= 0) || 
			(value.indexOf('Singles') >= 0)) {
				// Passed typeName test
				if (value.indexOf('Rally') >= 0 ||
				    value.indexOf('Match') >= 0) {
						titleEle.removeClass('red');
						return true;
					}
				
			}
		titleEle.addClass('red');
		return false;
	}
				
	
	// Rest of sections test if input has any values
	if (hiddenInput.val().length < 1) {
		titleEle.addClass('red');
		return false;
	} else {
		titleEle.removeClass('red');
		return true;
	}
	
	
}


/**
 * callback function from jCrop to update the import-main img to reflect cropped image
 * @params(coords => coordinates of jcrop)
 */
function updateProfilePic(coords)
{
	// 199 = width of preview image
	var rx = 199 / coords.w;
	// 160 = height of preview image
	var ry = 160 / coords.h; 
	var height = $('#signup-import-alert-img').height(); // height of original image
	var width  = $('#signup-import-alert-img').width() //width of original image

	
	$('#signup-import-main-img,.narrow-column-picture').css({
		width: Math.round(rx * width) + 'px',
		height: Math.round(ry * height) + 'px',
		marginLeft: '-' + Math.round(rx * coords.x) + 'px',
		marginTop: '-' + Math.round(ry * coords.y) + 'px'
	});
	
	$('#fileWidth').val(coords.w);
	$('#fileHeight').val(coords.h);
	$('#fileX').val(coords.x);
	$('#fileY').val(coords.y);
	
}

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
 * @params(ele => inputEle to change background of,
 		   isValid => should it be green (ok?) (boolean))
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
	
	if (sectionEle.is('.signup-sports-often')) {
		var index = selectedChildren.index() - 1;
		values = oftenConversion[index];
	}
	
	
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
			createSportsCopyableDropdown();
		}
		
	} else {
		// No timeslots selected
		var index = $.inArray(sport, copyAvailability);
		
		if (index > -1) {
			
			copyAvailability.splice(index,1);
			createSportsCopyableDropdown();
			
		}
	}
		
	
	var day	   = $.inArray(dayEle.attr('day'), days);
	var id     = sport + 'Availability' + day;
	var hiddenInput = $('#' + id);

	hiddenInput.val(values);

	
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


/** create ajax dropdown based on selected sports
 * 
 */
function createSportsCopyableDropdown() 
{
	if (Object.keys(copyAvailability).length < 1) {
		// There are no elements for dropdown
		return;
	}
	var options  = copyAvailability;
	var id       = 'sports-copyable';
	var selected = capitalize(options[0]);
	
	$.ajax({
		type: 'POST',
		url:  '/ajax/create-basic-dropdown',
		data: {id: id, selected: selected, options: options},
		success: function(data) {
			$('.signup-sports-availability-copy-option-container').html(data)
		}
	})
	
}


/** copies availability from one sports section to another
 * @params (receivingEle => element that is being copied to
 *			copiedEle    => element that is being copied)
 */
function copyAvailabilities(receivingEle, copiedEle) 
{
	var receivingSport = getSportName(receivingEle);
	var copiedSport = getSportName(copiedEle);
	if (receivingSport == copiedSport) {
		// Tried to copy the same sport, deny
		return;
	}
	receivingEle.find('.availability').removeClass('selected-green');
	
	copiedEle.find('.availability.selected-green').each(function()
	{
		var id = $(this).attr('id').replace(/\w+/,'')
		id     = receivingSport + id;
		receivingEle.find('#' + id).addClass('selected-green');
		
	})

	var dayEle;
	for (i = 0; i < days.length; i++) {
		// Loop through all days and update hidden input
		dayEle = $('#' + receivingSport + '-availability-calendar-day-container-' + days[i])
		updateAvailabilityHiddenInput(dayEle) 
	}


}


