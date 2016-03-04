// rating.js
var ratingClicked;
var chartData = new Object();
var data;
var lineChart;

$(function()
{
	
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
   
	if ($('#chart').length > 0) {
		// Only load charts on rating page
		
		google.load("visualization", "1", {"callback" : createInitialChart,
										   "packages" :["corechart"]});
	}
	
	
	
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
	chart = new google.visualization.ScatterChart(document.getElementById('chart')); 
	data = new google.visualization.DataTable();
	
	
	var tempArray = new Array();
	
	for (i = 0; i < chartData['overall'].length; i++) {
		if (i == 0) {
			tempArray[i] = chartData['overall'][i];
		} else {
			var value = null;
			if (chartData['overall'][i][1] !== null) {
				value = 60;
			}
				
			tempArray[i] = [chartData['overall'][i][0], value];
		}
	}
	
	var listener = google.visualization.events.addListener(chart, 'ready',
						  function() {
						   google.visualization.events.removeListener(listener)
						   setTimeout(function() {
							   createScatterChart(chartData['overall'], false, 1000)
						   }, 100);
						   
						  });
	
	createScatterChart(tempArray, true);

	
	
	
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
	
	clearData();

	for (i = 0; i < dataArray.length; i++) {
		if (i == 0) {
			// First array, columns
			if (initial) {
				data.addColumn('date', dataArray[i][0]);
				data.addColumn('number', dataArray[i][1]);
			
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
		min.setMonth(min.getMonth() - 6);
		
		var max = new Date();
		max.setDate(max.getDate() + 7);
		
        var options = {
		  backgroundColor: '#222',
		  chartArea:{left:50,top:15,width:"100%",height:"80%"},
		  colors:['#58bf12'],
		  fontName: 'Futura-Heavy',
		  hAxis: {baselineColor: '#3f3f3f',
		  	      minorGridlines: null,
				  gridlines: {count: 0,
				  			  color: '#222'},
		  		  textStyle: {color: '#666',
				  			  fontSize: 14},
				  slantedText: true,
				  slantedTextAngle: 30,
				  viewWindowMode: 'explicit',
				  viewWindow: {max: max,
				  			   min: min
							   },
				  format: 'MMM'
				  },
		  pointSize: 6,
		  animation:{duration: animation,
					 easing: 'out'},
		  tooltip: {textStyle: {color: '#58bf12', 
		  						fontName: 'Futura-Heavy'}, 
		  			showColorCode: false
					},
		  legend: {position: 'none'},
		  vAxis: {gridlines: {color: '#3f3f3f'},
		  		  minorGridlines: {color:'#3f3f3f',
		  					  	   count: 0},
		  		  textStyle: {color: '#666',
				  			  fontSize: 14},
				  baselineColor: '#3f3f3f',
				  maxValue: 100,
				  minValue: 60
				  },
		  
        };

        //chart = new google.visualization.LineChart(document.getElementById('chart')); 
        chart.draw(data, options);
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
	
	width = roundWidth(width, eleWidth);
	
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
	

