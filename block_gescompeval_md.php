<?php

/**
 * @package    gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_gescompeval_md extends block_base {
    public function init() {
        $this->title = get_string('gescompeval', 'block_gescompeval_md');
    }

	public function get_content() {
    	if ($this->content !== null) {
			return $this->content;
    	}

    	global $COURSE;
    	global $CFG;
    	global $USER;

    	/*Cambio añadido por Daniel Cabeza*/
//     	$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
		$coursecontext = context_course::instance($COURSE->id, MUST_EXIST);
		/*Fin del cambio*/
		$this->content = new stdClass;
		$this->content->text = '';
		$this->content->footer = '';

		$addremovecompetencies = get_string('managementcompetencies', 'block_gescompeval_md');
		$connectcompetencies = get_string('managementsubdimensions', 'block_gescompeval_md');
		$getreports = get_string('getreports', 'block_gescompeval_md');

		$this->content->text .= "<img src='".$CFG->wwwroot ."/blocks/gescompeval_md/images/logo3.png' alt='' align='absmiddle' width='100%' style='padding-top:0.6em;'>";

		// Checking access
		if(!is_siteadmin($USER) && !has_capability('block/evalcomix:edit',$coursecontext)){
			$items[0] = html_writer::tag('a', $getreports, array('href' => new moodle_url('/blocks/gescompeval_md/reports/index.php', array('courseid' => $COURSE->id)), 'style' => "padding: 0px;"));
		}
		else{
			$items[0] = html_writer::tag('a', $addremovecompetencies, array('href' => new moodle_url('/blocks/gescompeval_md/competencies/manage.php', array('courseid' => $COURSE->id)), 'style' => "padding: 0px;"));
			$items[1] = html_writer::tag('a', $connectcompetencies, array('href' => new moodle_url('/blocks/gescompeval_md/subdimensions/selectsub.php', array('courseid' => $COURSE->id)), 'style' => "padding: 0px;"));
			$items[2] = html_writer::tag('a', $getreports, array('href' => new moodle_url('/blocks/gescompeval_md/reports/index.php', array('courseid' => $COURSE->id)), 'style' => "padding: 0px;"));
		}

		$this->content->text .= html_writer::alist($items, array('style' => 'padding: 0px; margin-left: 21%;color:#00648C;font-weight:bold;'));

		$content = html_writer::tag('span', get_string('poweredby', 'block_gescompeval_md'), array('style' => "color:#E67300; font-size:8pt"));
		$this->content->footer .= html_writer::tag('div', $content, array('style' => "text-align:center"));

		return $this->content;
  	}

  	function instance_allow_config() {
  		return true;
  	}

  	function has_config() {
  		return true;
  	}

}   // Here's the closing bracket for the class definition

