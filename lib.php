<?php

/**
 * Adding/Removing competencies/results from courses lib
 *
 * @package    block-gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The default size of a user selector.
 */
/*Comentario añadido por Daniel Cabeza
define('USER_SELECTOR_DEFAULT_ROWS', 20);
*/

require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/subdimension.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/skill_course_sub.php');
require_once($CFG->dirroot . '/blocks/gescompeval_md/model/ws_gescompeval_client.php');


/**
 * Base class to add skills to the course
 */
abstract class competence_selector extends user_selector_base {
	/** @var boolean Used to ensure we only output the search options for one user selector on
	 * each page. */
	private static $searchoptionsoutput = false;
	/** @var boolean When the search changes, do we keep previously selected options that do
	 * not match the new search term? */
	protected $preserveselected = false;
	/** @var boolean If only one user matches the search, should we select them automatically. */
	protected $autoselectunique = false;
	/** @var boolean When searching, do we only match the starts of fields (better performance)
	 * or do we match occurrences anywhere? */
	protected $searchanywhere = false;
	/** @var array Extra fields to search on and return in addition to firstname and lastname. */
	protected $extrafields;
	/** @var array A list of userids that should not be returned by this control. */
	protected $exclude = array();
	/** @var int identificator of the course. */
	public $courseid;


	public function __construct($name, $options, $courseid = 0) {
		parent::__construct($name, $options);
		$this->extrafields = array();
		$this->extrafields[] = 'code';
		$this->extrafields[] = 'shortdescription';
		//$this->extrafields[] = 'skilltype';
		$this->courseid = $courseid;
	}

	public function find_users($search) {}

	/**
	 * Output this user_selector as HTML. Change options search text
	 * @param boolean $return if true, return the HTML as a string instead of outputting it.
	 * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
	 */
	public function display($return = false) {
		global $PAGE;

		// Get the list of requested users.
		$search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
		if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
			$search = '';
		}
		$groupedusers = $this->find_users($search);

		// Output the select.
		$name = $this->name;
		$multiselect = '';
		if ($this->multiselect) {
			$name .= '[]';
			$multiselect = 'multiple="multiple" ';
		}
		$output = '<div class="userselector" id="' . $this->name . '_wrapper">' . "\n" .
                '<select name="' . $name . '" id="' . $this->name . '" ' .
                $multiselect . 'size="' . $this->rows . '">' . "\n";

        // Populate the select.
        $output .= $this->output_options($groupedusers, $search);

        // Output the search controls.
        $output .= "</select>\n<div>\n";
        $output .= '<input type="text" name="' . $this->name . '_searchtext" id="' .
                //$this->name . '_searchtext" size="15" value="' . s($search) . '" />';
        		$this->name . '_searchtext" size="15" value="' . s($search) . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_searchbutton" id="' .
                $this->name . '_searchbutton" value="' . $this->search_button_caption() . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_clearbutton" id="' .
                $this->name . '_clearbutton" value="' . get_string('clear') . '" />';

		// And the search options.
		$optionsoutput = false;
		if (!competence_selector::$searchoptionsoutput) {
			$output .= print_collapsible_region_start('', 'userselector_options',
					get_string('searchoptions'), 'userselector_optionscollapsed', true, true);
			//$output .= $this->option_checkbox('preserveselected', $this->preserveselected, get_string('userselectorpreserveselected', 'block_gescompeval_md'));
			$output .= $this->option_checkbox('autoselectunique', $this->autoselectunique, get_string('userselectorautoselectunique', 'block_gescompeval_md'));
			//$output .= $this->option_checkbox('searchanywhere', $this->searchanywhere, get_string('userselectorsearchanywhere', 'block_gescompeval_md'));
			$output .= print_collapsible_region_end(true);

			$PAGE->requires->js_init_call('M.core_user.init_user_selector_options_tracker', array(), false, self::$jsmodule);
			competence_selector::$searchoptionsoutput = true;
		}
		$output .= "</div>\n</div>\n\n";

		// Initialise the ajax functionality.
		$output .= $this->initialise_javascript($search);

