<?php

require_once($CFG->dirroot . '/blocks/evalcomix/classes/evalcomix_tasks.php');
require_once($CFG->dirroot . '/blocks/evalcomix/classes/evalcomix_modes.php');
require_once($CFG->dirroot . '/blocks/evalcomix/classes/evalcomix_tool.php');
require_once($CFG->dirroot . '/blocks/evalcomix/classes/evalcomix_assessments.php');
/*Comentario añadido por Daniel Cabeza
require_once($CFG->dirroot . '/blocks/evalcomix/evalchrome/evalcomix_evidences.php');
*/
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/subdimension.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/ws_evalcomix_client.php');

/**
 * Class for getting datas for a report of competence
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_competence{

	/**
	 * Return an HTML string with the information to display in the tooltip in the report
	 *
	 * @param stdClass $arr_competence -> It has competencies/outcomes included in the course
	 * @param stdClass $skill_stdClass
	 * @param int $grade
	 * @param int $courseid
	 * @param int $studentid
	 *
	 * @return array array id => name of activities
	 */
	public static function get_tooltip($arr_competence, $skill_stdClass, $grade, $courseid, $studentid){

		$htmltext = '<div style="text-align:left;"><p>
				<b>'.(string)$skill_stdClass->code.'</b>: '.
				(string)$skill_stdClass->shortdescription.'<br/>';

		// If the skill is a competency, show the type
		if((string)$skill_stdClass->type == 'competency'){
			$competencetype = (string)$skill_stdClass->competencetype;
			if($competencetype != ''){
				$htmltext .= get_string('competencetypereport', 'block_gescompeval_md').$competencetype.'<br/>';
			}
			else{
				$htmltext .= get_string('nocompetencetypereport', 'block_gescompeval_md').'<br/>';
			}
		}

		// If the skill has a longdescription, show it
		$longdescription = (string)$skill_stdClass->longdescription;
		if($longdescription != ''){
			$htmltext .= '<br/>'.$longdescription.'<br/>';
		}

		$htmltext .= '<br/>'.get_string('value', 'block_gescompeval_md').'<b>'.$grade.'</b></p>';

		// Get the competencies/outcomes connected with the skill in this course
		if($skill = skill::fetch(array('id'=> $skill_stdClass->id))){
			$connected_skills = $skill->get_connected_skills($courseid);

			if(count($connected_skills)>0){
				if($skill_stdClass->type == 'competency'){
					$htmltext .= '<p>'.get_string('outcomesconnected', 'block_gescompeval_md').'</p><ul>';
				}
				elseif($skill_stdClass->type == 'outcome'){
					$htmltext .= '<p>'.get_string('competenciesconnected', 'block_gescompeval_md').'</p><ul>';
				}

				// Get the information of the connected skills from Web service
				if($arr_connected_skills = skill::get_array_skill_information($connected_skills)){
					// Show every competency/outcome connected
					foreach($arr_connected_skills as $skill){
						$code = (string)$skill->code;
						$shortdescription = (string)$skill->shortdescription;
						$htmltext .= '<li>'.$code.': '.$shortdescription.'</li>';
					}
				}
				$htmltext .= '</ul>';
			}
			else{
				if($skill_stdClass->type == 'competency'){
					$htmltext .= '<p>'.get_string('nooutcomesconnected', 'block_gescompeval_md').'</p>';
				}
				elseif($skill_stdClass->type == 'outcome'){
					$htmltext .= '<p>'.get_string('nocompetenciesconnected', 'block_gescompeval_md').'</p>';
				}
			}
		}

		// Get the activities' names which competence is connected
		if($skill = skill::fetch(array('id' => $skill_stdClass->id))){
			$arr_activities = report_competence::get_activities_connected($skill, $courseid);

			if(count($arr_activities) > 0){
				if($skill_stdClass->type == 'competency'){
					$htmltext .= '<p>'.get_string('activitieslistofcompetency', 'block_gescompeval_md').'</p><ul>';
				}
				else{
					$htmltext .= '<p>'.get_string('activitieslistofresult', 'block_gescompeval_md').'</p><ul>';
				}

				// If there isn't a studentid, omit the evidences
				if($studentid == ''){
					foreach($arr_activities as $taskid => $activityname){
						$htmltext .= '<li>'.$activityname.'</li>';
					}
				}
				else{
					foreach($arr_activities as $taskid => $activityname){
						$htmltext .= '<li>'.$activityname.'</li>';
						$htmltext .= report_competence::get_evidences($skill, $studentid, $taskid, $courseid);
					}
				}

				$htmltext .= '</ul>';
			}
			else{
				if($skill_stdClass->type == 'competency'){
					$htmltext .= '<p>'.get_string('competencynotassessed', 'block_gescompeval_md').'</p>';
				}
				else{
					$htmltext .= '<p>'.get_string('resultnotassessed', 'block_gescompeval_md').'</p>';
				}
				$htmltext .= '<p></p>';
			}
		}

		$htmltext .= '</div>';

		return $htmltext;
	}

	/**
	 * Return an HTML string with the information to display in the tooltip in the report
	 *
	 * @param skill $skill
	 * @param int $studentid
	 * @param int $taskid
	 * @param int $courseid
	 *
	 * @return array array string => url of evidences in all assessments of the received task
	 */
	public static function get_evidences($skill, $studentid, $taskid, $courseid){

		$html = '';
		$evidence_flag = false;
		// To check that the urls don't repeat
		$urls = array();

		// Get the assessments for the student in the task
		$params = array('taskid'=>$taskid, 'studentid'=>$studentid);
		if($assessments = evalcomix_assessments::fetch_all($params)){

			// Get the subdimensions connected with the skill object
			$subdimensions = $skill->get_subdimensions_connected($courseid);

			$html .= '<ul><li type="circle"><u>'.get_string('evidences', 'block_gescompeval_md').'</u></li>';

			// Iterate over the assessments
			foreach($assessments as $assessment){

				// Get type of assessment
				//$mode = report_competence::get_type_of_assessment($studentid, $assessment->assessorid, $courseid);

				// For all the subdimensions of this assessment
				foreach($subdimensions as $subdimension){

					// Get the evidences of each assessment and subdimension
					$params = array('assessmentid'=>$assessment->id, 'evxsubid'=>$subdimension->get_evxsubid());
					if($evidences = evalcomix_evidences::fetch_all($params)){
						foreach($evidences as $evidence){
							$evidence_flag = true;
							// Check that the evidence hasn't been included
							if(!in_array($evidence->url, $urls)){
								//$html .= '<li type="circle"><a href=\''.$evidence->url.'\'>'.$evidence->url.'</a>('.$mode.')</li>';
								$html .= '<li type="circle"><a href=\''.$evidence->url.'\'>'.$evidence->url.'</a></li>';
								$urls[] = $evidence->url;
							}
						}
					}
				}
			}
			$html .= '</ul>';
		}

		if(!$evidence_flag){
			$html = '<ul><li type="circle">'.get_string('notevidences', 'block_gescompeval_md').'</li></ul>';
		}

		return $html;
	}

	/**
	 * Return an array with the id => name of activities related with a competence
	 *
	 * @param skill $skill
	 * @param int $courseid
	 * @return array array id => name of activities
	 */
	public static function get_activities_connected($skill, $courseid){

		global $DB;
		$arr_activities = array();

		// Get the subdimensions connected with the competence
		$arr_subdimensions = $skill->get_subdimensions_connected($courseid);

		// Get the activities connected with each tool through EvalCOMIX
		foreach($arr_subdimensions as $subdimension){

			$toolid = $subdimension->get_toolid();

			$params = array('toolid' => $toolid);
			if($arr_evalcomix_modes = evalcomix_modes::fetch_all($params)){

				// Get the evalcomix_tasks
				$arr_tasks_ids = array();
				foreach($arr_evalcomix_modes as $evalcomix_modes){

					// Add the id if it isn't in the array yet
					if(!in_array($evalcomix_modes->taskid, $arr_tasks_ids)){
						$arr_tasks_ids[] = $evalcomix_modes->taskid;
					}
				}

				// Get the id and titles of the activities
				foreach($arr_tasks_ids as $taskid){

					$params = array('id' => $taskid);
					if($task = evalcomix_tasks::fetch($params)){

						$cm = $DB->get_record('course_modules', array('id' => $task->instanceid));
						if($cm){
							$module = evalcomix_tasks::get_type_task($cm->id);
							if($task_moodle = $DB->get_record($module, array('id' => $cm->instance))){
								$arr_activities[$task->id] = $task_moodle->name;
							}
						}
					}
				}
			}
		}

		return $arr_activities;
	}

	/**
	 * Create an array of stdClass objects with toolid, subdimensionid and assessmentid
	 * of all assessments in the course. It only keeps in mind the assessment of the
	 * subdimensions connected with competencies/learnings outcomes.
	 *
	 * @static
	 *
	 * @param $courseid
	 *
	 * @return array array of stdClass instances
	 */
	public static function get_data_report_course($courseid){

		$arr_objects = array();

		// Get subdimensions connected with the course
		if($arr_subdimension = subdimension::get_subdimensions_by_course($courseid)){

			// Get tasks of the course
			if ($evxtasks = evalcomix_tasks::get_tasks_by_courseid($courseid)){
				foreach($evxtasks as $evxtask){

					// Get needed datas to create assessmentid
					$activity = $evxtask->instanceid;
					$module = evalcomix_tasks::get_type_task($activity);

					// Get assessments of each task
					$param = array('taskid' => $evxtask->id);
					if($evxassessments = evalcomix_assessments::fetch_all($param)){
						foreach($evxassessments as $evxassessment){

							//Get the assessments of every subdimension connected with the course
							$arr_aux = report_competence::get_subdimensions_assessments($courseid, $activity, $module, $evxtask, $evxassessment, $arr_subdimension);
							$arr_objects = array_merge($arr_objects, $arr_aux);
						}
					}
				}
			}
		}

		return $arr_objects;
	}

	/**
	 * Create an array of stdClass objects with toolid, subdimensionid and assessmentid
	 * of all assessments in the course. It only keeps in mind the assessment of the
	 * subdimensions connected with competencies/learnings outcomes and the assessments
	 * made for the received student
	 *
	 * @static
	 *
	 * @param $courseid
	 * @param $studentid
	 *
	 * @return array array of stdClass instances
	 */
	public static function get_data_report_student($courseid, $studentid = 0){

		$arr_objects = array();

		// Get subdimensions connected with the course
		if($arr_subdimension = subdimension::get_subdimensions_by_course($courseid)){

			// Get tasks of the course
			if ($evxtasks = evalcomix_tasks::get_tasks_by_courseid($courseid)){
				foreach($evxtasks as $evxtask){

					// Get needed datas to create assessmentid
					$activity = $evxtask->instanceid;
					$module = evalcomix_tasks::get_type_task($activity);

					// Get assessments of each task
					$param = array('taskid' => $evxtask->id);
					if($evxassessments = evalcomix_assessments::fetch_all($param)){
						foreach($evxassessments as $evxassessment){

							// If the student assessed is the student received
							if($studentid == $evxassessment->studentid){
								//Get the assessments of every subdimension connected with the course
								$arr_aux = report_competence::get_subdimensions_assessments($courseid, $activity, $module, $evxtask, $evxassessment, $arr_subdimension);
								$arr_objects = array_merge($arr_objects, $arr_aux);
							}
						}
					}
				}
			}
		}

		return $arr_objects;
	}

	/**
	 * Get arithmetic average between the grades of every skill
	 * @static
	 *
	 * @param $courseid
	 * @param $arr_objects
	 *
	 * @return array with the following structure: [id] => [grade]
	 */
	public static function get_competence_grade($courseid, $arr_objects){
		$skillgrades = array();

		// Get the belonging to subdimension grade
		if($subdimension_grades = ws_evalcomix_client::get_subdimensions_grades($arr_objects)){

			// Calculate mean average of each subdimension
			if($subdim_mean_grades = report_competence::get_mean_average_subdimensions($subdimension_grades)){
				//print_r($subdim_mean_grades);
				$ids = array();
				$times = array();
				$grades = array();

				// Get grades for each competences/learning outcome of the course
				foreach($subdim_mean_grades as $id => $grade){

					// Get subdimension object by fetch
					$param = array('id'=>$id);
					if($subdimension = subdimension::fetch($param)){

						// Get connected skills and plus the grade of this subdimension
						if($arr_skill_from_subdimension = $subdimension->get_skill_connected($courseid)){

							foreach($arr_skill_from_subdimension as $skill){
								$id = $skill->get_id();

								// If the skill isn't included in the course, skip it
								$params = array('skillid'=>$id, 'courseid'=>$courseid);
								if(skill_course::fetch($params)){
									if(in_array($id, $ids)){
										$grades[$id] += $grade;
										$times[$id]++;
									}
									else{
										$ids[] = $id;
										$grades[$id] = $grade;
										$times[$id] = 1;
									}
									//echo 'A la competencia '.$skill->get_id().' le he añadido '.
									//	$grade.' de la subdimension '.$subdimension->get_id().' <br/>';
								}
							}
						}
					}
				}//print_r($grades);

				// Do the division according to the times array to get the final grade of each competence
				foreach($grades as $id => $grade){
					$skillgrades[$id] = round($grade/$times[$id], 2, PHP_ROUND_HALF_UP);
					//$skillgrades[$id] = $grade/$times[$id];
				}
			}
		}

		return $skillgrades;
	}

	/**
	 * Get arithmetic average between the grades of every subdimension
	 *
	 * @static
	 *
	 * @param $params is an array of stdClass objects with the following fields:
	 * - mdlsubdimensionid
	 * - evxsubdimensionid
	 * - grade
	 *
	 * @return array with the following structure: [id] => [grade], or false
	 */
	private static function get_mean_average_subdimensions($params = false){

		if($params){
			$ids = array();
			$grades = array();
			$times = array();
			$meangrades = array();

			// Get grades of each subdimension
			foreach($params as $param){
				$id = $param->mdlsubdimensionid;
				if(in_array($id, $ids)){
					$grades[$id] += $param->grade;
					$times[$id]++;
				}
				else{
					$ids[] = $id;
					$grades[$id] = $param->grade;
					$times[$id] = 1;
				}
			}

			// Do the division according to the times array
			foreach($grades as $id => $grade){
				$meangrades[$id] = $grade/$times[$id];
			}

			return $meangrades;
		}
		else{
			return false;
		}
	}

	/**
	 * @static
	 *
	 * @param $courseid
	 * @param $activity
	 * @param $module
	 * @param $evxtask
	 * @param $evxassessment
	 * @param $arr_subdimension
	 *
	 * @return array of stdClass instances
	 */
	private static function get_subdimensions_assessments($courseid, $activity, $module, $evxtask, $evxassessment, $arr_subdimension){

		$arr_objects = array();

		// Get needed datas to create assessmentid
		$student = $evxassessment->studentid;
		$assessor = $evxassessment->assessorid;
		$mode = report_competence::get_type_of_assessment($student, $assessor, $courseid);
		$lms = MOODLE_NAME;

		// Create assessmentid
		$str = $courseid . '_' . $module . '_' . $activity . '_' . $student . '_' . $assessor . '_' . $mode . '_' . $lms;
		$assessmentid = md5($str);

		// Get toolid through evalcomix_modes
		$toolid = '';
		$moodletoolid = '';
		$params = array('taskid' => $evxtask->id, 'modality' => $mode);
		if ($evxmodes = evalcomix_modes::fetch($params)){
			$params = array('id' => $evxmodes->toolid);
			if($evxtool = evalcomix_tool::fetch($params)){
				// toolid in Moodle
				$moodletoolid = $evxmodes->toolid;
				// toolid in EvalCOMIX
				$toolid = $evxtool->idtool;
			}
		}

		// Get subdimensionid if the subdimension belongs to the tool which id is toolid
		foreach($arr_subdimension as $subdimension){
			if($subdimension->get_toolid() == $moodletoolid){
				// Create the stdObject and add it
				$object = new stdClass();
				$object->toolid = $toolid;
				$object->subdimensionid = $subdimension->get_evxsubid();
				$object->assessmentid = $assessmentid;
				$arr_objects[] = $object;
			}
		}

		return $arr_objects;
	}

	/**
	 * Type of assessment according to the student and the assessor
	 * @static
	 *
	 * @param $student
	 * @param $assessor
	 * @param $courseid
	 *
	 * @return string mode of an assessment according to the student and the assessor
	 */
	private static function get_type_of_assessment($student, $assessor, $courseid){

		/*Añadido por Daniel Cabeza: cambiar función de contexto deprecada por la actual*/
		//$context = get_context_instance(CONTEXT_COURSE, $courseid);
		$context = context_course::instance($courseid, MUST_EXIST);
		/*Fin del cambio*/
		
		if ($student == $assessor){
			$mode = 'self';
		}
		else{
			if (has_capability('block/evalcomix:edit',$context, $assessor)){
				$mode = 'teacher';
			}
			else{
				$mode = 'peer';
			}
		}

		return $mode;
	}

}