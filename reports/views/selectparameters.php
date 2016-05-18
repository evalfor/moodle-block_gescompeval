
<script src="../js/jquery-1.10.2.min.js"></script>
<link href="../css/chosen.min.css" type="text/css" rel="stylesheet">
<script src="../js/chosen.jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	var courseid = <?php echo $course->id;?>;
	var textArray = new Array();
	textArray[0] = '<?php echo get_string('competencereport', 'block_gescompeval_md').'\n'; ?>';
	textArray[1] = '<?php echo get_string('competencies', 'block_gescompeval_md'); ?>';
	textArray[2] = '<?php echo get_string('learningoutcomes', 'block_gescompeval_md'); ?>';

	$('#reportselect').change(function(){
		var value = $('#reportselect').val();
		var char_data;
		// Report about all students
		if(value == 1){
			$('#studentselect option:eq(0)').prop('selected', true);
			// Update the chosen select
			$('#studentselect').trigger("chosen:updated");

			$('#studentselectdiv').hide();
			$('#evidences_checkbox_id').attr('checked', false);
			//$('#graphic_div').load('graphic.html', function() { drawChart(); })
			drawChart(courseid, '', $('#checkbox_id').prop('checked'), textArray, $('#evidences_checkbox_id').prop('checked'));
			//$('#chart_div').show();
		}
		// Report about one student
		else if(value == 2){
			$('#studentselectdiv').show();
			$('#chart_div').empty();
			//$('#chart_div').hide();
		}
		// Nothing selected
		else{
			$('#studentselect option:eq(0)').prop('selected', true);
			// Update the chosen select
			$('#studentselect').trigger("chosen:updated");

			$('#studentselectdiv').hide();
			$('#evidences_checkbox_id').attr('checked', false);
			$('#chart_div').empty();
			//$('#chart_div').hide();
		}
	});

	function change_checkbox(){
		var value_select_report = $('#reportselect').val();
		var studentid = $('#studentselect').val();

		if(value_select_report == 2 && studentid != ""){
			drawChart(courseid, studentid, $('#checkbox_id').prop('checked'), textArray, $('#evidences_checkbox_id').prop('checked'));
			//$('#chart_div').show();
		}
		else{
			$('#reportselect').change();
		}
	}

	$('#checkbox_id').change(function(){
		change_checkbox()
	});

	$('#evidences_checkbox_id').change(function(){
		change_checkbox()
	});

	$('#studentselect').change(function(){
		var studentid = $('#studentselect').val();
		if(studentid == ""){
			//$('#chart_div').hide();
			$('#chart_div').empty();
			//clearChart();
		}
		else{
			drawChart(courseid, studentid, $('#checkbox_id').prop('checked'), textArray, $('#evidences_checkbox_id').prop('checked'));
			//$('#chart_div').show();
		}
	});

	// Activates the chosen-select and hide the div where the student selector is
	$('#studentselectdiv').show();
	$('.chosen-select').chosen();
	$('#studentselectdiv').hide();
	$('#evidences_checkbox_id').attr('checked', false);

	// With this, the droplist won't cut if its height is bigger than the div which contains it
	$(".chosen-drop").show(0, function () {
		   $(this).parents("div").css("overflow", "visible");
	});

});
</script>

<!-- Display the parameteres selections -->
<div>
<form id="selectparametersform" method="post" action="">
<input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
<center>
	<fieldset style="border:1px solid #c3c3c3; width:35%; padding:0.3em">
	<legend style="color:#333333;text-align:left"><?php echo get_string('selectparameters', 'block_gescompeval_md'); ?></legend>
		<div>
			<select id="reportselect" name="reportselect" onChange="change_show_select_students(this.value)">
				<option value=""><?php echo get_string('selectreporttype', 'block_gescompeval_md'); ?></option>
				<option value="1"><?php echo get_string('allstudents', 'block_gescompeval_md'); ?></option>
				<option value="2"><?php echo get_string('onestudent', 'block_gescompeval_md'); ?></option>
			</select>
		</div>
		<div id="studentselectdiv" style="padding:0.3em;" hidden="true">
			<select id="studentselect" name="studentselect" class="chosen-select" style="width:97%;">
				<option value=""><?php echo get_string('selectstudent', 'block_gescompeval_md'); ?></option>
					<?php
					if ($students){
						foreach($students as $student){
							echo '<option value="'.$student->id.'">'.$student->firstname.' '.$student->lastname.'</option>';
						}
					}
					?>
			</select>
			<!-- Comentario añadido por Daniel Cabeza 
			<div id="evidencesdiv" style="padding:0.3em;">
				<?php echo $OUTPUT->help_icon('helpevidence', 'block_gescompeval_md'); ?>
				<input type="checkbox" name="evidences_checkbox" id="evidences_checkbox_id">
				<label for="evidences_checkboxid"><?php echo get_string('showevidences', 'block_gescompeval_md'); ?></label>
			</div> -->
		</div>
	</fieldset>
</center>
</form>
</div>