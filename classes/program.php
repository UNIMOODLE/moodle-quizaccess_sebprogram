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

namespace quizaccess_sebprogram;

use core\persistent;
use rest;
/**
 * A persistent class to store the SEB program details.
 */
class program extends persistent {


    /** Table name for the persistent. */
    const TABLE = 'quizaccess_seb_program';

    /** @var property_list $plist The SEB config represented as a Property List object. */
    private $plistprogram;

    /**
     * The id of the course this program belongs to.
     *
     * @var int
     */
    private $idcourse;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'title' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'executable' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'originalname' => [
                'type' => PARAM_TEXT,
            ],
            'numberofuses' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'path' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'display' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    /**
     * Validate programs content.
     *
     * @param string $content Content string to validate.
     *
     * @return bool|\lang_string
     */
    protected function validate_content(string $content) {
        if (helper::is_valid_seb_config($content)) {
            return true;
        } else {
            return new \lang_string('invalidprogram', 'quizaccess_sebprogram');
        }
        echo($content);
    }

    /**
     * Check if we can delete the programs.
     *
     * @return bool
     */
    public function can_delete() : bool {
        $resultprogramoriginal = true;
        $resultprogramdependency = true;
        $resultprogramquiz = true;

        if ($this->get('id')) {
            $programoriginal = program_dependency::get_records(['idprogram' => $this->get('id')]);
            $programdependency = program_dependency::get_records(['idprogram_dependency' => $this->get('id')]);
            $programandquiz = program_quiz::get_records(['idprogram' => $this->get('id')]);

            $resultprogramoriginal = empty($programoriginal);
            $resultprogramdependency = empty($programdependency);
            $resultprogramquiz = empty($programandquiz);
        }

        if ($resultprogramoriginal && $resultprogramdependency && $resultprogramquiz) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get records for a specific course.
     *
     * @param array $courseid
     * @param string $sort
     * @param bool $returnrecords
     * @return array
     */
    public static function get_records_course($courseid = [], $sort = '', $returnrecords = false) {
        global $DB;

        $sql = "SELECT *
                  FROM {quizaccess_seb_program} sp
                 WHERE sp.display = 1
                       AND (sp.courseid = -1 OR sp.courseid = :courseid)";

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if ($returnrecords) {
            return $records;
        }

        $instances = [];

        foreach ($records as $record) {
            $newrecord = new static(0, $record);
            array_push($instances, $newrecord);
        }
        return $instances;
    }

    /**
     * Retrieve records based on a generic dependency for a given program ID.
     *
     * @param int $programid The ID of the program
     * @return array|null The records based on the generic dependency for the given program ID
     */
    public static function get_records_generic_dependency($programid = 0) {
        global $DB;

        $sql = "SELECT *
                  FROM {quizaccess_seb_program} sp
                  JOIN {quizaccess_sebprogram_depend} sd
                    ON sp.id = sd.idprogram_dependency
                 WHERE sd.idprogram = :programid";

        $results = $DB->get_records_sql($sql, ['programid' => $programid]);

        return $results;
    }

}
