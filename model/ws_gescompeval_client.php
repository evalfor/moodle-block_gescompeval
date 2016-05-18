<?php

global $CFG;
include_once($CFG->dirroot . '/blocks/gescompeval_md/confgescompeval.php');
include_once($CFG->dirroot .'/blocks/gescompeval_md/model/curl.class.php');

/**
 * Class for using Gescompeval API
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ws_gescompeval_client {

	/**
	 * Get one or more skills from web service
	 * @static
	 *
	 * @param string $ids it's a string with the ids separated by ';'
	 * @return array of stdClass instances with the data provided or false
	 */
	public static function get_skills($ids = ''){ //old get_objects
		defined('SKILLS_GESC') || die('Gescompeval is not configured properly');
		$url = SKILLS_GESC;
		$curl = new Curly();

		// Check if $ids is an empty string
		if($ids != ''){

			// Create xml to send it by POST
			$xml = '<?xml version="1.0" encoding="utf-8"?><skills>';

			// Get all the ids separated by ';'
			$arr_ids_types = explode(';', $ids);

			foreach($arr_ids_types as $id_type){
				$arr_id_type = explode('-', $id_type);
				/*Cambiado por Daniel Cabeza: añadida comprobación de arr_id_type[0] y arr_id_type[1]*/
				$id = '';
				if(isset($arr_id_type[0])){
					$id = $arr_id_type[0];
				}
				$type = '';
				if(isset($arr_id_type[1])){
					$type = $arr_id_type[1];
				}
				/*Fin del cambio*/
				
				if($id != '' && $type != ''){
					$xml .= '<skill><id>'.$id.'</id><type>'.$type.'</type></skill>';
				}
			}

			$xml .= '</skills>';

			// Get the response
			$response = $curl->post($url, $xml);
		}
		else{
			// Get all skills
			$response = $curl->get($url);
		}

		// Check response
		if ($response && $curl->getHttpCode()>=200 && $curl->getHttpCode()<400){
			if($xml = simplexml_load_string($response)){
				return $xml;
			}
			else{
				return null;
			}
		}
		else{
			print_error('Gescompeval: invalid URL');
			return null;
		}
	}

	/**
	 * Get one competency from web service
	 * @static
	 *
	 * @return string URL validated
	 */
	public static function get_competency($id){
		defined('COMPETENCY_GESC') || die('Gescompeval is not configured properly');

		$serverurl = ws_gescompeval_client::insert_id_in_url(COMPETENCY_GESC, $id);

		if(ws_gescompeval_client::check_url($serverurl)){
			return $serverurl;
		}
		else{
			print_error('Gescompeval: invalid URL');
			return false;
		}
	}

	/**
	 * Get one learning outcome from web service
	 * @static
	 *
	 * @return string URL validated
	 */
	public static function get_outcome($id){ //old get_result
		defined('OUTCOME_GESC') || die('Gescompeval is not configured properly');

		$serverurl = ws_gescompeval_client::insert_id_in_url(OUTCOME_GESC, $id);

		if(ws_gescompeval_client::check_url($serverurl)){
			return $serverurl;
		}
		else{
			print_error('Gescompeval: invalid URL');
			return false;
		}
	}

	/**
	 * Get all competencies from web service
	 * @static
	 *
	 * @return string URL validated
	 */
	public static function get_competencies(){
		defined('COMPETENCIES_GESC') || die('Gescompeval is not configured properly');

		$serverurl = COMPETENCIES_GESC;

		if(ws_gescompeval_client::check_url($serverurl)){
			return $serverurl;
		}
		else{
			print_error('Gescompeval: invalid URL');
			return false;
		}
	}

	/**
	 * Get all learning outcomes from web service
	 * @static
	 *
	 * @return string URL validated
	 */
	public static function get_outcomes(){ //old get_results
		defined('OUTCOMES_GESC') || die('Gescompeval is not configured properly');

		$serverurl = OUTCOMES_GESC;

		if(ws_gescompeval_client::check_url($serverurl)){
			return $serverurl;
		}
		else{
			print_error('Gescompeval: invalid URL');
			return false;
		}
	}

	/**
	 * @static
	 *
	 * @param string $url
	 * @return array with the data provided or null
	 */
	public static function get_data_from_url($url){
		$curl = new Curly();
		$response = $curl->get($url);

		if ($response && $curl->getHttpCode()>=200 && $curl->getHttpCode()<400){
			if($xml = simplexml_load_string($response)){
				return $xml;
			}
			else{
				return null;
			}
		}
		else{
			print_error('Gescompeval: invalid URL');
			return null;
		}
	}

	/**
	 * To get only one object, not an array of objects
	 * @static
	 * @param string $url
	 * @return stdClass instance with the data provided or null
	 */
	/*public static function get_one_object_from_url($url){
		$d = null;
		if($url){
			$url .= '.xml';
			$response = file_get_contents($url);
			if($response){
				$arr_data = simplexml_load_string($response);
			}

			foreach($arr_data as $data){
				$d = $data;
			}
		}

		return $d;
	}*/

	/**
	 * @static
	 * @param string $url file url starting with http(s)://
	 * @return int if it is OK return 1 in other case, 0
	 */
	public static function check_url($url){
		global $CFG;
		include_once($CFG->dirroot .'/blocks/gescompeval_md/model/curl.class.php');

		$curl = new Curly();
		$response = $curl->get($url);

		if ($response && $curl->getHttpCode()>=200 && $curl->getHttpCode()<400){
			return 1;
		}
		else{
			return 0;
		}
	}

	/**
	 * @param string $url
	 * @param int $id
	 * @return string that replaces the substring 'id' for $id
	 */
	public function insert_id_in_url($url, $id){
		return str_replace('ID', $id, $url);
	}
}