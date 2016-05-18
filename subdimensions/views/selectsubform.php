<?php

$nextpage = new moodle_url('/blocks/gescompeval_md/subdimensions/manage.php', $param);

// Get course's tools
$tools = evalcomix_tool::get_tools($courseid);

// Create subdimension selector
$subdimensionselector = new subdimension_selector('subdimensionselect', $options);
$subdimensionselector->courseid = $courseid;

?>

<!-- Change the options of the select of subdimensions according to the selected tool -->
<script src="../js/jquery-1.10.2.min.js"></script>

<script type="text/javascript">
$(document).ready(function(){
  	$("#selectinstrument").change(function(){
  	  	// Empty the connected competence div
  		//$("#connected_competence").html('');

		var id = $(this).val();
		if(id != ''){
			$.ajax({
		        type: "POST",
		        url: "get_subdimensions.php",
		        data: "id="+ id,
		        success: function(option){
		           $("#subdimensionselect").html(option);
		        }
	      	});
	    	return false;
		}
		else{
			$("#subdimensionselect").html('<optgroup label="None"></optgroup>');
		}
  	});

  	$('#divselectsub').on('submit','#selectsubform',function(event){
  		if($("#subdimensionselect").val() == null){
  	        event.preventDefault();
  	    }
  		else{
  	  		// Get the names of dimension and subdimension to post them into hidden inputs
  			var dimname=$('#subdimensionselect :selected').parent().attr('label');
  			var subname=$('#subdimensionselect :selected').text();
  			if(dimname){
				$("#dimnamehidden").val(htmlspecialchars(dimname));
			}
			else{
				$("#dimnamehidden").val('dim');
			}
  			$("#subnamehidden").val(htmlspecialchars(subname));
  		}
  	});

  	$("#subdimensionselect").change(function(){
  		// Empty the connected skills div
  		$("#connected_skills").html('');

  	  	var subdimensionid = $("#subdimensionselect").val();
  		var courseid = <?php echo $courseid; ?>;
  		var post_values = { "courseid" : courseid, "subdimensionid" : subdimensionid};
  		$.ajax({
  	        type: "POST",
  	        url: "get_competence.php",
  	        data: post_values,
  	        success: function(competence){
  	           $("#connected_skills").html(competence);
  	        }
  	  	});

		return false;
  	});

  	function htmlspecialchars(string){
  	  	return $('<span>').text(string).html();
  	}

});
</script>

<!-- Display the selects -->
<div id="divselectsub">
<form id="selectsubform" method="post" action="<?php echo $nextpage ?>">
  	<input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
	<center>
		<fieldset style="border:1px solid #c3c3c3; width:35%; padding:0.3em">
			<legend style="color:#333333;text-align:left"><?php echo get_string('evxtools', 'block_gescompeval_md'); ?></legend>
				<div>
					<select id="selectinstrument" name="selectinstrument">
					<option value=""><?php echo get_string('selecttool', 'block_gescompeval_md'); ?></option>
					<?php
					if ($tools){
						foreach($tools as $key => $value){
							echo '<option value="'.$key.'">'.$value.'</option>';
						}
					}
					?>
					</select>
				</div>
		</fieldset>
	</center>

	<table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    	<tr>
      		<td id="existingcell">
          		<p><label for="subdimensionselect"><?php echo get_string('subdimensionsbelonging', 'block_gescompeval_md'); ?></label></p>
          		<?php $subdimensionselector->display(); ?>
      		</td>
    	</tr>
    	<tr>
      		<td id="buttonscell">
      	  		<center><div id="submitbutton">
      	  	  		<input type="submit" value="<?php echo get_string('selectsubsubmit', 'block_gescompeval_md'); ?>" />
      	    	</div></center>
      		</td>
    	</tr>
 	</table>

 	<input type="hidden" id="dimnamehidden" name="dimnamehidden" value="" />
 	<input type="hidden" id="subnamehidden" name="subnamehidden" value="" />

</form>
</div>

<div>
<center>
	<fieldset style="border:1px solid #c3c3c3; width:85%; padding:0.3em">
	<legend style="color:#333333;text-align:left"><?php echo get_string('competenciesincludedsub', 'block_gescompeval_md'); ?></legend>
		<div id="connected_skills"></div>
	</fieldset>
</center>
</div>
