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
 * Serves an encrypted/unencrypted string as a file for download.
 *
 * @package    quizaccess_sebprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_sebprogram;

use core\persistent;
use rest;

class program extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'quiz_seb_program';

    /** @var property_list $plist The SEB config represented as a Property List object. */
    private $plistprogram;

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

        if ($resultprogramquiz ) {
            return true;
        } else {
            return false;
        }
    }


    public static function get_records_course($courseid = [], $sort = '', $returnrecords = false) {
        global $DB;

        $sql = <<<END
        SELECT * FROM mdl_quiz_seb_program
        WHERE mdl_quiz_seb_program.display = 1 AND
        (mdl_quiz_seb_program.courseid = -1 OR mdl_quiz_seb_program.courseid = $courseid);
        END;

        $records = $DB->get_records_sql($sql);

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

    public static function get_records_generic_dependency($programid = 0) {
        global $DB;

        $sql = <<<END
        SELECT * FROM mdl_quiz_seb_program JOIN mdl_quiz_seb_program_dependecy
        ON mdl_quiz_seb_program.id = mdl_quiz_seb_program_dependecy.idprogram_dependency
        WHERE mdl_quiz_seb_program_dependecy.idprogram = $programid ;
        END;

        $results = $DB->get_records_sql($sql);

        return $results;
    }

}
