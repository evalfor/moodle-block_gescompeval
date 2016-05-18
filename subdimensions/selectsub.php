<?php

/**
 * Adding/Removing connections between competencies/results and subdimensions
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/lib.php');

// Check if EvalCOMIX_MD is installed
if(!isset($CFG->evalcomix_serverurl)){
	print_error('EvalCOMIX is not configured');
}

// Parameteres received and checking
$courseid = required_param('courseid', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Check if there is an EvalCOMIX_MD instance in this course
$params = array('blockname'=>'evalcomix', 'parentcontextid'=>(int)$context->id);
if(!$DB->get_record('block_instances',$params)){
	print_error(get_string('notinstance', 'block_gescompeval_md'));
}
require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');

// Checking access
require_login($course);
global $USER;
if(!is_siteadmin($USER) && !has_capability('block/evalcomix:edit',$context)){
	print_error(get_string('notcapabilities', 'block_gescompeval_md'));
}

// Set PAGE values
$param = array('courseid' => $course->id);
$PAGE->set_url(new moodle_url('/blocks/gescompeval_md/subdimensions/selectsub.php', $param));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('gescompeval');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add('gescompeval');

// Set moodle urls
$param = array('courseid' => $courseid);
$commanagement_page = new moodle_url('/blocks/gescompeval_md/competencies/manage.php', $param);
$reports_page = new moodle_url('/blocks/gescompeval_md/reports/index.php', $param);

// Print UI
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectsub', 'block_gescompeval_md'));
?>

<center>
	<div style="padding:0.9em">
		<form id="gotocompetenciesform" method="post" action="<?php echo $commanagement_page ?>">
    		<input type="submit" value="<?php echo get_string('managementcompetencies', 'block_gescompeval_md'); ?>" />
    	</form>
    </div>
    <div style="padding-bottom:1.1em">
    	<form id="gotoreportsform" method="post" action="<?php echo $reports_page ?>">
    		<input type="submit" value="<?php echo get_string('getreports', 'block_gescompeval_md'); ?>" />
    	</form>
    </div>
</center>

<?php

include('views/selectsubform.php');

echo $OUTPUT->footer();

// Check if there are subdimenions connected with the course which belong to a tool that has been deleted
// If so, delete the connection
require_once($CFG->dirroot.'/blocks/gescompeval_md/model/subdimension.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/model/skill_course_sub.php');
require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');

$tools = evalcomix_tool::get_tools($course->id);
if($subdimensions = subdimension::get_subdimensions_by_course($course->id)){

	foreach($subdimensions as $subdimension){
		// If the tool doesn't exist, delete all the relations of the subdimension with any skill_course
		if(!array_key_exists($subdimension->get_toolid(), $tools)){

			// Get every skill_course_sub which the subdimensions is in and delete it
			$params = array('subdimensionid' => $subdimension->get_id());
			if($arr_skill_course_sub = skill_course_sub::fetch_all($params)){

				foreach($arr_skill_course_sub as $skill_course_sub){
					$skill_course_sub->delete();
				}
			}
		}
	}
}



