<?php

include_once('gescompeval_object.php');
include_once('skill.php');
include_once('skill_course.php');
include_once('skill_course_sub.php');

/**
 * Class for subdimensions of EvalCOMIX tools
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subdimension extends gescompeval_object{
	public $table = 'block_gesc_subdimension';

	/**
	 * Array of required table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $required_fields = array('id', 'evxsubid', 'toolid');

	/**
	 * Array of optional table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $optional_fields = array();

	/**
	 * ID in EvalCOMIX web service
	 * @var string $evxsubid
	 */
	protected $evxsubid;

	/**
	 * Tool's ID in Moodle which the subdimension belongs to
	 * @var int $toolid
	 */
	protected $toolid;


	/**
	 * Constructor
	 * @param $id
	 * @param $evxsubid
	 * @param $toolid
	 */
	public function __construct($id = '', $evxsubid = '', $toolid = 0){
		$this->id = $id;
		$this->evxsubid = $evxsubid;
		$this->toolid = $toolid;
	}

	/**
	 * Set evxsubid
	 * @param $evxsubid
	 */
	public function set_evxsubid($evxsubid){
		$this->evxsubid = $evxsubid;
	}

	/**
	 * Get evxsubid
	 * @return evxsubid
	 */
	public function get_evxsubid(){
		return $this->evxsubid;
	}

	/**
	 * Set toolid
	 * @param $toolid
	 */
	public function set_toolid($toolid){
		$this->toolid = $toolid;
	}

	/**
	 * Get toolid
	 * @return toolid
	 */
	public function get_toolid(){
		return $this->toolid;
	}

	/**
	 * Finds and returns an array with the skill elements connected with the subdimension in a course
	 *
	 * @param int $courseid
	 * @return array array of skill instances or empty array
	 */
	public function get_skill_connected($courseid) {

		$arr_skill = array();
		// Get skill_course_sub connected with the subdimension
		$params = array('subdimensionid' => $this->id);
		if ($arr_skill_course_sub = skill_course_sub::fetch_all($params)){

			// Get skill_course of the course received and connected with the subdimension
			foreach($arr_skill_course_sub as $skill_course_sub){
				$params = array('id' => $skill_course_sub->get_skillcourseid());
				if ($skill_course = skill_course::fetch($params)){
					// Get skill element
					$params = array('id' => $skill_course->get_skillid());
					if($skill = skill::fetch($params)){
						$arr_skill[] = $skill;
					}
				}
			}
			//print_r($arr_skill);
		}
		return $arr_skill;
	}

	/**
	 * Get subdimensions connected with a course
	 * @static
	 *
	 * @param int $courseid
	 * @return array array of subdimensions instances if successful, false otherwise
	 */
	public static function get_subdimensions_by_course($courseid) {

		// Get skill_course of the course received
		$params = array('courseid'=>$courseid);
		if ($arr_skill_course = skill_course::fetch_all($params)){
			$arr_subdimension = array();

			// Get relations between the course and subdimensions
			foreach($arr_skill_course as $skill_course){

				// Each instance of $arr_skill_course_sub will be a relation between the
				// course and a subdimension
				$params = array('skillcourseid' => $skill_course->get_id());
				if ($arr_skill_course_sub = skill_course_sub::fetch_all($params)){

					// Get conected subdimensions
					foreach($arr_skill_course_sub as $skill_course_sub){

						$params = array('id' => $skill_course_sub->get_subdimensionid());
						if($subdimension = subdimension::fetch($params)){
							$id = $subdimension->get_id();

							// Only inserts not repeated subdimensions
							//if($arr_subdimension[$id] == null){
							if(!isset($arr_subdimension[$id])){
								$arr_subdimension[$id] = $subdimension;
							}
						}
					}
				}
			}

			return $arr_subdimension;
		}

		return false;
	}

	/**
	 * Records this object in the Database, sets its id to the returned value, and returns that value.
	 * If successful this function also fetches the new object data from database and stores it
	 * in object properties.
	 * @return int PK ID if successful, false otherwise
	 */
	public function insert(){
		global $DB;

		// Checking if the evalcomix tool exists before inserting
		$evxtool = $DB->get_record('block_evalcomix_tools', array('id'=>$this->toolid));
print_r($evxtool);

		if($evxtool){
			$this->id = parent::insert();
			return $this->id;
		}
		else{
			return false;
		}
	}

	/**
	 * Finds and returns all block_gesc_subdimension instances.
	 * @static abstract
	 *
	 * @return array array of block_gesc_subdimension instances or false if none found.
	 */
	public static function fetch_all($params){
		return gescompeval_object::fetch_all_helper('block_gesc_subdimension', 'subdimension', $params);
	}

	/**
	 * Finds and returns a block_gesc_subdimension instance based on params.
	 * @static
	 *
	 * @param array $params associative arrays varname=>value
	 * @return object subdimension instance or false if none found.
	 */
	public static function fetch($params) {
		return gescompeval_object::fetch_helper('block_gesc_subdimension', 'subdimension', $params);
	}

}
