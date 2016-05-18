<?php

/**
 * Create reports about competence
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

global $CFG;
global $USER;
global $DB;

//require_once('../model/skill_course.php');
//require_once('../model/skill.php');
//require_once('../model/subdimension.php');
//require_once('../model/ws_evalcomix_client.php');
require_once('lib.php');
//$skill = skill::fetch(array('id'=>7));
//print_r($skill->get_connected_skills(2));
//$arr_objects = report_competence::get_data_report_student(2,3);
//print_r($arr_objects);
//$subdimension_grades = ws_evalcomix_client::get_subdimensions_grades($arr_objects);
//print_r($subdimension_grades);
//$skillgrades = report_competence::get_competence_grade(2, $arr_objects, true);
//print_r($skillgrades);
//$skillgrades = report_competence::get_competence_grade(2, $arr_objects, false);
//print_r($skillgrades);
//$skill_course = skill_course::fetch(array('skillid'=>15, 'courseid'=>2));
//print_r($skill_course->get_connected_abilities());
//$arr_skill = skill_course::get_skills_by_courseid(2);
//$skill = skill::fetch(array('id'=>26));
//$subdimensions = $skill->get_subdimensions_connected(4);
//foreach($subdimensions as $subdimension){
//	echo $subdimension->get_id();
//}exit;
//$arr_activities = report_competence::get_activities_connected($skill, 2);

// Parameteres received and checking
$courseid = required_param('courseid', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Check if there is an EvalCOMIX_MD instance in this course
$params = array('blockname'=>'evalcomix', 'parentcontextid'=>(int)$context->id);
if(!$DB->get_record('block_instances',$params)){
	print_error(get_string('notinstance', 'block_gescompeval_md'));
}

// Get users from the course
$students = get_role_users(5 , $context);

// Checking access
require_login($course);

// Set PAGE values
/*Cambio añadido por Daniel Cabeza: $param por $params*/
$PAGE->set_url(new moodle_url('/blocks/gescompeval_md/subdimensions/manage.php', $params));
/*Fin del Cambio*/
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('gescompeval');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add('gescompeval');
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/gescompeval_md/js/jquery-1.10.2.min.js'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/gescompeval_md/js/chosen.min.css'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/gescompeval_md/js/chosen.jquery.min.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/gescompeval_md/js/google.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/gescompeval_md/js/graphic.js'));

// Set moodle urls
$param = array('courseid' => $courseid);
$compage = new moodle_url('/blocks/gescompeval_md/competencies/manage.php', $param);
$subpage = new moodle_url('/blocks/gescompeval_md/subdimensions/selectsub.php', $param);

// Print UI
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('getreports', 'block_gescompeval_md'));

// Checking access to display the buttons to go to other gescompeval options
if(is_siteadmin($USER) || has_capability('block/evalcomix:edit',$context)){
	?>
	<center>
		<div style="padding:0.9em">
			<form id="gotocompetenciesform" method="post" action="<?php echo $compage ?>">
	    		<input type="submit" value="<?php echo get_string('managementcompetencies', 'block_gescompeval_md'); ?>" />
	    	</form>
	    </div>
	    <div style="padding-bottom:1.1em">
	    	<form id="gotosubdimensionsform" method="post" action="<?php echo $subpage ?>">
	    		<input type="submit" value="<?php echo get_string('managementsubdimensions', 'block_gescompeval_md'); ?>" />
	    	</form>
	    </div>
	</center>
	<?php
}

include('views/selectparameters.php');

// Print graphic div
echo '
	<center>
		<div id="graphic_div">
		<!--Div that will hold the bar chart-->
		<div id="chart_div"></div>
		</div>
	</center>';

echo $OUTPUT->footer();