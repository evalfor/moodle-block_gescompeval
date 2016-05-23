<?php
/**
 * @package    block_gescompeval
 * @copyright  2010 onwards EVALfor Research Group {@link http://evalfor.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Daniel Cabeza Sánchez <daniel.cabeza@uca.es>
 */

class restore_gescompeval_md_block_structure_step extends restore_structure_step {

    protected function define_structure() {

        $paths = array();

		$paths[] = new restore_path_element('gescompeval_md_skill', '/block/gescompeval_md/skills/skill');
		/*$paths[] = new restore_path_element('gescompeval_md_subdimension', '/block/gescompeval_md/subdimensions/subdimension');
		$paths[] = new restore_path_element('gescompeval_md_skill_course', '/block/gescompeval_md/skill_courses/skill_course');
		$paths[] = new restore_path_element('gescompeval_md_skill_course_sub', '/block/gescompeval_md/skill_courses/skill_course_sub');*/
		
        return $paths;
    }

    public function process_gescompeval_md_skill($data) {
        global $DB, $CFG;
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill.php');
		
        $data = (object)$data;
		$oldid = $data->id;
		
		if(!skill::fetch(array('gescompevalid' => $data->gescompevalid))){
			$newitemid = $DB->insert_record('block_gesc_skill', $data);
			$this->set_mapping('skill', $oldid, $newitemid);
		}
		else{
			$this->set_mapping('skill', $oldid, $oldid);
		}
		
    }
	/*
	public function process_gescompeval_md_skill_course($data) {echo "dentro3";
        global $DB, $CFG;
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course.php');
		
        $data = (object)$data;
		$oldid = $data->id;
		$data->courseid = $this->get_courseid();
		$newskillid = $this->get_mapping('skill', $data->skillid);
		if($newskillid && !skill_course::fetch(array('skillid' => $newskillid, 'courseid' => $data->courseid))){
			$data->skillid = $newskillid;
			$newitemid = $DB->insert_record('block_gesc_skill_course', $data);
			$this->set_mapping('skill_course', $oldid, $newitemid);
		}
		else{
			$this->set_mapping('skill', $oldid, $oldid);
		}
    }
    
    public function process_gescompeval_md_subdimension($data) {
        global $DB, $CFG;
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/subdimension.php');
		
        $data = (object)$data;
		$oldid = $data->id;
		$data->toolid = $this->get_mapping('evalcomix_tool', $data->toolid);
		if(!subdimension::fetch(array('toolid' => $data->toolid))){
			$newitemid = $DB->insert_record('block_gesc_subdimension', $data);
			$this->set_mapping('subdimension', $oldid, $newitemid);
		}
		else{
			$this->set_mapping('skill', $oldid, $oldid);
		}
    }
    
    public function process_gescompeval_md_skill_course_sub($data) {
        global $DB, $CFG;
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course_sub.php');
		
        $data = (object)$data;
		$oldid = $data->id;
		$data->skillcourseid = get_mapping('skill_course', $data->skillcourseid);
		$data->subdimensionid = $this->get_mapping('subdimension', $data->subdimensionid);
		if(!skill_course_sub::fetch(array('skillcourseid' => $data->skillcourseid, 'subdimensionid' => $data->subdimensionid))){
			$newitemid = $DB->insert_record('block_gesc_skill_course_sub', $data);
			$this->set_mapping('skill_course', $oldid, $newitemid);
		}
		else{
			$this->set_mapping('skill', $oldid, $oldid);
		}
    }*/
    
