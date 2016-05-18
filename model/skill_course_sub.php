<?php

include_once('gescompeval_object.php');
include_once('skill_course.php');
include_once('subdimension.php');

/**
 * Class for connecting competencies/results and courses with subdimensions
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class skill_course_sub extends gescompeval_object{
	public $table = 'block_gesc_skill_course_subd';

	/**
	 * Array of required table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $required_fields = array('id', 'skillcourseid', 'subdimensionid');

	/**
	 * Array of optional table fields, must start with 'id'.
	 * @var array $required_fields
	 */
	public $optional_fields = array();

	/**
	 * Comres_course ID
	 * @var int $skillcourseid
	 */
	protected $skillcourseid;

	/**
	 * Subdimension ID
	 * @var int $subdimensionid
	 */
	protected $subdimensionid;


	/**
	 * Constructor
	 * @param $id
	 * @param $skillcourseid
	 * @param $subdimensionid
	 */
	public function __construct($id='', $skillcourseid=0, $subdimensionid=0){
		$this->id = $id;
		$this->skillcourseid = $skillcourseid;
		$this->subdimensionid = $subdimensionid;
	}

	/**
	 * Set skillcourseid
	 * @param $skillcourseid
	 */
	public function set_skillcourseid(){
		return $this->skillcourseid;
	}

	/**
	 * Get skillcourseid
	 * @return skillcourseid
	 */
	public function get_skillcourseid(){
		return $this->skillcourseid;
	}

	/**
	 * Set subdimensionid
	 * @param $subdimensionid
	 */
	public function set_subdimensionid(){
		return $this->subdimensionid;
	}

	/**
	 * Get subdimensionid
	 * @return subdimensionid
	 */
	public function get_subdimensionid(){
		return $this->subdimensionid;
	}

	/**
	 * Records this object in the Database, sets its id to the returned value, and returns that value.
	 * If successful this function also fetches the new object data from database and stores it
	 * in object properties.
	 * @return int PK ID if successful, false otherwise
	 */
	public function insert(){
		global $DB;

		// Checking if skill_course and subdimension exist before inserting
		$skill_course = skill_course::fetch(array('id'=>$this->skillcourseid));
		$subdimension = subdimension::fetch(array('id'=>$this->subdimensionid));

		if($skill_course && $subdimension){
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

		if ($DB->delete_records($this->table, array('id'=>$this->id))) {

			// Check if the subdimension doesn't have any connections more. If so, delete it
			$aux = skill_course_sub::fetch_all(array('subdimensionid' => $data->subdimensionid));
			if (!$aux){
				if($subdimension = subdimension::fetch(array('id' => $data->subdimensionid))){
					$subdimension->delete();
				}
			}

			return true;

		} else {
			return false;
		}
	}

	/**
	 * Finds and returns all block_gesc_skill_course_subd instances.
	 * @static abstract
	 *
	 * @return array array of block_gesc_skill_course_subd instances or false if none found.
	 */
	public static function fetch_all($params){
		return gescompeval_object::fetch_all_helper('block_gesc_skill_course_subd', 'skill_course_sub', $params);
	}

	/**
	 * Finds and returns a block_gesc_skill_course_subd instance based on params.
	 * @static
	 *
	 * @param array $params associative arrays varname=>value
	 * @return object skill_course_sub instance or false if none found.
	 */
	public static function fetch($params) {
		return gescompeval_object::fetch_helper('block_gesc_skill_course_subd', 'skill_course_sub', $params);
	}
}