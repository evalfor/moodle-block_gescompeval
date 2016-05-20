<?php

/**
 * @package    block_gescompeval_md
 * @copyright  2010 onwards EVALfor Research Group {@link http://evalfor.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Daniel Cabeza Sánchez <daniel.cabeza@uca.es>
 */
 
require_once($CFG->dirroot . '/blocks/gescompeval_md/backup/moodle2/restore_gescompeval_md_stepslib.php'); // We have structure steps

class restore_gescompeval_md_block_task extends restore_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        // rss_client has one structure step
        $this->add_step(new restore_gescompeval_md_block_structure_step('gescompeval_md_structure', 'gescompeval_md.xml'));
    }

    public function get_fileareas() {
        return array(); // No associated fileareas
    }

    public function get_configdata_encoded_attributes() {
        return array(); // No special handling of configdata
    }

    static public function define_decode_contents() {
	return array();
    }

    static public function define_decode_rules() {
	  return array();
    }
}

