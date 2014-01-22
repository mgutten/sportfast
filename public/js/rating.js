// rating.js
var ratingClicked;
var chartData = new Object();
var data;
var lineChart;
var giveStats = new Array();
var avgStats = new Array();
var decisiveness = new Array();
var sports = new Object();
var hovering = false

$(function()
{
	
	/* bug fix to display container of charts so they can be drawn properly */
	$('.rating-section-container.hidden').addClass('shown');
	
	/* change section from skills->stats or vice versa for each sport */
	$('.rating-section-tab').click(function()
	{
		if ($(this).is('.selected')) {
			// Already selected
			return false;
		}
		$(this).parent().children('.rating-section-tab').toggleClass('selected');
		
		var container = $(this).attr('container');
		
		$(this).parents('.rating-sport-container').find('.rating-section-container').toggleClass('hidden');
	})
	
	$('.rating-icon-container').click(function()
	{
		var sport = $(this).find('.rating-icon').attr('sport');
		
		$('.rating-sport-container').hide();
		
		$('.rating-icon-num').show();
		$(this).children('.rating-icon-num').hide();
		
		$('#rating-' + sport + '-container').show();
		
		$('.rating-icon.selected').removeClass('selected');
		$(this).find('.rating-icon').addClass('selected');
		

		$('#selected-sport').text(capitalize(sport));
	})
	
	
	$('.rating-recent-outer-container').hover(function()
	{
		$(this).addClass('selected');
		$(this).find('.rating-recent-overlay-container').stop().animate({opacity: 1}, 300);
		$(this).find('.rating-recent-new').fadeOut();
		$(this).find('.rating-recent-game').stop().animate({left: '96px'}, 300);
	}, function()
	{
		$(this).removeClass('selected');
		$(this).find('.rating-recent-overlay-container').stop().animate({opacity: 0}, 300);
		$(this).find('.rating-recent-game').stop().animate({left: '0px'}, 300);
	});
	
	
	$('.rating-skillChart-column-container').hover(function()
	{
		$(this).find('.rating-skillChart-value').stop().animate({opacity: 1}, 300);
		/*$(this).find('.rating-skillChart-skill').stop().animate({opacity: 0}, 300);
		$(this).find('.rating-skillChart-ing').stop().animate({opacity: 1}, 300);*/
	}, function()
	{
		$(this).find('.rating-skillChart-value').stop().animate({opacity: 0}, 300);
		/*$(this).find('.rating-skillChart-skill').stop().animate({opacity: 1}, 300);
		$(this).find('.rating-skillChart-ing').stop().animate({opacity: 0}, 300);*/
	})
	
	$('.rating-star-clickable').mousemove(function(e)
	{
		if (ratingClicked) {
			return false;
		}

		setRatingWidth(e, $(this))
		
	})
	.mouseout(function()
	{
		if (!ratingClicked) {
			$(this).children('.rating-star-back').css('width', 0);
		}
	})
	.click(function(e)
	{
		ratingClicked = true;
		
		var inputEle = $('#rating-hidden');
		
		
		var width = setRatingWidth(e, $(this));
		
		// out of 5 stars
		inputEle.val(width/$(this).width() * 5); 
	})
		
	$('#user-rating').submit(function()
	{
		if ($('#rating-hidden').val() == '') {
			// Did not input a rating
			showConfirmationAlert('Please click the stars to rate this park');
			return false;
		}
	})
	
	$('.rating-chart-option').click(function()
	{
		if ($(this).is('.green-bold')) {
			// Already selected
			return false;
		}
		
		$('.rating-chart-option.green-bold').removeClass('largest-text green-bold')
											.addClass('light');
											
		$(this).addClass('largest-text green-bold')
			   .removeClass('light');
			   
		var value = $(this).text().toLowerCase();
		
		createScatterChart(chartData[value]);
			   
		
	})
	
	$('.flag-incorrect').click(function()
	{
		flagRemoval($(this).attr('userRatingID'));
	})
	
   /*
	if ($('#chart').length > 0) {
		// Only load charts on rating page
		
		google.load("visualization", "1", {"callback" : createInitialChart,
										   "packages" :["corechart"]});
	}
	*/
	
	google.load("visualization", "1", {callback: createInitialChart,
									   packages: ["corechart"]});
	
	
})

