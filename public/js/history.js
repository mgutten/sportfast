// JavaScript Document
var chartData;
var pieChartData;
var columnChart;
var data;

$(function()
{
	$('.history-option').click(function()
	{
		if ($(this).is('.green-bold')) {
			// Already selected
			return false;
		}
		
		$('.history-option.green-bold').removeClass('largest-text green-bold')
									   .addClass('light');
											
		$(this).addClass('largest-text green-bold')
			   .removeClass('light');
		
		/*	   
		var value = $(this).text().toLowerCase();
		
		$('.chart').hide();
		$('.' + value).show();
		*/
		var index = $(this).index('.history-option');
		var marginLeft = index * $('#history-charts-container').width();
		
		$('#history-charts-inner-container').stop().animate({'margin-left': -marginLeft}, 400);
			   
		
	})
	
	google.load("visualization", "1", {callback: createInitialChart,
									   packages: ["corechart"]});

})
	  
// bug fix to allow callback in document.ready "google.load(..." line
function createInitialChart()
{
	createPieChart();
	
	columnChart = new google.visualization.ColumnChart(document.getElementById('history-chart')); 
	data = new google.visualization.DataTable();
	
	
	var tempArray = new Array();
	
	for (i = 0; i < chartData.length; i++) {
		if (i == 0) {
			tempArray[i] = chartData[i];
		} else {
				
			tempArray[i] = [chartData[i][0], 0,0];
		}
	}
	
	var listener = google.visualization.events.addListener(columnChart, 'ready',
						  function() {
							 
						   google.visualization.events.removeListener(listener)
						   setTimeout(function() {
							   createColumnChart(chartData, false, 1000)
						   }, 200);
						   
						  });
	
	createColumnChart(tempArray, true);
	//createColumnChart(chartData);
}

function clearData() {
	
	if (data.getNumberOfRows() > 0) {
		var numRows = data.getNumberOfRows()
		data.removeRows(0, (numRows))
	}
	
}


function createColumnChart(dataArray, initial, animation)
{
	clearData();
	
	for (i = 0; i < dataArray.length; i++) {
		if (i == 0) {
			// First array, columns
			if (initial) {
				data.addColumn('string', dataArray[i][0]);
				data.addColumn('number', dataArray[i][1]);
				data.addColumn('number', dataArray[i][2]);
			}
			
		} else {
			// Not first array, rows
			data.addRow(dataArray[i]);
			
		}
	}
	
	if (typeof animation == 'undefined') {
		animation = 600;
	}

   		//var data = google.visualization.arrayToDataTable(dataArray);
		
		/* dark back
        var options = {
		  backgroundColor: '#222',
		  chartArea:{left:50,top:30,width:"100%",height:"70%"},
		  colors:['#58bf12','#B30000'],
		  fontName: 'Futura-Heavy',
		  hAxis: {minorGridlines: {color:'#000'},
		  		  textStyle: {color: '#666',
				  			  fontSize: 13},
				  slantedText: true,
				  slantedTextAngle: 30,
				  baselineColor: '#3f3f3f',
				  gridlines: {color: '#2f2f2f',
				  			  count: 0},
				  format:'MMM d'},
		  lineWidth: 4,
		  pointSize: 8,
		  isStacked: true,
		  animation:{duration: animation,
					 easing: 'out'},
		  tooltip: {textStyle: {color: '#58bf12', 
		  						fontName: 'Futura-Book',
								fontSize: 12}, 
		  			showColorCode: false},
		  legend: {position: 'none'},
		  vAxis: {gridlines: {color: '#2f2f2f'},
		  		  minorGridlines: {color:'#3f3f3f',
		  					  	   count: 0},
		  		  textStyle: {color: '#666',
				  			  fontSize: 13},
				  baselineColor: '#3f3f3f',
				  minValue: 0,
				  maxValue: 20
				  },
		  
        };*/
		
		var options = {
		  //backgroundColor: '#222',
		  chartArea:{left:50,top:30,width:"100%",height:"70%"},
		  colors:['#58bf12','#B30000'],
		  fontName: 'Futura-Heavy',
		  hAxis: {minorGridlines: {color:'#000'},
		  		  textStyle: {color: '#bbb',
				  			  fontSize: 13},
				  slantedText: true,
				  slantedTextAngle: 30,
				  baselineColor: '#eee',
				  gridlines: {color: '#eee',
				  			  count: 0},
				  format:'MMM d'},
		  lineWidth: 4,
		  pointSize: 8,
		  isStacked: true,
		  animation:{duration: animation,
					 easing: 'out'},
		  tooltip: {textStyle: {color: '#8d8d8d', 
		  						fontName: 'Futura-Book',
								fontSize: 12}, 
		  			showColorCode: false},
		  legend: {position: 'none'},
		  vAxis: {gridlines: {color: '#fff'},
		  		  minorGridlines: {color:'#eee',
		  					  	   count: 0},
		  		  textStyle: {color: '#bbb',
				  			  fontSize: 13},
				  baselineColor: '#ddd',
				  minValue: 0,
				  maxValue: 20
				  },
		  
        };
		

        //var chart = new google.visualization.ColumnChart(document.getElementById('history-chart'));
        columnChart.draw(data, options);
}

function createPieChart()
{
        var data = google.visualization.arrayToDataTable(pieChartData);

        var options = {
		  chartArea:{left:50,top:0,width:"100%",height:"100%"},
		  //backgroundColor: '#222',
		  colors:['#58bf12','#B30000'],
		  fontName: 'Futura-Heavy',
		  hAxis: {minorGridlines: {color:'#000'},
		  		  textStyle: {color: '#666',
				  			  fontSize: 14},
				  slantedText: true,
				  slantedTextAngle: 30},
		  is3D: true,
		  tooltip: {textStyle: {color: '#58bf12', 
		  						fontName: 'Futura-Heavy'}, 
		  			showColorCode: false},
		  height: 250,
		  width: 679,
		  vAxis: {gridlines: {color: '#3f3f3f'},
		  		  minorGridlines: {color:'#3f3f3f',
		  					  	   count: 0},
		  		  textStyle: {color: '#666',
				  			  fontSize: 14},
				  baselineColor: '#3f3f3f',
				  minValue: 0
				  },
		  legend: {textStyle:{color: '#222'},
		  		   alignment: 'center'}
		  
        };

        var chart = new google.visualization.PieChart(document.getElementById('pie-chart'));
        chart.draw(data, options);
}
