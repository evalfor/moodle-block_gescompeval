<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Section links block
 *
 * @package    gescompeval
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {
	$settings->add(new admin_setting_heading('gescompeval_heading', get_string('adminheader', 'block_gescompeval_md'),
			get_string('admindescription', 'block_gescompeval_md')));

	// Server URL
	$settings->add(new admin_setting_configtext('gescompeval_serverurl', get_string('serverurl', 'block_gescompeval_md'),
			get_string('serverurlinfo', 'block_gescompeval_md'), ''));


	// Check if EvalCOMIX_MD is installed
	if(!$DB->get_record('block', array('name'=>'evalcomix'))){
		$settings->add(new admin_setting_heading('warning_heading', get_string('warning', 'block_gescompeval_md'),
			get_string('warningevalcomix', 'block_gescompeval_md')));
	}

	// TODO: Add a validation button


}