		// Return or output it.
		if ($return) {
			return $output;
		} else {
			echo $output;
		}
	}

	/**
	 * Output one particular optgroup. Used by the preceding function output_options.
	 *
	 * @param string $groupname the label for this optgroup.
	 * @param array $users the users to put in this optgroup.
	 * @param boolean $select if true, select the users in this group.
	 * @return string HTML code.
	 */
	protected function output_optgroup($groupname, $users, $select) {
		if (!empty($users)) {
			$output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($users) . ')">' . "\n";
			foreach ($users as $user) {
				$attributes = ' style="white-space: pre-line;"';
				if (!empty($user->disabled)) {
					$attributes .= ' disabled="disabled"';
				} else if ($select || isset($this->selected[$user->id])) {
					$attributes .= ' selected="selected"';
				}
				unset($this->selected[$user->id]);
				// Insert the shortdescription into the title option
				$output .= '    <option' . $attributes . ' value="' . $user->id . '"' .
								$this->output_user($user) . "</option>\n";
				if (!empty($user->infobelow)) {
					// 'Poor man's indent' here is because CSS styles do not work
					// in select options, except in Firefox.
					$output .= '    <option disabled="disabled" class="userselector-infobelow">' .
							'&nbsp;&nbsp;&nbsp;&nbsp;' . s($user->infobelow) . '</option>';
				}
			}
		} else {
			$output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
			$output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
		}
		$output .= "  </optgroup>\n";
		return $output;
	}

	/**
     * Convert a user object to a string suitable for displaying as an option in the list box.
     *
     * @param object $user the user to display.
     * @return string a string representation of the user.
     */
    public function output_user($user) {
        //$out = fullname($user);
        /*Cambio añadido por Daniel Cabeza*/
        $out = '';
        /*Fin del cambio*/
        if ($this->extrafields) {
            $displayfields = array();
            foreach ($this->extrafields as $field) {
                $displayfields[] = $user->{$field};
            }
            //$out .= ' (' . implode(', ', $displayfields) . ')';
            $out .= implode(': ', $displayfields);
        }
        // Return the description into the title option
        $out = ' title="'.$displayfields[1].'">'.$out;
        return $out;
    }

	// Output one of the options checkboxes.
	protected function option_checkbox($name, $on, $label) {
		if ($on) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$name = 'userselector_' . $name;
		$output = '<p><input type="hidden" name="' . $name . '" value="0" />' .
				// For the benefit of brain-dead IE, the id must be different from the name of the hidden form field above.
		// It seems that document.getElementById('frog') in IE will return and element with name="frog".
		'<input type="checkbox" id="' . $name . 'id" name="' . $name . '" value="1"' . $checked . ' /> ' .
		'<label for="' . $name . 'id">' . $label . "</label></p>\n";
		user_preference_allow_ajax_update($name, PARAM_BOOL);
		return $output;
	}

	/**
	 * Get the list of users that were selected by doing optional_param then
	 * validating the result.
	 *
	 * @return array of user objects.
	 */
	protected function load_selected_users() {
		// See if we got anything.
		if ($this->multiselect) {
			$userids = optional_param_array($this->name, array(), PARAM_CLEANHTML);
		} else if ($userid = optional_param($this->name, 0, PARAM_CLEANHTML)) {
			$userids = array($userid);
		}
		// If there are no users there is nobody to load
		if (empty($userids)) {
			return array();
		}

		return $userids;
	}

	protected function get_options() {
		$options = parent::get_options();
		$options['file'] = 'blocks/gescompeval_md/competencies/lib.php';
		return $options;
	}

}

/**
 * Class for the potencial competencies and results
 */
class potential_competence_selector extends competence_selector {

	public function find_users($search) {
		$not_connected_skills = array();
		// Obtain skills from web service
		$skills = ws_gescompeval_client::get_skills();

		// Set excluded ids
		$this->set_exclude();

		// Only get not connected skills
		foreach($skills as $skill){
			$id_type = $skill->id.'-'.$skill->skilltype;
			if(!in_array($id_type, $this->exclude)){
				// If there is a search string, the code or the shortdescription must have it
				if($search == '' || (
						strpos($skill->code, $search) !== false ||
						strpos($skill->shortdescription, $search) !== false)){
					$not_connected_skills[] = $skill;
				}
			}
		}

		return array(get_string('notconnected', 'block_gescompeval_md') => $not_connected_skills);
	}

	/**
	 * Remove skills from a course
	 */
	public function remove_skill($id){

		// Delete the connection
		$skill = skill::fetch(array('id'=>$id));
		if($skill){
			$params = array('skillid' => $id, 'courseid' => $this->courseid);
			$skill_course = skill_course::fetch($params);
			if($skill_course){
				$skill_course->delete();
			}
		}
	}

