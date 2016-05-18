<?php

require_once('../model/skill.php');
require_once('../model/skill_course.php');
require_once('lib.php');

//$skill = skill::fetch(array('id'=>1));
//echo report_competence::get_evidences($skill, 3, 1, 2);
//exit;

if(isset($_POST['courseid']) && $_POST['courseid'] != ''){
	$courseid = $_POST['courseid'];
	//$courseid = 2;

	if(isset($_POST['studentid']) && $_POST['studentid'] != ''){
		$studentid = $_POST['studentid'];
	}
	else{
		$studentid = '';
		//$studentid = 3;
	}

	// Check if is necessary show the evidences
	if(isset($_POST['withevidences']) && $_POST['withevidences'] == 1){
		$withevidences = true;
	}
	else{
		$withevidences = false;
	}

	// Check if the text for competencies and learning outcomes is received
	if(isset($_POST['competencies_text']) && isset($_POST['outcomes_text'])){
		$competencies_text = $_POST['competencies_text'];
		$outcomes_text = $_POST['outcomes_text'];
	}
	else{
		$competencies_text = 'Competencies';
		$outcomes_text = 'Learning outcomes';
	}

	$table = array();
	$table['cols'] = array(
			// Define my DataTable columns here
			// Each column gets its own array
			// Syntax of the arrays is:
			// label => column label
			// type => data type of column
			//
			// The first column is a "string" type
			// The second column is a "number" type, for the competencies
			// The third column is a string type for the tooltips of the competencies
			// The forth column is a "number" type, for the learning outcomes
			// The fifth column is a string type for the tooltips of the learning outcomes
			// The sixth column is a string type for the color
			array('label' => 'Code', 'type' => 'string'),
			array('label' => $competencies_text, 'type' => 'number'),
			array('role' => 'tooltip', 'type' => 'string', 'p' => array('html'=>true)),
			array('label' => $outcomes_text, 'type' => 'number'),
			array('role' => 'tooltip', 'type' => 'string', 'p' => array('html'=>true)),
			array('role' => 'style', 'type' => 'string')
	);

	// Get skills connected with the course
	$arr_skill = skill_course::get_skills_by_courseid($courseid);

	// Get data of reports (toolid, subdimensionid, assessmentid)
	// If a studentid is received only gets its data, else it gets the data of every student
	if($studentid != ''){
		$arr_objects = report_competence::get_data_report_student($courseid, $studentid);
	}
	else{
		$arr_objects = report_competence::get_data_report_course($courseid);
	}

	// Get grades of each competence
	// In the old version, here took the grades taking account the relations through a skill method called get_competence_relations
	$skillgrades = report_competence::get_competence_grade($courseid, $arr_objects);

	// Create an array with the datas of the competence to display
	if($arr_competence = skill::get_array_skill_information($arr_skill)){
		foreach ($arr_competence as $competence){

			$temp = array();
			$temp[] = array('v' => (string)$competence->code);

			// Get grade
			if(array_key_exists($competence->id, $skillgrades)){
				$grade = $skillgrades[$competence->id];
			}
			else{
				$grade = 0;
			}

			// Get tooltip information
			if($withevidences){
				$htmltext = report_competence::get_tooltip($arr_competence, $competence, $grade, $courseid, $studentid);
			}
			else{
				$htmltext = report_competence::get_tooltip($arr_competence, $competence, $grade, $courseid, '');
			}

			// Use a color for competency and another for learning outcomes
			if($competence->type == 'competency'){
				$temp[] = array('v' => $grade);
				$temp[] = array('v' => $htmltext);
				$temp[] = array('v' => null);
				$temp[] = array('v' => null);
				$temp[] = array('v' => 'blue');
			}
			elseif($competence->type == 'outcome'){
				$temp[] = array('v' => null);
				$temp[] = array('v' => null);
				$temp[] = array('v' => $grade);
				$temp[] = array('v' => $htmltext);
				$temp[] = array('v' => 'green');
			}

			//Add the row
			$rows[] = array('c' => $temp);
		}
	}

	// Insert the rows
	$table['rows'] = $rows;

	// Encode the table as JSON
	$jsonTable = json_encode($table);

	// Set up header; first two prevent IE from caching queries
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	// Return the JSON data
	echo $jsonTable;
}

?>