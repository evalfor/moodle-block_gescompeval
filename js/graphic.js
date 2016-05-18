// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

function drawChart(courseid, studentid, withrelations, textArray, withevidences) {

	// Create the data table.
    //var data = new google.visualization.DataTable();
    //data.addColumn('string', 'Valor');
    //data.addColumn('number', 'Puntuación');
    //data.addRows([
    //var data = google.visualization.arrayToDataTable([
	//  	['Competencies/Learning outcomes', 'Puntuación', { role: 'style' }],
    //  	['COM1: Competencia 1ppppppppppppppppppppppppppppppppppppppppppppppp', 100, 'silver'],
    //  	['COM2: Competencia 2', 25, 'color: gold'],
    //  	['COM3: Competencia 3', 90, 'color: green'],
    //  	['RES1: Resultado 1', 15, 'color: blue'],
    //  	['RES2: Resultado 2', 50, 'color: red'],
    //  	['RES3: Resultado 3', 70, 'color: yellow']
    //]);

	// Check if the relations between competencies and outcomes learnings must keep in mind
    if(withrelations) var check = 1;
    else var check = 0;
    
    // Check if the evidences must be shown
    if(withevidences) var evidences = 1;
    else var evidences = 0;

	var post_values = { "courseid" : courseid, "studentid" : studentid, 
			"withrelations" : check, "withevidences" : evidences, 
			"competencies_text" : textArray[1], "outcomes_text" : textArray[2]};

	// Get data by AJAX
    var json = $.ajax({
    				type: "POST",
					url: 'get_data_report.php',
					data: post_values,
					dataType: 'json',
					async: false
				}).responseText;

    var data = new google.visualization.DataTable(json);
    //var data = google.visualization.arrayToDataTable(json);

    //var data = google.visualization.arrayToDataTable([
    //    ['Competencies/Learning outcomes', 'Puntuación', { role: 'style' }],
        
    /*    if($dataChart){
	        foreach ($dataChart as $obj){
				echo "['".$obj->code."', ".$obj->grade.", '".$obj->colour."'],";
			}
		}*/
        
    //]);

   // var title = '<?php echo get_string('competencereport', 'block_gescompeval_md'); ?>';
    var options = {
    	title: textArray[0],
    	width: 1050,
        height: 550,
        bar: {groupWidth: '70%'},
        legend: { position: 'top' },
        hAxis: {minValue: '0', maxValue: '100'},
        //chartArea: {left:"17%",top:"15%",width:"78%",height:"70%"},
        chartArea: {left:"19%", width:"70%"},
        backgroundColor: {stroke: 'black', strokeWidth: 2},
        tooltip: {isHtml: true},
        colors: ['blue', 'green'],
        isStacked: true
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
    
    // To show the context menu
    google.visualization.events.addListener(chart, 'ready', function () {
        google.visualization.events.addListener(chart, 'rightclick', function () {
        	var selection = JSON.parse(chart.getState()).iconKeySettings;
        	alert(selection.length);
		});
    });
    
    chart.draw(data, options);
}