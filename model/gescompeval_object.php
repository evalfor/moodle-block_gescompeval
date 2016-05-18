<?php
if(file_exists('../../../config.php')){
	include_once('../../../config.php');
}

/**
 * Parent class
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gescompeval_object {
	protected $table;

	/**
	 * PK
	 * @var int $id
	 */
	protected $id;

	/**
	 * Get id
	 * @return id
	 */
	public function get_id(){
		return $this->id;
	}


	/**
	 * Records this object in the Database, sets its id to the returned value, and returns that value.
	 * If successful this function also fetches the new object data from database and stores it
	 * in object properties.
	 * @return int PK ID if successful, false otherwise
	 */
	public function insert(){
		global $DB;

		if (!empty($this->id)) {
			debugging("Object already exists!");
			return false;
		}

		$data = $this->get_record_data();

		$this->id = $DB->insert_record($this->table, $data);

		return $this->id;
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
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Returns object with fields and values that are defined in database
	 */
	public function get_record_data() {
		$data = new stdClass();
		foreach ($this as $var=>$value) {
			if (in_array($var, $this->required_fields) or array_key_exists($var, $this->optional_fields)) {
				if (is_object($value) or is_array($value)) {
					debugging("Incorrect property '$var' found when inserting object");
				} else {
					$data->$var = $value;
				}
			}
		}
		return $data;
	}

	/**
	 * Factory method - uses the parameters to retrieve matching instance from the DB.
	 * @static final protected
	 * @return mixed object instance or false if not found
	 */
	protected static function fetch_helper($table, $classname, $params) {
		if ($instances = gescompeval_object::fetch_all_helper($table, $classname, $params)) {
			if (count($instances) > 1) {
				// we should not tolerate any errors here - problems might appear later
				print_error('morethanonerecordinfetch','debug');
			}
			return reset($instances);
		} else {
			return false;
		}
	}

	/**
	 * Factory method - uses the parameters to retrieve all matching instances from the DB.
	 * @static final protected
	 * @return mixed array of object instances or false if not found
	 */
	public static function fetch_all_helper($table, $classname, $params) {

		$instance = new $classname();

		$classvars = (array)$instance;
		$params    = (array)$params;

		$wheresql = array();
		$newparams = array();

		foreach ($params as $var=>$value) {//echo "var:$var = $value<br>";
			if (!in_array($var, $instance->required_fields) and !array_key_exists($var, $instance->optional_fields)) {
				continue;
			}
			if (is_null($value)) {
				$wheresql[] = " $var IS NULL ";
			} else {
				$wheresql[] = " $var = ? ";
				$newparams[] = $value;
			}
		}

		if (empty($wheresql)) {
			$wheresql = '';
		} else {
			$wheresql = implode("AND", $wheresql);
		}

		global $DB;

		$rs = $DB->get_recordset_select($table, $wheresql, $newparams);
		//returning false rather than empty array if nothing found
		if (!$rs->valid()) {
			$rs->close();
			return false;
		}

		$result = array();
		foreach($rs as $data) {
			$instance = new $classname();
			gescompeval_object::set_properties($instance, $data);
			$result[$instance->id] = $instance;
		}
		$rs->close();

		return $result;
	}

	/**
	 * Given an associated array or object, cycles through each key/variable
	 * and assigns the value to the corresponding variable in this object.
	 * @static final
	 */
	public static function set_properties(&$instance, $params) {
		$params = (array) $params;
		foreach ($params as $var => $value) {
			if (in_array($var, $instance->required_fields) or array_key_exists($var, $instance->optional_fields)) {
				$instance->$var = $value;
			}
		}
	}

}

?>