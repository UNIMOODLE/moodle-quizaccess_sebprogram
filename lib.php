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

use quizaccess_sebprogram\event\access_prevented;
use quizaccess_sebprogram\access_manager;

defined('MOODLE_INTERNAL') || die();

    /**
     * Overrides the web service execution for the quizaccess_sebprogram plugin.
     *
     * @param object $function The function being executed.
     * @param array $params The parameters passed to the function.
     * @throws invalid_parameter_exception If no key is provided or the quiz does not exist.
     * @return array The result of the function execution.
     */
function quizaccess_sebprogram_override_webservice_execution($function, $params) {

    // Check if it's the function we want to override.
    if ($function->name === 'quizaccess_seb_validate_quiz_keys') {
            $cmid = $params[0];
            $url = $params[1];
            $configkey = $params[2];
            $browserexamkey = $params[3];

        \external_api::validate_context(\context_module::instance($cmid));

        // At least one SEB key must be provided.
        if (empty($configkey) && empty($browserexamkey)) {
            throw new invalid_parameter_exception(get_string('error:ws:nokeyprovided', 'quizaccess_seb'));
        }

        // Check quiz exists corresponding to cmid.
        if (($quizid = get_quiz_id($cmid)) === 0) {
            throw new invalid_parameter_exception(get_string('error:ws:quiznotexists', 'quizaccess_seb', $cmid));
        }

        $result = ['configkey' => true, 'browserexamkey' => true];

        $accessmanager = new access_manager(quiz::create($quizid));

        // Check if there is a valid config key.
        if (!$accessmanager->validate_config_key($configkey, $url)) {
            access_prevented::create_strict($accessmanager, get_string('invalid_config_key', 'quizaccess_seb'),
                    $configkey, $browserexamkey)->trigger();
            $result['configkey'] = false;
        }

        // Check if there is a valid browser exam key.
        if (!$accessmanager->validate_browser_exam_key($browserexamkey, $url)) {
            access_prevented::create_strict($accessmanager, get_string('invalid_browser_key', 'quizaccess_seb'),
                    $configkey, $browserexamkey)->trigger();
            $result['browserexamkey'] = false;
        }

        if ($result['configkey'] && $result['browserexamkey']) {
            // Set the state of the access for this Moodle session.
            $accessmanager->set_session_access(true);
        }
        return $result;

    }
    return false;
}

/**
 * Retrieve the quiz ID associated with a given course module ID.
 *
 * @param int $cmid The course module ID to retrieve the quiz ID for.
 * @return int|null The quiz ID if the course module is a quiz, otherwise null.
 */
function get_quiz_id($cmid) {

    $coursemodule = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);

    if ($coursemodule->modname == 'quiz') {

        return $coursemodule->instance;
    }
}
