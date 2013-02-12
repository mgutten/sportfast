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
		updateNarrowColumnName($(this).val(), firstOrLast);
	})
	
	
	/* update age narrow column */
	$('#dobDay,#dobMonth,#dobYear').keyup(function()
	{
		if(($('#dobDay').val().length + $('#dobMonth').val().length + $('#dobYear').val().length) < 6) {
			return;
		}
		
		var month = $('#dobMonth').val();
		var day = $('#dobDay').val();
		var year = $('#dobYear').val();
		
		updateNarrowColumnAge(month,day,year);
	})
	
	
	
	
});

/**
 * simultaneously update input and narrow column values for firstname and lastname
 * @params(value => new value
 *		   firstOrLast => true=first name, false=last name)
 */
function updateNarrowColumnName (value, firstOrLast)
{
	var ele;
	if (firstOrLast) {
		//first name being changed
		ele = $('#account-info-first-name');
	} else {
		//last name being changed
		ele = $('#account-info-last-name');
		value = value[0];
	}
	
	ele.text(value);
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
	var age = findAge(month,day,year);
	var str = age + ' year old';
	
	$('#account-info-age').text(str);
}