    public function after_restore(){
		global $DB, $COURSE, $CFG;
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course.php');
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/subdimension.php');
		include_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course_sub.php');
		require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');
		
		$settings = $this->task->get_info()->root_settings;
	
		$fullpath = $this->task->get_taskbasepath();
        // We MUST have one fullpath here, else, error
        if (empty($fullpath)) {
            throw new restore_step_exception('restore_structure_step_undefined_fullpath');
        }

        // Append the filename to the fullpath
        $fullpath = rtrim($fullpath, '/') . '/' . $this->filename;

        // And it MUST exist
        if (!file_exists($fullpath)) { // Shouldn't happen ever, but...
            throw new restore_step_exception('missing_moodle_backup_xml_file', $fullpath);
        }
		$xml = simplexml_load_file($fullpath);
		$courseid_new = $this->get_courseid();
		
		$skill_courses_hash = array();
		if(isset($xml->gescompeval_md->skill_courses[0])){
			foreach($xml->gescompeval_md->skill_courses[0] as $skill_course){
				$skillid_old = (string)$skill_course->skillid;
				$newskill = $this->get_mapping('skill', $skillid_old);
				$skill_course_params = array('skillid' => $newskill->newitemid, 'courseid' => $courseid_new);
				if(!$skill_course_new = skill_course::fetch($skill_course_params)){
					$skill_course_new = new skill_course('', $newskill->newitemid, $courseid_new);
					$skill_course_id_new = $skill_course_new->insert();
		
					if(!empty($skill_course['id'])){
						$skill_course_id_old = (string)$skill_course['id'];
						$skill_courses_hash[$skill_course_id_old] = $skill_course_id_new;
					}
				}
			}
		}
		
		$subdimension_hash = array();
		if(isset($xml->gescompeval_md->subdimensions[0])){
			foreach($xml->gescompeval_md->subdimensions[0] as $subdimension){
				$toolid_old = (string)$subdimension->toolid;
				$newtool = $this->get_mapping('evalcomix_tool', $toolid_old);
				
				if(isset($newtool)){
					// Get evalcomix tool by its id and if it exists, get the subdimensions
					$elements_old = $this->get_subdimensions($toolid_old);
					$elements_new = $this->get_subdimensions($newtool->newitemid);
					$evxsubid_new = '';
					if(!empty($elements_old) && !empty($elements_new) && count($elements_old) == count($elements_new)){
						foreach($elements_old as $key_tool => $tools){
							foreach($tools as $key_dim => $dimensions){
								foreach($dimensions as $key_sub => $subdimensions){
									foreach($subdimensions as $key_att => $attributes){
										if((string)$subdimension->evxsubid == $attributes){
											$evxsubid_new = $elements_new[$key_tool][$key_dim][$key_sub][$key_att];
										}
									}
								}
							}
						}
					}
					else{
						$level = (string)$subdimension->level;
						if(!empty($level)){
							$exp_level = explode('/', $level);
							$key_tool = $exp_level[0];
							$key_dim = $exp_level[1];
							$key_sub = $exp_level[2];
							$key_att = $exp_level[3];
							$evxsubid_new = $elements_new[$key_tool][$key_dim][$key_sub][$key_att];
						}
					}
						
					if(isset($evxsubid_new)){
						if(!empty($newtool) && !$subdimension_new = subdimension::fetch(array('toolid' => $newtool->newitemid, 'evxsubid' => $evxsubid_new))){
							$subdimension_new = new subdimension('', $evxsubid_new, $newtool->newitemid);
							if(!$subdimensionid_new = $subdimension_new->insert()){
								echo "Esta subdimensión no se ha guardado";
							}
						
							if(!empty($subdimension['id'])){
								$id_old = (string)$subdimension['id'];
								$subdimension_hash[$id_old] = $subdimensionid_new;
							}
						}
					}
				}	
			}
		}
	
		if(isset($xml->gescompeval_md->skill_course_subs[0])){
			foreach($xml->gescompeval_md->skill_course_subs[0] as $skill_course_sub){
				$skillcourseid_old = (string)$skill_course_sub->skillcourseid;
				$subdimensioid_old = (string)$skill_course_sub->subdimensionid;
				$id_old = $skill_course_sub['id'];
				
				$skillcourseid_new = $skill_courses_hash[$skillcourseid_old];
				$subdimensioid_new = $subdimension_hash[$subdimensioid_old];
				$id_old = $skill_course_sub['id'];
				
				if(!$skill_course_sub_new = skill_course_sub::fetch(array('skillcourseid' => $skillcourseid_new, 'subdimensionid' => $subdimensioid_new))){
					$skill_course_sub_new = new skill_course_sub('', $skillcourseid_new, $subdimensioid_new);
					$skill_course_sub_new->insert();
				}
			}
		}
		//exit;
	}
	
	function get_subdimensions($toolid){
		global $CFG;
		require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');
		$result = array();
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
							$result[$i][$k][$j][$l] = $id;
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
					$result[$i][$k][$j][$l] = $id;
					++$l;
				}
			}
			else{
				$dimensions = ws_evalcomix_client::get_dimensions($tool->idtool);
				foreach ($dimensions as $dimname => $subdimensions){
					foreach ($subdimensions as $id => $name){
						
						$result[$i][$k][$j][$l] = $id;
						$k++;
					}
					$j++;
				}
			}
		}
		return $result;
	}
	
}

