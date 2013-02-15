// signup.js
var date = new Date();

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
	
	$('#email').keyup(function()
	{
		var value   = $(this).val();
		var regex	= /^\S+@\S+\.\S+$/;
		var isValid = $(this).isValid({regex: regex});
		
		changeInputBackground($(this), isValid);
	});
	
	
	/* keyup for signup password and reenter password */
	$('#signupPassword,#signupReenterPassword').keyup(function()
	{
		var value   = $.trim($(this).val());
		var isValid = $(this).isValid({minLength: 8, maxLength: 12});
		
		changeInputBackground($(this),isValid);
		
		if (($.trim($('#signupReenterPassword').val()).length > 0) &&
			($('#signupPassword').val() !== $('#signupReenterPassword').val())) {
			isValid = false;			
		}  else if($.trim($('#signupReenterPassword').val()).length < 1) {
			return;
		}
		
		changeInputBackground($('#signupReenterPassword'), isValid);
		
		
	})
	
	
	$('input[type=text],input[type=password]').blur(function()
	{
		$(this).trigger('keyup');
	})
	
	
});



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
 * update narrow column age
 * @params(month => mm,
 *		   day 	 => dd,
 *		   year  => yy)
 */
function updateNarrowColumnAge(month, day, year)
{
	var ageEle = $('#account-info-age');
	var age  = findAge(month,day,year);
	var str  = age + ' years old';
	var sexEle = ageEle.children();
	var sex    = sexEle.text();
	
	ageEle.text(str)
		  .fadeIn('fast');
						  
}


/**
 * update narrow column sex
 * @params(sex => male or female)
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
 * update narrow column sex
 * @params(sex => male or female)
 */
function updateNarrowColumnHeight(feet, inches)
{
	
	var str = feet + "' " + inches + "\"";
	
	$('#account-info-height').text(str)
						     .fadeIn('fast');
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


