<?php

require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/model/skill.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/model/subdimension.php');

if(isset($_POST['subdimensionid']) && $_POST['subdimensionid'] != '' &&
   isset($_POST['courseid']) && $_POST['courseid'] != ''){

	$subdimensionid = $_POST['subdimensionid'];
	$courseid = $_POST['courseid'];

	// Get competence connected with the subdimension
	$params = array('evxsubid' => $subdimensionid);
	/*Añadido por Daniel Cabeza: defino $arr_skill con un valor array()*/
	$arr_skill = array();
	/*Fin del cambio*/
	if($subdimension = subdimension::fetch($params)){
		$arr_skill = $subdimension->get_skill_connected($courseid);
	}

	// Get codes and descriptions from Web Service
	if($arr_obj = skill::get_array_skill_information($arr_skill)){
		echo '<ul>';
		foreach($arr_obj as $obj){
			echo '<li style=";text-align:left" type="disc">'.$obj->code.': '.$obj->shortdescription.'</li>';
		}
		echo '</ul>';
	}
}
?>