<?php

global $CFG;
include_once($CFG->dirroot . '/blocks/evalcomix/configeval.php');
include_once($CFG->dirroot . '/blocks/gescompeval_md/confgescompeval.php');
include_once($CFG->dirroot .'/blocks/gescompeval_md/model/curl.class.php');
include_once($CFG->dirroot .'/blocks/gescompeval_md/model/subdimension.php');

/**
 * Class for using EvalCOMIX API
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ws_evalcomix_client {

	/**
	 * @param $toolid attribute of an evalcomix_tool object
	 * @return xml with a tool's schema from EvalCOMIX Web service, in other case false
	 */
	public static function get_tool($toolid){
		global $CFG;
		defined('GET_TOOL_ASSESSED') || die('EvalCOMIX is not configured');

		$serverurl_aux = GET_TOOL_ASSESSED;
		$serverurl = $serverurl_aux . '?tool='. $toolid . '&format=xml';

		$curl = new Curly();
		$response = $curl->get($serverurl);

		if ($response && $curl->getHttpCode()>=200 && $curl->getHttpCode()<400){
			if($xml = simplexml_load_string($response)){
				return $xml;
			}
		}

		return false;
	}

	/**
	 * @param $toolid attribute of an evalcomix_tool object
	 * @return array with a tool's dimensions and subdimensions from EvalCOMIX Web service,
	 *  in other case false
	 */
	public static function get_dimensions($toolid){

		$xml = ws_evalcomix_client::get_tool($toolid);

		if($xml){
		$dimensions = array();

		foreach($xml as $key => $value){
			if($key == 'Dimension'){
				$subdimensions = array();
				$dimname = (string)$value['name'];
				foreach($value as $key2 => $value2){
					if($key2 == 'Subdimension'){
						$id = (string)$value2['id'];
						$name = (string)$value2['name'];
						$subdimensions[$id] = $name;
					}
				}
				$dimensions[$dimname] = $subdimensions;
			}
		}
		return $dimensions;
		}
		else{
			return false;
		}
	}

	/**
	 * @param $toolid attribute of an evalcomix_tool object
	 * @return array with a tool's dimensions and subdimensions from EvalCOMIX Web service,
	 *  in other case false
	 */
	public static function get_tools_mixed($toolid){

		$xml = ws_evalcomix_client::get_tool($toolid);

		if($xml){//print_r($xml);

			$tools = array();

			foreach($xml as $key => $tool){
				if($key == 'SemanticDifferential'){
					$attributes = array();
					$toolname = (string)$tool['name'];

					foreach($tool as $toolkey => $value){
						if($toolkey == 'Attribute'){
							$name = (string)$value['nameN'].' - '.(string)$value['nameP'];
							$id = (string)$value['idNeg'].'-'.(string)$value['idPos'];;
							$attributes[$id] = $name;
						}
					}
					$tools[] = array($toolname => $attributes);
				}
				elseif($key != 'SemanticDifferential'){
					$dimensions = array();
					$toolname = $tool['name'];

					foreach($tool as $toolkey => $value){
						if($toolkey == 'Dimension'){
							$subdimensions = array();
							$dimname = $toolname. ' > ' .(string)$value['name'];
							foreach($value as $toolkey2 => $value2){
								if($toolkey2 == 'Subdimension'){
									$id = (string)$value2['id'];
									$name = (string)$value2['name'];
									$subdimensions[$id] = $name;
								}
							}
							$dimensions[$dimname] = $subdimensions;
						}
					}
					$tools[] = $dimensions;
				}
			}

			return $tools;
		}
		else{
			return false;
		}
	}

	/**
	 * @param $toolid attribute of an evalcomix_tool object which must be a differential
	 * @return array with a the attributes of the tool
	 *  in other case false
	 */
	public static function get_attributes_differential($toolid){

		$xml = ws_evalcomix_client::get_tool($toolid);

		if($xml){
			$attributes = array();

			foreach($xml as $key => $value){
				if($key == 'Attribute'){
					$name = (string)$value['nameN'].' - '.(string)$value['nameP'];
					$id = (string)$value['idNeg'].'-'.(string)$value['idPos'];;
					$attributes[$id] = $name;
				}
			}
			return $attributes;
		}
		else{
			return false;
		}
	}

	/**
	 * @param $params is an array of stdClass objects with the following fields:
	 * - toolid
	 * - subdimensionid
	 * - assessmentid
	 *
	 * @return array of stdClass objects with subdimensionid and its grade, in other case false
	 */
	public static function get_subdimensions_grades($params = false){

		if($params){

			$subdimensions_grades = array();

			// Get subdimension's grades from EvalCOMIX WS
			if ($arr_subdimension_grades = ws_evalcomix_client::get_ws_subdimensions_grades($params)){

				foreach($arr_subdimension_grades as $sub_grade){
					$object = new stdClass();
					$object->evxsubdimensionid = (string)$sub_grade->subdimensionid;
					// Get Subdimension Id in Moodle
					$param = array('evxsubid' => $object->evxsubdimensionid);

					if($subdimension = subdimension::fetch($param)){
						$object->mdlsubdimensionid = $subdimension->get_id();
					}

					$object->grade = (string)$sub_grade->grade;
					$subdimensions_grades[] = $object;
				}

				return $subdimensions_grades;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	/**
	 * @param $params is an array of stdClass objects with the following fields:
	 * - toolid
	 * - subdimensionid
	 * - assessmentid
	 *
	 * @return array of stdClass objects with subdimensionid and its grade, in other case false
	 */
	public static function get_ws_subdimensions_grades($params = false){

		if($params){

			global $CFG;
			defined('GET_SUBDIMENSIONS_GRADES') || die('EvalCOMIX or Gescompeval are not configured');

			$serverurl = GET_SUBDIMENSIONS_GRADES. '?format=xml';

			// Create xml to send it by POST
			$xml = '<?xml version="1.0" encoding="utf-8"?>'.
					'<subdimensionassessments>';

			foreach($params as $param){
				$xml .= '<subdimensionassessment>'.
							'<toolid>'.$param->toolid.'</toolid>'.
							'<subdimensionid>'.$param->subdimensionid.'</subdimensionid>'.
							'<assessmentid>'.$param->assessmentid.'</assessmentid>'.
						'</subdimensionassessment>';
			}

			$xml .= '</subdimensionassessments>';

			// Get the response
			$curl = new Curly();
			$response = $curl->post($serverurl, $xml);
//echo $response;
			if ($response && $curl->getHttpCode()>=200 && $curl->getHttpCode()<400){
				if($xml = simplexml_load_string($response)){
					return $xml;
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	/*public static function do_post_request($url, $data, $optional_headers = null)
	{
		$params = array('http' => array(
				'method' => 'POST',
				'content' => $data
		));
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}*/
}