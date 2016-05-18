<?php

global $CFG;
include_once($CFG->dirroot . '/blocks/evalcomix/configeval.php');

// Moodle instance name
if(!defined('MOODLE_NAME')){
	define('MOODLE_NAME', $CFG->dbname);
}

if(isset($CFG->gescompeval_serverurl)){

	// URL base of Gescompeval application
	if(!defined('DIRGESCOMPEVAL')){
		define('DIRGESCOMPEVAL',$CFG->gescompeval_serverurl);
	}

	if(!defined('DIRGESCOMPEVALs')){
		define('DIRGESCOMPEVALs',$CFG->gescompeval_serverurl);
	}

	/////////////////////
	// Gescompeval API //
	/////////////////////

	if(!defined('SKILLS_GESC')){
		define('SKILLS_GESC', DIRGESCOMPEVAL . '/api/skills');
	}

	if(!defined('COMPETENCIES_GESC')){
		define('COMPETENCIES_GESC', DIRGESCOMPEVAL . '/api/competencies');
	}

	if(!defined('COMPETENCY_GESC')){
		define('COMPETENCY_GESC', DIRGESCOMPEVAL . '/api/competencies/ID');
	}

	if(!defined('OUTCOMES_GESC')){
		define('OUTCOMES_GESC', DIRGESCOMPEVAL . '/api/outcomes');
	}

	if(!defined('OUTCOME_GESC')){
		define('OUTCOME_GESC', DIRGESCOMPEVAL . '/api/outcomes/ID');
	}
}

// API for EvalCOMIX
if(isset($CFG->evalcomix_serverurl) && defined('DIREvalCOMIX')){

	if(!defined('GET_SUBDIMENSIONS_GRADES')){
		define('GET_SUBDIMENSIONS_GRADES', DIREvalCOMIX . '/webservice/get_grade_subdimensions.php');
	}
}

// Block name
if(!defined('blockname')){
	define('blockname', 'gescompeval');
}
?>
