<?php

/**
 * Adding/Removing competencies/results from courses UI
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/lib.php');
require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');

// Parameteres received and checking
$courseid = required_param('courseid', PARAM_INT); // course id
$toolid = required_param('selectinstrument', PARAM_INT); // evalcomix tool id
$subid = required_param('subdimensionselect', PARAM_TEXT); // subdimension tool id
$dimname = required_param('dimnamehidden', PARAM_TEXT); // dimension name
$subname = required_param('subnamehidden', PARAM_TEXT); // subdimension name
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$tool = evalcomix_tool::fetch(array('id'=>$toolid));
if (!$tool){
	print_error('EvalCOMIX tool doesn\'t exist');
}

// Checking access
require_login($course);
global $USER;
if(!is_siteadmin($USER) && !has_capability('block/evalcomix:edit',$context)){
	print_error(get_string('notcapabilities', 'block_gescompeval_md'));
}

// Set PAGE values
$param = array('courseid' => $course->id);
$PAGE->set_url(new moodle_url('/blocks/gescompeval_md/subdimensions/manage.php', $param));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('gescompeval');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add('gescompeval');

$backpage = new moodle_url('/blocks/gescompeval_md/subdimensions/selectsub.php', $param);

// Create the user selector objects
$options = array('accesscontext' => $context);
$currentcompetenceselector = new current_competence_selector('addselect', $options, $courseid, $subid);
$subdimensioncompetenceselector = new subdimension_competence_selector('removeselect', $options, $courseid, $subid);
$currentcompetenceselector->clear_exclusions();

// Process add
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
	$elementstoassign = $currentcompetenceselector->get_selected_users();
	if (!empty($elementstoassign)) {
		foreach($elementstoassign as $add_element_id) {
			$subdimensioncompetenceselector->connect_competence($add_element_id, $toolid);
		}

		$currentcompetenceselector->invalidate_selected_users();
		$subdimensioncompetenceselector->invalidate_selected_users();
	}
}

// Process remove
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
	$elementstoassign = $subdimensioncompetenceselector->get_selected_users();
	if (!empty($elementstoassign)) {
		foreach($elementstoassign as $rem_element_id) {
			$currentcompetenceselector->remove_skill($rem_element_id);
		}

		$currentcompetenceselector->invalidate_selected_users();
		$subdimensioncompetenceselector->invalidate_selected_users();
	}
}


// Print UI
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managementsubdimensions', 'block_gescompeval_md'));
?>
<center>
	<fieldset style="border:1px solid #c3c3c3; width:55%; padding:0.3em">
		<legend style="color:#333333;text-align:left"><?php echo $tool->title; ?></legend><br />
			<?php
			// Check the type of the tool
			if($tool->type == 'differential'){
				echo '<p>'.get_string('attribute', 'block_gescompeval_md') . $subname.'</p>';
			}
			elseif($tool->type == 'mixed'){
				$toolname = explode(' > ', $dimname);
				echo '<p>'.get_string('tooldimension', 'block_gescompeval_md') . $toolname[0].'</p>';
				if($toolname[1] != ''){
					echo '<p>'.get_string('dimension', 'block_gescompeval_md') .$toolname[1].'</p>';
				}
				echo '<p>'.get_string('subdimension', 'block_gescompeval_md') . $subname.'</p>';
			}
			else{
				echo '<p>'.get_string('dimension', 'block_gescompeval_md') . $dimname.'</p>';
				echo '<p>'.get_string('subdimension', 'block_gescompeval_md') . $subname.'</p>';
			}
			?>
			<form id="backform" method="post" action="<?php echo $backpage ?>">
	      	  <div id="backbutton">
	      	  	<input type="submit" value="<?php echo get_string('selectsubback', 'block_gescompeval_md'); ?>" />
	      	  </div>
      	    </form>
	</fieldset>
</center>

<div>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>">
	<input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  	<table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    	<tr>
      		<td id="existingcell">
          		<p><label for="removeselect"><?php echo get_string('competenciesincludedsub', 'block_gescompeval_md'); ?></label></p>
          		<?php $subdimensioncompetenceselector->display() ?>
      		</td>
      		<td id="buttonscell">
          		<div id="addcontrols">
              		<input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
          		</div>
          		<div id="removecontrols">
              		<input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
          		</div>
      		</td>
      		<td id="potentialcell">
          		<p><label for="addselect"><?php echo get_string('competenciesnotincludedsub', 'block_gescompeval_md'); ?></label></p>
          		<?php echo $currentcompetenceselector->display(); ?>
      		</td>
   		</tr>
  	</table>

  	<input type="hidden" id="selectinstrument" name="selectinstrument" value=<?php echo $toolid; ?> />
  	<input type="hidden" id="subdimensionselect" name="subdimensionselect" value="<?php echo $subid; ?>" />
  	<input type="hidden" id="dimnamehidden" name="dimnamehidden" value="<?php echo htmlspecialchars($dimname, ENT_QUOTES, 'UTF-8'); ?>" />
  	<input type="hidden" id="subnamehidden" name="subnamehidden" value="<?php echo htmlspecialchars($subname, ENT_QUOTES, 'UTF-8'); ?>" />

</form>
</div>

<?php
echo $OUTPUT->footer();