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

namespace quizaccess_sebprogram;

 use CFPropertyList\CFPropertyList;
 use CFPropertyList\CFArray;
 use CFPropertyList\CFBoolean;
 use CFPropertyList\CFData;
 use CFPropertyList\CFDate;
 use CFPropertyList\CFDictionary;
 use CFPropertyList\CFNumber;
 use CFPropertyList\CFString;
 use CFPropertyList\CFType;
 use quizaccess_seb\property_list;
 use quizaccess_seb\quiz_settings;

/**
 * Helper class.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Get a filler icon for display in the actions column of a table.
     *
     * @param string $url The URL for the icon.
     * @param string $icon The icon identifier.
     * @param string $alt The alt text for the icon.
     * @param string $iconcomponent The icon component.
     * @param array $options Display options.
     * @return string
     */
    public static function format_icon_link($url, $icon, $alt, $iconcomponent = 'moodle', $options = []) {
        global $OUTPUT;

        return $OUTPUT->action_icon(
            $url,
            new \pix_icon($icon, $alt, $iconcomponent, [
                'title' => $alt,
            ]),
            null,
            $options
        );
    }

    /**
     * Validate seb config string.
     *
     * @param string $sebconfig
     * @return bool
     */
    public static function is_valid_seb_config(string $sebconfig) : bool {
        $result = true;

        set_error_handler(function($errno, $errstr, $errfile, $errline ){
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        $plist = new CFPropertyList();
        try {
            $plist->parse($sebconfig);
        } catch (\ErrorException $e) {
            $result = false;
        } catch (\Exception $e) {
            $result = false;
        }

        restore_error_handler();

        return $result;
    }

    /**
     * Get seb config content for a particular quiz. This method checks caps.
     *
     * @param string $cmid The course module ID for a quiz with config.
     * @return string SEB config string.
     */
    public static function get_seb_config_content(string $cmid) : string {
        // Try and get the course module.
        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);

        // Make sure the user is logged in and has access to the module.
        require_login($cm->course, false, $cm);

        // Retrieve the config for quiz.
        $config = quiz_settings::get_config_by_quiz_id($cm->instance);
        if (empty($config)) {
            throw new \moodle_exception('noconfigfound', 'quizaccess_sebprogram', '', $cm->id);
        }

        $config = self::attach_extra_config_content($config, $cm->instance);

        return $config;
    }

    /**
     * Attach extra elements to seb config.
     *
     * @param string actual config
     * @param string quiz id
     */
    private static function attach_extra_config_content($config, $idquiz) {

        global $DB;

        $sql = <<<END
        SELECT title, executable, originalname, path, display
        FROM {quizaccess_seb_program}
        INNER JOIN {quizaccess_seb_program_quiz} ON {quizaccess_seb_program}.id = {quizaccess_seb_program_quiz}.idprogram
        WHERE {quizaccess_seb_program_quiz}.idquiz = :idquiz
        END;

        $records = $DB->get_records_sql($sql, ['idquiz' => $idquiz]);

        $plist = new property_list($config);

        $entries = [];
        foreach ($records as $record) {
            $entry = new CFDictionary([
                'title' => new CFString($record->title),
                'executable' => new CFString($record->executable),
                'originalName' => new CFString($record->originalname),
                'path' => new CFString($record->path),
                'display' => new CFNumber($record->display),
            ]);

            $entries[] = $entry;
        }

        $plist->add_element_to_root('permittedProcesses', new CFArray($entries));

        $config = $plist->to_xml();

        return $config;
    }

}

