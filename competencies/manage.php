<?php

/**
 * Adding/Removing competencies/results from courses UI
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/lib.php');

// Parameteres received and checking
$courseid = required_param('courseid', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
/*Cambio por Daniel Cabeza*/
$context = context_course::instance($course->id, MUST_EXIST);
//$context = get_context_instance(CONTEXT_COURSE, $course->id);
/*Fin del cambio*/

// Check if there is an EvalCOMIX_MD instance in this course
$params = array('blockname'=>'evalcomix', 'parentcontextid'=>(int)$context->id);
if(!$DB->get_record('block_instances',$params)){
	print_error(get_string('notinstance', 'block_gescompeval_md'));
}

// Checking access
require_login($course);
global $USER;
if(!is_siteadmin($USER) && !has_capability('block/evalcomix:edit',$context)){
	print_error(get_string('notcapabilities', 'block_gescompeval_md'));
}

// Set PAGE values
$PAGE->set_url(new moodle_url('/blocks/gescompeval_md/competencies/manage.php', array('courseid' => $course->id)));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('gescompeval');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add('gescompeval');

// Set moodle urls
$param = array('courseid' => $courseid);
$subpage = new moodle_url('/blocks/gescompeval_md/subdimensions/selectsub.php', $param);
$reports_page = new moodle_url('/blocks/gescompeval_md/reports/index.php', $param);

// Create the user selector objects
$options = array('accesscontext' => $context);
$potentialcompetenceselector = new potential_competence_selector('addselect', $options, $courseid);
$currentcompetenceselector = new current_competence_selector('removeselect', $options, $courseid);
$potentialcompetenceselector->clear_exclusions();

// Process add
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
	$elementstoassign = $potentialcompetenceselector->get_selected_users();
	if (!empty($elementstoassign)) {
		foreach($elementstoassign as $add_element_id) {
			$currentcompetenceselector->connect_competence($add_element_id);
		}

		$potentialcompetenceselector->invalidate_selected_users();
		$currentcompetenceselector->invalidate_selected_users();
	}
}

// Process remove
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
	$elementstoassign = $currentcompetenceselector->get_selected_users();
	if (!empty($elementstoassign)) {
		foreach($elementstoassign as $rem_element_id) {
			$potentialcompetenceselector->remove_skill($rem_element_id);
		}

		$potentialcompetenceselector->invalidate_selected_users();
		$currentcompetenceselector->invalidate_selected_users();
	}
}

// Print UI
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managementcompetencies', 'block_gescompeval_md'));
?>

<!-- Buttons go to -->
<center>
	<div style="padding:0.9em">
    	<form id="gotosubdimensionsform" method="post" action="<?php echo $subpage ?>">
    		<input type="submit" value="<?php echo get_string('managementsubdimensions', 'block_gescompeval_md'); ?>" />
    	</form>
    </div>
	<div style="padding-bottom:1.1em">
		<form id="gotoreportsform" method="post" action="<?php echo $reports_page ?>">
    		<input type="submit" value="<?php echo get_string('getreports', 'block_gescompeval_md'); ?>" />
    	</form>
    </div>
</center>


<!-- Assign form -->
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  <table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect"><?php echo get_string('competenciesincluded', 'block_gescompeval_md'); ?></label></p>
          <?php $currentcompetenceselector->display(); ?>
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
          <p><label for="addselect"><?php echo get_string('competenciesnotincluded', 'block_gescompeval_md'); ?></label></p>
          <?php echo $potentialcompetenceselector->display(); ?>
      </td>
    </tr>
  </table>
</div></form>

<?php
echo $OUTPUT->footer();