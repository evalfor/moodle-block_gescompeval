<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Define the complete assignment structure for backup, with file and id annotations
 */

class backup_gescompeval_md_block_structure_step extends backup_block_structure_step {

	protected function define_structure() {

		// Define each element separated
		$gescompeval = new backup_nested_element('gescompeval_md', array(), array());
		$skills = new backup_nested_element('skills');
		$skill = new backup_nested_element('skill', array('id'), array('gescompevalid', 'type'));
		$skill_courses = new backup_nested_element('skill_courses');
		$skill_course = new backup_nested_element('skill_course', array('id'), array('skillid', 'courseid'));
		$subdimensions = new backup_nested_element('subdimenions');
		$subdimension = new backup_nested_element('subdimension', array('id'), array('evxsubid', 'toolid'));
		$skill_course_subs = new backup_nested_element('skill_course_subs');
		$skill_course_sub = new backup_nested_element('skill_course_sub', array('id'), array('skillcourseid', 'subdimensionid'));

		// Build the tree
		$gescompeval->add_child($skills);
		$gescompeval->add_child($skill_courses);
		$gescompeval->add_child($subdimensions);
		$gescompeval->add_child($skill_course_subs);
		$skills->add_child($skill);
		$skill_courses->add_child($skill_course);
		$subdimensions->add_child($subdimension);
		$skill_course_subs->add_child($skill_course_sub);

		// Define sources
		global $DB, $COURSE, $CFG;
		$courseid = $this->get_courseid();
		$cms = $DB->get_records('course_modules', array('course' => $courseid));
		$items = array();
		foreach($cms as $cm){
			$items[] = $cm->id;
		}
		$in_params = array();
		if(!empty($items)){
			list($in_sql, $in_params) = $DB->get_in_or_equal($items);
			foreach ($in_params as $key => $value) {
				$in_params[$key] = backup_helper::is_sqlparam($value);
			}
		}

		//if($block = $DB->get_record('block_gesc', array('courseid' => $courseid))){
		//	$gescompeval->set_source_table('block_gesc', array('id' => backup_helper::is_sqlparam($block->id)));
		//}


		/*include_once($CFG->dirroot . '/blocks/evalcomix/configeval.php');
		include_once($CFG->dirroot . '/blocks/evalcomix/classes/webservice_evalcomix_client.php');
		$evalcomix_environment->set_source_array(array((object)array('courseid' => $COURSE->id, 'moodlename' => MOODLE_NAME)));

		try{
			$array_xml_tool = array();
			$xml = webservice_evalcomix_client::get_ws_xml_tools2(array('courseid' => $courseid));
			foreach($xml as $toolxml){
				$id = (string)$toolxml['id'];
				foreach($toolxml as $txml){
					$array_xml_tool[$id] = $txml->asXML();
				}
			}
			if($tools = $DB->get_records('block_evalcomix_tools', array('evxid' => $block->id))){

				$array = array();
				foreach($tools as $tool){
					$time = time();
					$idtool = $tool->idtool;
					if(isset($array_xml_tool[$idtool])){
						$array[] = (object)array('id' => $tool->id, 'title' => $tool->title, 'type' => $tool->type, 'timecreated' => $time, 'timemodified' => $time, 'idtool' => $idtool, 'code' => $array_xml_tool[$idtool]);
					}
				}
				$evalcomix_tool->set_source_array($array);
			}
		}
		catch(Exception $e){

		}*/

		/*$invented->set_source_array(array((object)array('one' => 1, 'two' => 2, 'three' => 3),
		 (object)array('one' => 11, 'two' => 22, 'three' => 33))); // 2 object array*/

		//$evalcomix_tool->set_source_table('block_evalcomix_tools', array('evxid' => backup::VAR_PARENTID));


		if(!empty($in_params)){

			$skill->set_source_sql("
					SELECT *
					FROM {block_gesc_skill}", $in_params);

			$skill_course->set_source_sql("
					SELECT *
					FROM {block_gesc_skill_course}
					WHERE courseid $in_sql", $in_params);

			$subdimension->set_source_sql("
					SELECT *
					FROM {block_gesc_subdimension}", $in_params);

			$skill_course_sub->set_source_sql("
					SELECT *
					FROM {block_gesc_skill_course_subd}", $in_params);
		}

		$skill->set_source_table('block_gesc_skill', array());
		$subdimension->set_source_table('block_gesc_subdimension', array());
		$skill_course_sub->set_source_table('block_gesc_skill_course_subd', array());

		//$skill_course->set_source_table('block_gesc_skill_course', array('id' => backup::VAR_PARENTID));
		//$evalcomix_mode->set_source_table('block_evalcomix_modes', array('taskid' => backup::VAR_PARENTID));
		//$evalcomix_modes_time->set_source_table('block_evalcomix_modes_time', array('modeid' => backup::VAR_PARENTID));
		//$evalcomix_modes_extra->set_source_table('block_evalcomix_modes_extra', array('modeid' => backup::VAR_PARENTID));

		// Define annotations
		//$skill_course->annotate_ids('course_modules', 'courseid');

		return $this->prepare_block_structure($gescompeval);

	}
}