	/**
	 * Set excluded ids
	 */
	public function set_exclude($return = false) {
		// Obtains elements already connected with the course to exclude them
		if($arr_skill_course = skill_course::fetch_all(array('courseid' => $this->courseid))){
			foreach($arr_skill_course as $skill_course){
				if($skill = skill::fetch(array('id' => $skill_course->get_skillid()))){
					$this->exclude[] = $skill->get_gescompevalid().'-'.$skill->get_type();
				}
			}
		}
	}

	protected function output_optgroup($groupname, $elements, $select) {
		if (!empty($elements)) {
			$output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($elements) . ')">' . "\n";
			foreach ($elements as $elem) {
				$attributes = ' style="white-space: pre-line;"';
				if (!empty($elem->disabled)) {
					$attributes .= ' disabled="disabled"';
				//} else if ($select || isset($this->selected[$elem->id])) {
				} else if ($select || in_array($elem->id, $this->selected)) {
					$attributes .= ' selected="selected"';
				}
				/*Código añadido por Daniel Cabeza: envolver el unset con in_array*/
				if(in_array($elem->id, $this->selected)){
					unset($this->selected[$elem->id]);
				}
				/*Fin del cambio*/
				// Value of the option is id-type to obtain the type later and save it in
				// DB without looking it for in the web service
				$output .= '    <option' . $attributes .
						   ' value="' . $elem->id.'-'.$elem->skilltype . '"' .
						   $this->output_user($elem) . "</option>\n";
				if (!empty($elem->infobelow)) {
				// 'Poor man's indent' here is because CSS styles do not work
				// in select options, except in Firefox.
				$output .= '    <option disabled="disabled" class="userselector-infobelow">' .
						'&nbsp;&nbsp;&nbsp;&nbsp;' . s($elem->infobelow) . '</option>';
				}
				}
				} else {
				$output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
				$output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
				}
				$output .= "  </optgroup>\n";
		return $output;
	}
}

/**
 * Connected skills with a course
 */
class current_competence_selector extends competence_selector {
	public $evxsubid;

	public function __construct($name, $options, $courseid = 0, $evxsubid = '') {
		parent::__construct($name, $options, $courseid);
		$this->evxsubid = $evxsubid;
	}

	public function find_users($search) {

		$elements = array();

		// Obtain elements connected with the course from Moodle
		$params = array('courseid' => $this->courseid);
		if($all_skill_course = skill_course::fetch_all($params)){

			// Obtain data
			$all_skill = array();
			foreach ($all_skill_course as $skill_course){
				if($skill = skill::fetch(array('id' => $skill_course->get_skillid()))){
					$all_skill[] = $skill;
				}
			}

			// Set excluded ids
			$this->set_exclude();

			// Get codes and descriptions from Web Service
			$arr_obj = skill::get_array_skill_information($all_skill);

			// Get datas excluding the skill element which are in the array of excluded ids
			foreach ($arr_obj as $obj){
			 	if(!in_array($obj->id, $this->exclude)){
					// If there is a search string, the code or the description must have it
					if($search == '' || (
							strpos($obj->code, $search) !== false ||
							strpos($obj->shortdescription, $search) !== false)){
								$elements[] = $obj;
					}
				}
			}
		}

		// If the user is connecting competence-subdimension
		if($this->evxsubid != ''){
			$nameofarray = get_string('notconnected', 'block_gescompeval_md');
		}
		// If the user is connecting competence-course
		else{
			$nameofarray = get_string('connected', 'block_gescompeval_md');
		}
		return array($nameofarray => $elements);
	}

	/**
	 * Set excluded ids
	 */
	public function set_exclude($return = false) {
		// Get subdimension moodle's id
		if($this->evxsubid != ''){
			if($subdimension = subdimension::fetch(array('evxsubid' => $this->evxsubid))){
				// Get skill elements already connected with the subdimension to exclude them
				if($arr_skill = $subdimension->get_skill_connected($this->courseid)){
					foreach($arr_skill as $skill){
						$this->exclude[] = $skill->get_id();
					}
				}
			}
		}
	}

