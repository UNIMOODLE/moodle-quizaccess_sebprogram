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
 * Backup code for the quizaccess_sebprogram plugin.
 *
 * @package   quizaccess_sebprogram
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_mod_quiz_access_subplugin.class.php');


/**
 * Provides the information to backup the honestycheck quiz access plugin.
 *
 * If this plugin is requires, a single
 * <quizaccess_sebprogram><required>1</required></quizaccess_sebprogram> tag
 * will be added to the XML in the appropriate place. Otherwise nothing will be
 * added. This matches the DB structure.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_quizaccess_sebprogram_subplugin extends backup_mod_quiz_access_subplugin {

    protected function define_quiz_subplugin_structure() {
        // sebprogram dependency child sebprogram, 2 ids con sql set source sql.
        parent::define_quiz_subplugin_structure();
        $quizid = backup::VAR_ACTIVITYID;

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Programs.
        $subpluginprograms = new \quizaccess_sebprogram\program();
        $blankprogramsarray = (array) $subpluginprograms->to_record();
        $programskeys = array_keys($blankprogramsarray);

        $subpluginprogramssettings = new backup_nested_element('quizaccess_seb_program', null, $programskeys);

        // Quiz programs.
        $subpluginquizprograms = new \quizaccess_sebprogram\program_quiz();
        $blankquizprogramsarray = (array) $subpluginquizprograms->to_record();
        $quizprogramskeys = array_keys($blankquizprogramsarray);

        $subpluginquizprogramssettings = new backup_nested_element('quizaccess_seb_program_quiz', null, $quizprogramskeys);

        // Quiz dependendecies.
        $subplugindependencies = new \quizaccess_sebprogram\program_dependency();
        $blankdependenciesarray = (array) $subplugindependencies->to_record();
        $dependencieskeys = array_keys($blankdependenciesarray);

        $subplugindependenciessettings = new backup_nested_element('quizaccess_sebprogram_depend', null, $dependencieskeys);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginprogramssettings);
        $subpluginwrapper->add_child($subpluginquizprogramssettings);
        $subpluginwrapper->add_child($subplugindependenciessettings);

        // Set source to populate the data.
        $subpluginprogramssettings->set_source_table(\quizaccess_sebprogram\program::TABLE,
            ['courseid' => backup::VAR_COURSEID]);
        $subpluginquizprogramssettings->set_source_table(\quizaccess_sebprogram\program_quiz::TABLE,
        ['idquiz' => $quizid]);
        $subplugindependenciessettings->set_source_table(\quizaccess_sebprogram\program_dependency::TABLE, []);
        $subplugindependenciessettings->annotate_ids('idprogram', 'idprogram');
        $subplugindependenciessettings->annotate_ids('idprogram_dependency', 'idprogram_dependency');

        return $subplugin;
    }
}