/**
 * ajax flag rating for removal
 */
function flagRemoval(userRatingID)
{
	$.ajax({
		url: '/ajax/flag-removal',
		type: 'POST',
		data: {userRatingID: userRatingID},
		success: function() {
			showConfirmationAlert('Rating will be reviewed shortly');
			reloadPage();
		}
	})
}

function createPieChart(dataArray, eleID, opts)
{
        var data = google.visualization.arrayToDataTable(dataArray);
		
		
        var options = {
		  chartArea:{left:0,top:15,width:"100%",height:"88%"},
		  //backgroundColor: '#222',
		  colors:['#8d8d8d','#58bf12'],
		  fontName: 'Futura-Heavy',
		  is3D: false,
		  width: 300,
		  tooltip: {textStyle: {color: '#8d8d8d', 
		  						fontName: 'Futura-Heavy',
								fontSize: 12}, 
		  			showColorCode: true},
		  legend: {textStyle:{color: '#8d8d8d'},
		  		   alignment: 'center',
				   position: 'none'},
		  pieSliceBorderColor: 'white',
		  pieSliceText: 'none'
		  /*pieSliceTextStyle: {color: '#ffffff',
		  					  fontName: 'Futura-Heavy',
		  					  fontSize: 1},
		  slices: [{}, {textStyle: {fontSize: 20}}]*/
		  
        };

		
		if (typeof opts != 'undefined') {
			$.each(opts, function(i, v) {
				options[i] = v;
			})
		}
        var chart = new google.visualization.PieChart(document.getElementById(eleID));
        chart.draw(data, options);
		
		/*
		var ele = $('#' + eleID).find('svg').children('g').children('text');
		
		if (parseInt(ele.text(),10) < 50) {
			
			var left = ele.attr('x') - 18;
			ele.attr('x', left);
		}
		*/
		
}


/**
 * ajax to retrieve ratings and then create chart
 
function getRatingsForChart(userID, sportID, rating)
{
	var options = {userID: userID,
				   sportID: sportID,
				   rating: rating};
	$.ajax({
		url: '/ajax/get-ratings-for-chart',
		type: 'POST',
		data: {options: options},
		success: function(data) {
			alert(data);
		}
	})
}*/

// bug fix to allow callback in document.ready "google.load(..." line
function createInitialChart()
{ 
	//data = new google.visualization.DataTable();
	

	/*
	var tempArray = new Array();
	var sport = 'basketball';
	
	for (i = 0; i < avgStats[sport].length; i++) {
		if (i == 0) {
			tempArray[i] = avgStats[sport][i];
		} else {

				
			tempArray[i] = [avgStats[sport][i][0], avgStats[sport][i][1]];
		}
	}
	
	*/
	

	$.each(sports, function(sport, array) {
		
		chart = new google.visualization.LineChart(document.getElementById('rating-avgSkillChart-' + sport));
		
		var listener = google.visualization.events.addListener(chart, 'ready',
						  function() {
						   //google.visualization.events.removeListener(listener)
						   $('#rating-avgSkillChart-' + sport).parents('.rating-section-container.hidden').removeClass('shown');
						   $('#rating-' + sport + '-container.hidden').removeClass('shown');

						  });
		
		if (typeof avgStats[sport] != 'undefined') {
			createScatterChart(avgStats[sport], true);
		}
		
		createPieChart(giveStats[sport], 'rating-giveStats-' + sport);
		
		createPieChart(decisiveness[sport], 'rating-decisiveness-' + sport);
		
	})
	
	
}

function clearData() {
	
	if (data.getNumberOfRows() > 0) {
		var numRows = data.getNumberOfRows()
		data.removeRows(0, (numRows))
	}
	
}