	/**
	 * Remove skills from a subdimension
	 */
	public function remove_skill($id){

		// Delete the connection
		$skill = skill::fetch(array('id'=>$id));
		if($skill){
			$params = array('skillid' => $id, 'courseid' => $this->courseid);
			if($skill_course = skill_course::fetch($params)){
				// Get subdimension moodle's id
				if($this->evxsubid != ''){
					if($subdimension = subdimension::fetch(array('evxsubid' => $this->evxsubid))){
						$params = array('skillcourseid' => $skill_course->get_id(), 'subdimensionid' => $subdimension->get_id());
						if($skill_course_sub = skill_course_sub::fetch($params)){
							$skill_course_sub->delete();
						}
					}
				}
			}
		}
	}


	/**
	 * Add skills to a course
	 */
	public function connect_competence($selectid){

		// Separate selectid because it's in the form id-type
		$array = explode('-', $selectid);
		$gescompevalid = $array[0];
		$type = $array[1];

		// Check if the competency/outcome exists for inserting it
		$skill = skill::fetch(array('gescompevalid'=>$gescompevalid, 'type'=>$type));
		if(!$skill){
			// Insert it in Moodle's DB
			$skill = new skill('', $gescompevalid, $type);
			$skill->insert();
		}

		// Connect competency/outcome with course if it is not connected yet
		$params = array('skillid' => $skill->get_id(), 'courseid' => $this->courseid);
		if (!skill_course::fetch($params)){
			$skill_course = new skill_course('', $skill->get_id(), $this->courseid);
			$skill_course->insert();
		}
	}
}

/**
 * Selector for subdimensions of EvalCOMIX tools
 */
class subdimension_selector extends competence_selector {
	public $courseid;

	/**
	 * Output this user_selector as HTML. Change options search text
	 * @param boolean $return if true, return the HTML as a string instead of outputting it.
	 * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
	 */
	public function display($return = false) {

		$groupedusers = $this->find_users('');

		// Output the select.
		$name = $this->name;
		$this->rows = 15;

		$output = '<div class="userselector" id="' . $this->name . '_wrapper">' . "\n" .
				'<select name="' . $name . '" id="' . $this->name . '" ' .
				 'size="' . $this->rows . '">' . "\n";

		// Populate the select.
		$output .= $this->output_options($groupedusers, '');

		$output .= "</div>\n</div>\n\n";

		// Return or output it.
		if ($return) {
			return $output;
		} else {
			echo $output;
		}
	}
}

/**
 * Selector of competences of subdimensions of EvalCOMIX tools
 */
class subdimension_competence_selector extends competence_selector{
	public $evxsubid;

	public function __construct($name, $options, $courseid = 0, $evxsubid = '') {
		parent::__construct($name, $options, $courseid);
		$this->evxsubid = $evxsubid;

	}

	public function find_users($search) {
		$elements = array();

		// Get subdimension moodle's id
		if($subdimension = subdimension::fetch(array('evxsubid' => $this->evxsubid))){
			// Get skill elements already connected with the subdimension to exclude them
			if($arr_skill = $subdimension->get_skill_connected($this->courseid)){

				// Get codes and descriptions from Web Service
				$arr_obj = skill::get_array_skill_information($arr_skill);

				// Get datas excluding the skill element which are in the array of excluded ids
				foreach ($arr_obj as $obj){
					// If there is a search string, the code or the shortdescription must have it
					if($search == '' || (
							strpos($obj->code, $search) !== false ||
							strpos($obj->shortdescription, $search) !== false)){
						$elements[] = $obj;
					}
				}
			}
		}

		return array(get_string('connected', 'block_gescompeval_md') => $elements);
	}

	/**
	 * Connect skills with a subdimension
	 */
	public function connect_competence($skillid, $toolid){

		// Check if the subdimension exists for inserting it
		$subdimension = subdimension::fetch(array('evxsubid'=>$this->evxsubid));
		if(!$subdimension){
			// Insert it in Moodle's DB
			$subdimension = new subdimension('', $this->evxsubid, $toolid);
			$subdimension->insert();
		}
		$subdimensionid = $subdimension->get_id();

		if($subdimensionid){
			// Get skill_course according to the skill and the course
			$params = array('skillid' => $skillid, 'courseid' => $this->courseid);
			if ($skill_course = skill_course::fetch($params)){
				// Connect competency/result with subdimension if it is not connected yet
				$params = array('skillcourseid' => $skill_course->get_id(), 'subdimensionid' => $subdimensionid);
				if (!skill_course_sub::fetch($params)){
					$skill_course_sub = new skill_course_sub('', $skill_course->get_id(), $subdimensionid);
					$skill_course_sub->insert();
				}
			}
		}
	}
}

