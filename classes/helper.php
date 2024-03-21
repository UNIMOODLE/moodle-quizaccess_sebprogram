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
 use quizaccess_sebprogram\quiz_settings;

/**
 * Helper class.
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
        global $DB;

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
     * Attach extra config content to the given config based on the quiz ID.
     *
     * @param mixed $config description
     * @param mixed $idquiz description
     * @return mixed
     */
    private static function attach_extra_config_content($config, $idquiz) {

        global $DB;

        $sql = "SELECT sp.title, sp.executable, sp.originalname, sp.path, sp.display
                  FROM {quizaccess_seb_program} sp
                  JOIN {quizaccess_seb_program_quiz} sq ON sp.id = sq.idprogram
                 WHERE sq.idquiz = :idquiz";

        $records = $DB->get_records_sql($sql, ['idquiz' => $idquiz]);

        $plist = new property_list($config);

        $entries = [];
        foreach ($records as $record) {
            $entry = new CFDictionary([
                'active' => new CFBoolean("true"),
                'iconInTaskbar' => new CFBoolean("true"),
                'os' => new CFNumber(1),
                'title' => new CFString($record->title),
                'executable' => new CFString($record->executable),
                'originalName' => new CFString($record->originalname),
                'path' => new CFString($record->path),
            ]);

            $entries[] = $entry;
        }

        $plist->add_element_to_root('permittedProcesses', new CFArray($entries));

        $config = $plist->to_xml();

        return $config;
    }
    /**
     * A helper function to get a list of seb config file headers.
     *
     * @param int|null $expiretime  Unix timestamp
     * @return array
     */
    public static function get_seb_file_headers(int $expiretime = null) : array {
        if (is_null($expiretime)) {
            $expiretime = time();
        }
        $headers = [];
        $headers[] = 'Cache-Control: private, max-age=1, no-transform';
        $headers[] = 'Expires: '. gmdate('D, d M Y H:i:s', $expiretime) .' GMT';
        $headers[] = 'Pragma: no-cache';
        $headers[] = 'Content-Disposition: attachment; filename=config.seb';
        $headers[] = 'Content-Type: application/seb';

        return $headers;
    }
    /**
     * Serve a file to browser for download.
     *
     * @param string $contents Contents of file.
     */
    public static function send_seb_config_file(string $contents) {
        // We can now send the file back to the browser.
        foreach (self::get_seb_file_headers() as $header) {
            header($header);
        }

        echo($contents);
    }


}

