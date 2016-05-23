<?php
/**
 * @package    block_gescompeval_md
 * @copyright  2010 onwards EVALfor Research Group {@link http://evalfor.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Daniel Cabeza Sánchez <daniel.cabeza@uca.es>
 */

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
		/*Añadido por Daniel Cabeza*/
		//$subdimensions = new backup_nested_element('subdimenions');
		$subdimensions = new backup_nested_element('subdimensions');
		/*Fin del Cambio*/
		$subdimension = new backup_nested_element('subdimension', array('id'), array('evxsubid', 'toolid', 'level'));
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

			
		}

		$skill->set_source_table('block_gesc_skill', array());
		/*Añadido por Daniel Cabeza */
		//$subdimension->set_source_table('block_gesc_subdimension', array());
		
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/subdimension.php');
		$all_subdimensions = subdimension::fetch_all(array());
		if(!empty($all_subdimensions)){
			$array = array();
			foreach($all_subdimensions as $sub){
				$level = $this->get_level($sub->get_toolid(), $sub->get_evxsubid());
				$array[] = (object)array('id' => $sub->get_id(), 'evxsubid' => $sub->get_evxsubid(), 'toolid' => $sub->get_toolid(), 'level' => $level);
			}
			$subdimension->set_source_array($array);
		}
		
		$skill_course->set_source_table('block_gesc_skill_course', array('courseid' => backup::VAR_COURSEID));
		$skill_course_sub->set_source_sql('
					SELECT scs.*
					FROM {block_gesc_skill_course_subd} scs, {block_gesc_skill_course} sc, {block_gesc_subdimension} s
                    WHERE 
							scs.skillcourseid = sc.id 
						AND scs.subdimensionid = s.id 
						AND sc.courseid = ?', array(backup::VAR_COURSEID));
		/*Fin del Cambio*/
		//$skill_course->set_source_table('block_gesc_skill_course', array('id' => backup::VAR_PARENTID));
		//$evalcomix_mode->set_source_table('block_evalcomix_modes', array('taskid' => backup::VAR_PARENTID));
		//$evalcomix_modes_time->set_source_table('block_evalcomix_modes_time', array('modeid' => backup::VAR_PARENTID));
		//$evalcomix_modes_extra->set_source_table('block_evalcomix_modes_extra', array('modeid' => backup::VAR_PARENTID));

		// Define annotations
		//$skill_course->annotate_ids('course_modules', 'courseid');

		return $this->prepare_block_structure($gescompeval);

	}
	
	function get_level($toolid, $subdimensionid){
		global $CFG;
		require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');
		$i = 0; //tool index (for mixed tool)
		$j = 0; //dimension index
		$k = 0; //subdimension index
		$l = 0; //attribute index (for differential tool
		if($tool = evalcomix_tool::fetch(array('id'=>$toolid))){
			include_once($CFG->dirroot . '/blocks/gescompeval_md/model/ws_evalcomix_client.php');
			if($tool->type == 'mixed'){
				$mixtools = ws_evalcomix_client::get_tools_mixed($tool->idtool);//print_r($mixtools);
				foreach ($mixtools as $dimensions){
					foreach ($dimensions as $dimname => $subdimensions){
						foreach ($subdimensions as $id => $name){
							if($id == $subdimensionid){
								return $i.'/'. $k . '/' . $j .'/' . $l;
							}
							$k++;
						}
						$j++;
					}
					$i++;
				}
			}
			elseif ($tool->type == 'differential'){
				$attributes = ws_evalcomix_client::get_attributes_differential($tool->idtool);
				foreach ($attributes as $id => $name){
					if($id == $subdimensionid){
						return $i.'/'. $k . '/' . $j .'/' . $l;
					}
					++$l;
				}
			}
			else{
				$dimensions = ws_evalcomix_client::get_dimensions($tool->idtool);
				foreach ($dimensions as $dimname => $subdimensions){
					foreach ($subdimensions as $id => $name){
						if($id == $subdimensionid){
							return $i.'/'. $k . '/' . $j .'/' . $l;
						}
						$k++;
					}
					$j++;
				}
			}
		}
		return '';
	}
}
