<?php

include_once('gescompeval_object.php');
include_once('skill.php');
include_once('skill_course_sub.php');
include_once('ws_gescompeval_client.php');

/**
 * Class for connecting competencies/results and courses
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skill_course extends gescompeval_object{
	public $table = 'block_gesc_skill_course';

	/**
	 * Array of required table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $required_fields = array('id', 'skillid', 'courseid');

	/**
	 * Array of optional table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $optional_fields = array();

	/**
	 * Competence/Result ID
	 * @var int $skillid
	 */
	public $skillid;

	/**
	 * Course ID
	 * @var int $courseid
	 */
	public $courseid;


	/**
	 * Constructor
	 * @param $id
	 * @param $skillid
	 * @param $courseid
	 */
	public function __construct($id='', $skillid=0, $courseid=0){
		$this->id = $id;
		$this->skillid = $skillid;
		$this->courseid = $courseid;
	}

	/**
	 * Set skillid
	 * @param $skillid
	 */
	public function set_skillid(){
		return $this->skillid;
	}

	/**
	 * Get skillid
	 * @return skillid
	 */
	public function get_skillid(){
		return $this->skillid;
	}

	/**
	 * Set courseid
	 * @param $courseid
	 */
	public function set_courseid(){
		return $this->courseid;
	}

	/**
	 * Get courseid
	 * @return courseid
	 */
	public function get_courseid(){
		return $this->courseid;
	}

	/**
	 * Records this object in the Database, sets its id to the returned value, and returns that value.
	 * If successful this function also fetches the new object data from database and stores it
	 * in object properties.
	 * @return int PK ID if successful, false otherwise
	 */
	public function insert(){
		global $DB;

		// Checking if competence/result and course exist before inserting
		$skill = skill::fetch(array('id'=>$this->skillid));
		$course = $DB->get_record('course', array('id'=>$this->courseid));

		if($course && $skill){
			$this->id = parent::insert();
			return $this->id;
		}
		else{
			return false;
		}
	}

	/**
	 * Deletes this object from the database.
	 * @param string $source from where was the object deleted
	 * @return boolean success
	 */
	public function delete(){
		global $DB;

		if (empty($this->id)) {
			debugging('Can not delete object, no id!');
			return false;
		}

		$data = $this->get_record_data();

		// Check if exists some skill_course_sub with this skill_course to delete them
		if($arr_ccs = skill_course_sub::fetch_all(array('skillcourseid'=>$this->id))){
			foreach($arr_ccs as $skill_course_sub){
				$skill_course_sub->delete();
			}
		}

		if ($DB->delete_records($this->table, array('id'=>$this->id))) {

			// Check if the skill doesn't have any connections more. If so, delete it
			$aux = skill_course::fetch_all(array('skillid' => $data->skillid));
			if (!$aux){
				if($skill = skill::fetch(array('id' => $data->skillid))){
					$skill->delete();
				}
			}

			return true;

		} else {
			return false;
		}
	}

	/**
	 * Get connected abilities (competencies or learning outcomes) which are included in the course
	 * @return array array of objects with id,gescompevalid,code,description and type
	 */
	/*public function get_connected_abilities(){

		$arr_skill = array();
		$skill = skill::fetch(array('id' => $this->skillid));

		// Get the abilities connected
		if($skill->get_type() == 'competency'){
			$arr_abi = ws_gescompeval_client::get_connected_results($skill->get_gescompevalid());
		}
		else{
			$arr_abi = ws_gescompeval_client::get_connected_competencies($skill->get_gescompevalid());
		}

		if($arr_abi && count($arr_abi>0)){
			// Only return the ones which are connected with the course
			foreach($arr_abi as $abi){
				$skill = skill::fetch(array('gescompevalid' => (int)$abi->id));
				$skill_course = skill_course::fetch(array('skillid' => $skill->get_id(), 'courseid' => $this->courseid));
				if($skill_course){
					$arr_skill[] = $skill;
				}
			}
		}

		return $arr_skill;
	}*/

	/**
	 * Get all skill objects connected with a course
	 * @static
	 *
	 * @param courseid
	 * @return array array of skill objects if successful, false otherwise
	 */
	public static function get_skills_by_courseid($courseid) {

		$arr_skill = false;
		$params = array('courseid' => $courseid);
		$arr_skill_course = skill_course::fetch_all($params);
		if($arr_skill_course){
			// Get skill objects
			$arr_skill = array();
			foreach($arr_skill_course as $skill_course){
				$params = array('id' => $skill_course->get_skillid());
				$arr_skill[] = skill::fetch($params);
			}
		}

		return $arr_skill;
	}

	/**
	 * Finds and returns all block_gesc_skill_course instances.
	 * @static
	 *
	 * @return array array of block_gesc_skill_course instances or false if none found.
	 */
	public static function fetch_all($params){
		return gescompeval_object::fetch_all_helper('block_gesc_skill_course', 'skill_course', $params);
	}

	/**
	 * Finds and returns a block_gesc_skill_course instance based on params.
	 * @static
	 *
	 * @param array $params associative arrays varname=>value
	 * @return object skill_course instance or false if none found.
	 */
	public static function fetch($params) {
		return gescompeval_object::fetch_helper('block_gesc_skill_course', 'skill_course', $params);
	}
}