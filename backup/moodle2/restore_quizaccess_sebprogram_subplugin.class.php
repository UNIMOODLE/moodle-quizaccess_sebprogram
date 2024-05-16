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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    quizaccess_sebprogram
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');


/**
 * Provides the information to restore the honestycheck quiz access plugin.
 *
 * If this plugin is required, a single
 * <quizaccess_sebprogram><required>1</required></quizaccess_sebprogram> tag
 * will be in the XML, and this needs to be written to the DB. Otherwise, nothing
 * needs to be written to the DB.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizaccess_sebprogram_subplugin extends restore_mod_quiz_access_subplugin {

    /**
     * A method to define the structure of the quiz subplugin.
     *
     * @return array
     */
    protected function define_quiz_subplugin_structure() {

        $paths = [];

        $elepath = $this->get_pathfor('/quizaccess_seb_program');
        $paths[] = new restore_path_element("quizaccess_sebprogram", $elepath);

        $elepath = $this->get_pathfor('/quizaccess_seb_program_quiz');
        $paths[] = new restore_path_element("quizaccess_program_quiz", $elepath);

        $elepath = $this->get_pathfor('/quizaccess_sebprogram_depend');
        $paths[] = new restore_path_element("quizaccess_program_depend", $elepath);

        return $paths;
    }

    /**
     * Processes the quizaccess_sebprogram element, if it is in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_quizaccess_sebprogram($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->courseid = $this->step->get_task()->get_courseid();
        // If its site level program we don't need to restore it.
        if ($data->courseid != -1) {
            // Check that the program hasn't been restored in this course already to not duplicate it.
            if (!$DB->get_record_select('quizaccess_seb_program',
                    'courseid = ? AND ' . $DB->sql_compare_text('title') . ' = ?' ,
                    [$data->courseid, $data->title], '*')) {
                $newitemid = $DB->insert_record('quizaccess_seb_program', $data);
                $this->set_mapping('quizaccess_seb_program', $oldid, $newitemid);
            } else {
                $newitemid = $DB->get_record_select('quizaccess_seb_program',
                    'courseid = ? AND ' . $DB->sql_compare_text('title') . ' = ?' ,
                    [$data->courseid, $data->title], '*')->id;
                $this->set_mapping('quizaccess_seb_program', $oldid, $newitemid);
            }
        } else {
            // In case that the restore is made on another site, or that the programs were deleted.
            if (!$DB->get_record_select('quizaccess_seb_program',
                    'courseid = ? AND ' . $DB->sql_compare_text('title') . ' = ?' ,
                    [-1, $data->title], '*')) {
                    $newitemid = $DB->insert_record('quizaccess_seb_program', $data);
                    $this->set_mapping('quizaccess_seb_program', $oldid, $newitemid);
            }
        }
    }


    public function process_quizaccess_program_quiz($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $oldprogramid = $data->idprogram;
        $data->idquiz = $this->get_new_parentid('quiz');
        // We get the program id from the current course.
        $idprogram = $this->get_mapping('quizaccess_seb_program', $data->idprogram)->newitemid;
        // If the progrma dont exist in the course, it's a site level program so we just get it back from the data.
        if (!$idprogram) {
            $idprogram = $oldprogramid;
        }
        $data->idprogram = $idprogram;
        $newitemid = $DB->insert_record('quizaccess_seb_program_quiz', $data);
    }

    // Add dependency table
    public function process_quizaccess_program_depend($data) {
        global $DB;

        $data = (object)$data;
        $program = $this->get_mapping('quizaccess_seb_program', $data->idprogram);
        // If the program is mapped (not site program) we restore it with the new references.
        if ($program) {
            $data->idprogram = $program->newitemid;
            $data->idprogram_dependency = $this->get_mapping('quizaccess_seb_program', $data->idprogram_dependency)->newitemid;
            // We make sure that the dependency has not been restored already in this course to not duplicate it.
            if (!$DB->get_record('quizaccess_sebprogram_depend', ['idprogram' => $data->idprogram,
                    'idprogram_dependency' => $data->idprogram_dependency, ], '*', IGNORE_MISSING)) {
                $DB->insert_record('quizaccess_sebprogram_depend', $data);
            }
        }
    }
}
