<?php

include_once('gescompeval_object.php');
include_once('skill_course.php');
include_once('ws_gescompeval_client.php');

/**
 * Class for competencies and learning results
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skill extends gescompeval_object{
	public $table = 'block_gesc_skill';

	/**
	 * Array of required table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $required_fields = array('id', 'gescompevalid', 'type');

	/**
	 * Array of optional table fields, must start with 'id'.
	 * @var array $required_fields
	*/
	public $optional_fields = array();

	/**
	 * ID in Gescompeval web service
	 * @var int $gescompevalid
	 */
	public $gescompevalid;

	/**
	 * Array of courses connected with the competence/result
	 * @var array $courses
	 */
	public $courses;


	/**
	 * Constructor
	 * @param $id
	 * @param $gescompevalid
	 * @param $type
	 */
	public function __construct($id = '', $gescompevalid = 0, $type = ''){
		$this->id = $id;
		$this->gescompevalid = $gescompevalid;
		$this->type = $type;
		$this->courses = array();
	}

	/**
	 * Set gescompevalid
	 * @param $gescompevalid
	 */
	public function set_gescompevalid($gescompevalid){
		$this->gescompevalid = $gescompevalid;
	}

	/**
	 * Get gescompevalid
	 * @return gescompevalid
	 */
	public function get_gescompevalid(){
		return $this->gescompevalid;
	}

	/**
	 * Set type
	 * @param $type
	 */
	public function set_type($type){
		$this->type = $type;
	}

	/**
	 * Get type
	 * @return type
	 */
	public function get_type(){
		return $this->type;
	}

	/**
	 * Set connected courses
	 * @return courses
	 */
	public function set_connected_courses(){
		// Getting courses connected in DB to set and return
		if ($courses = skill_course::fetch_all(array('skillid'=>$this->id))){
			$this->courses = $courses;
		}
		else{
			return array();
		}
		return $courses;
	}

	/**
	 * Get courses
	 * @return courses
	 */
	public function get_courses(){
		return $this->courses;
	}

	/**
	 * Finds and returns an array with subdimensions instances connected with the skill in
	 * a course
	 *
	 * @param int $courseid
	 * @return array array of subdimensions instances or empty array
	 */
	public function get_subdimensions_connected($courseid) {
		$arr_subdimensions = array();

		// Get skill_course instance connected with the skill in the course received
		$params = array('courseid' => $courseid, 'skillid' => $this->id);
		if($skill_course = skill_course::fetch($params)){

			// Get skill_course_sub instances related
			$params = array('skillcourseid' => $skill_course->get_id());
			if($arr_skill_course_sub = skill_course_sub::fetch_all($params)){

				// Get subdimensions related
				foreach($arr_skill_course_sub as $skill_course_sub){

					$params = array('id' => $skill_course_sub->get_subdimensionid());
					if($subdimension = subdimension::fetch($params)){
						$arr_subdimensions[] = $subdimension;
					}
				}
			}
		}

		return $arr_subdimensions;
	}

	/**
	 * Get connected skills (competencies or learning outcomes) with the skill in the course
	 * @param courseid
	 * @return array array of objects with id,gescompevalid,code,shortdescription and type
	 */
	public function get_connected_skills($courseid){

		$connected_skills = array();
		$connected_ids = array();

		// Get subdimensions connected in the course with the skill
		if($arr_subdimensions = $this->get_subdimensions_connected($courseid)){

			// For each one, get the connected skills with a different type
			foreach($arr_subdimensions as $subdimension){
				$arr_connected_skill = $subdimension->get_skill_connected(null);
				foreach($arr_connected_skill as $connected_skill){
					// If the skill has a different type and it has not been
					// added before, add it now
					if($connected_skill->get_type() != $this->type ||
					in_array($connected_skill->get_id(), $connected_ids)){
						$connected_skills[] = $connected_skill;
						$connected_ids[] = $connected_skill->get_id();
					}
				}
			}
		}

		return $connected_skills;
	}

	/**
	 * Get Information from Gescompeval WS about the competence received
	 * @static
	 *
	 * @param $skill skill instance
	 * @return stdClass stdClass instance with skill information or false
	 */
	public static function get_skill_information($skill){
		/*Añadido por Daniel Cabeza*/
		include_once('ws_gescompeval_client.php');
		/*Fin del cambio*/
		$obj = false;

		if($skill){
			$obj = new stdClass();
			$obj->id = $skill->get_id();
			$obj->gescompevalid = $skill->get_gescompevalid();
			$obj->type = $skill->type;
			// Obtain code and description from web service
			$id_type = $skill->get_gescompevalid().'-'.$skill->get_type();
			if ($data = ws_gescompeval_client::get_skills($id_type)){
				$obj->code = $data->code;
				$obj->shortdescription = $data->shortdescription;
				$obj->longdescription = $d->longdescription;
				if($obj->type == 'competency'){
					$obj->competencetype = $data->competencetype_type;
				}
			}
		}
		return $obj;
	}

	/**
	 * Get Information from Gescompeval about array of skills received
	 * @static
	 *
	 * @param $arr_skills array of skill instances
	 * @return array array of stdClass instances with skill information
	 */
	public static function get_array_skill_information($arr_skills){

		$elements = false;

		if($arr_skills){

			$ids_types = '';
			$arr_ids = array();
			$elements = array();
			foreach($arr_skills as $skill){
				// Create a string with the ids and types
				$ids_types.= $skill->get_gescompevalid() . '-'.$skill->get_type().';';
				// Save the moodle ids
				$arr_ids[] = $skill->get_id();
			}

			// Get code and shortdescription from WS
			if ($data = ws_gescompeval_client::get_skills($ids_types)){
				/*Añadido por Daniel Cabeza: Añadida la consrucción del objeto $obj*/
				$obj = new stdClass();
				/*Fin del cambio*/
				$obj->code = $data->code;
				$obj->shortdescription = $data->shortdescription;

				$cont = 0;
				foreach($data as $d){
					$obj = new stdClass();
					// The values are returned in the same orden that the ids are sent
					$obj->id = $arr_ids[$cont];
					$cont++;
					/*if($skill = skill::fetch(array('id'=>$cont))){
					 if($d->id != $skill->get_gescompevalid()){
					die('Error in the data received. IDs don\'t agree');
					}
					}*/
					$obj->gescompevalid = $d->id;
					$obj->type = $d->skilltype;
					$obj->code = $d->code;
					$obj->shortdescription = $d->shortdescription;
					$obj->longdescription = $d->longdescription;
					if($obj->type == 'competency'){
						$obj->competencetype = $d->competencetype_type;
					}

					$elements[] = $obj;
				}
			}
		}

		return $elements;
	}

	/**
	 * Finds and returns all block_gesc_skill instances.
	 * @static abstract
	 *
	 * @return array array of block_gesc_skill instances or false if none found.
	 */
	public static function fetch_all($params){
		return gescompeval_object::fetch_all_helper('block_gesc_skill', 'skill', $params);
	}

	/**
	 * Finds and returns a block_gesc_skill instance based on params.
	 * @static
	 *
	 * @param array $params associative arrays varname=>value
	 * @return object skill instance or false if none found.
	 */
	public static function fetch($params) {
		return gescompeval_object::fetch_helper('block_gesc_skill', 'skill', $params);
	}
}