/**
 * create line chart
 * @params (initial => inital load of chart? (boolean),
 *			animation => (optional) duration for animation transition)
 */
function createScatterChart(dataArray, initial, animation)
{
	//clearData();
	
	var data = new google.visualization.DataTable();

	for (i = 0; i < dataArray.length; i++) {
		if (i == 0) {
			// First array, columns
			if (initial) {
				data.addColumn('date', dataArray[i][0]);
				data.addColumn('number', dataArray[i][1]);
				data.addColumn('number', dataArray[i][2]);
				data.addColumn('number', dataArray[i][3]);
			
			} else {
				data.removeColumn(1);
				data.addColumn('number', dataArray[i][1]);
			}
			
		} else {
			// Not first array, rows
			data.addRow(dataArray[i]);
			
		}
	}
	
	if (typeof animation == 'undefined') {
		animation = 600;
	}
		
    	//data = google.visualization.arrayToDataTable(dataArray);
		var min = new Date();
		min.setMonth(min.getMonth() - 3);
		
		var max = new Date();
		max.setDate(max.getDate() + 1);
		
        var options = {
		  backgroundColor: '#fff',
		  chartArea:{left:50,top:30,width:"100%",height:"80%"},
		  interpolateNulls: true,
		  colors:['#58bf12', '#d0d0d0', '#8d8d8d'],
		  fontName: 'Futura-Heavy',
		  hAxis: {baselineColor: '#bbb',
		  	      minorGridlines: null,
				  gridlines: {count: 0,
				  			  color: '#fff'},
		  		  textStyle: {color: '#bbb',
				  			  fontSize: 14},
				  slantedText: false,
				  slantedTextAngle: 30,
				  viewWindowMode: 'explicit',
				  viewWindow: {max: max,
				  			   min: min
							   },
				  format: 'MMM'
				  },
		  lineWidth: 3,
		  pointSize: 4,
		  animation:{duration: animation,
					 easing: 'out'},
		  tooltip: {textStyle: {color: '#8d8d8d', 
		  						fontName: 'Futura-Heavy'}, 
		  			showColorCode: false
					},
		  legend: {position: 'top',
		  		   alignment: 'start',
		  		   textStyle: {color: '#8d8d8d'}},
		  vAxis: {gridlines: {color: '#e9e9e9',
		  					  count: 6},
				  textStyle: {color: '#ccc',
				  			  fontSize: 14},
				  baselineColor: '#ccc',
				  maxValue: 10,
				  minValue: 0
				  }
		  
        };

        //chart = new google.visualization.LineChart(document.getElementById('chart')); 
        chart.draw(data, options);
		/*
		google.visualization.events.addListener(chart, 'select', function() {
          // grab a few details before redirecting
          	var selection = chart.getSelection();
          	var row = selection[0].row;
          	var col = selection[0].column;
			
			var ele = $('.rating-container:eq(' + row + ')');
			
			$('body').animate({'scrollTop': ele.offset().top}, 600);
			
			$('.rating-container').removeClass('light-back');
			ele.addClass('light-back');
          
        });
		*/
}


/**
 * set width of rating back
 */
function setRatingWidth(event, ele)
{
	var bar = ele.children('.rating-star-back');
	var parentOffset = ele.parent().offset(); 
	var eleWidth = ele.width()
	
	var width = event.pageX - parentOffset.left;
	
	width = roundWidthFull(width, eleWidth);
	
	bar.css('width', width);
	
	return width;
}


/**
 * round width of rating star to halves
 */
function roundWidth(width, parentWidth) 
{
	var percentage = width/parentWidth;
	var roundedPercentage = (Math.round(percentage * 10))/10;
	
	return roundedPercentage * parentWidth;
	
}

/**
 * round width of rating star to full
 */
function roundWidthFull(width, parentWidth) 
{
	var percentage = width/parentWidth;
	
	var roundedPercentage = (Math.round(percentage * 5))/5;
	
	return roundedPercentage * parentWidth;
	
}
	

