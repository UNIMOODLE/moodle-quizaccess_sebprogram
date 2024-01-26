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
 * Implementaton of the quizaccess_sebprogram plugin.
 *
 * @package   quizaccess_sebprogram
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use quizaccess_sebprogram\program;
use quizaccess_sebprogram\program_quiz;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/seb/classes/access_manager.php');

use quizaccess_seb\access_manager;
use quizaccess_seb\settings_provider;

/**
 * A rule requiring the student to promise not to cheat.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_sebprogram extends quiz_access_rule_base {

    /**
     * Return an appropriately configured instance of this rule, if it is applicable
     * to the given quiz, otherwise return null.
     * @param quiz $quizobj information about the quiz in question.
     * @param int $timenow the time that should be considered as 'now'.
     * @param bool $canignoretimelimits whether the current user is exempt from
     *      time limits by the mod/quiz:ignoretimelimits capability.
     * @return quiz_access_rule_base|null the rule, if applicable, else null.
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        $accessmanager = new access_manager($quizobj);
        // If Safe Exam Browser is not required, this access rule is not applicable.
        if (!$accessmanager->seb_required()) {
            return null;
        }
        return new self($quizobj, $timenow, $accessmanager);
    }

    public function get_link(bool $seb = false, bool $secure = true) {

        $url = new moodle_url('/mod/quiz/accessrule/sebprogram/config.php?cmid='. $this->quiz->cmid);

        if ($seb) {
            $secure ? $url->set_scheme('sebs') : $url->set_scheme('seb');
        } else {
            $secure ? $url->set_scheme('https') : $url->set_scheme('http');
        }
        return $url->out();
    }

    /**
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     * @return mixed a message, or array of messages, explaining the restriction
     *         (may be '' if no message is appropriate).
     */
    public function description() {

        $url = $this->get_link(false, is_https());

        $httlink = $this->get_link(false, is_https());
        $seblink = $this->get_link(true, is_https());

        $content = <<<END
        window.addEventListener("load", replacehref);
        function replacehref() {
            const div_lst = document.getElementsByClassName("singlebutton");
            for (let i = 0; i < div_lst.length; i ++) {
                const a_lst = div_lst[i].getElementsByTagName("a");
                for (let j = 0; j < a_lst.length; j ++) {
                    if (a_lst[j].href.startsWith("http")) {
                        a_lst[j].href = "$httlink";
                    } else if (a_lst[j].href.startsWith("seb")) {
                        a_lst[j].href = "$seblink";
                    } else {
                    }
                }
            }
        }
        END;
        $html = html_writer::tag('script', $content);

        return [$html];

    }

    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
                global $DB, $PAGE;
        $idquiz = $quizform->get_instance();
        $idcurse = $quizform->get_coursemodule() ? $quizform->get_coursemodule()->course : optional_param('course', -1, PARAM_INT);

        $mform->addElement('header', 'sebprogramheader_my', get_string('pluginname', 'quizaccess_sebprogram'));

        $currenturl = $PAGE->url;
        // Not needed at the moment 'session_start();'.
        $_SESSION['urleditquiz'] = $currenturl;
        if (has_capability('quizaccess/sebprogram:manageprograms',  context_course::instance($idcurse))) {
            $mform->addElement('button', 'seb_program_button_admin_programs_course',
                '<a href="'. new moodle_url("/mod/quiz/accessrule/sebprogram/view_course.php",
                    ['course' => $idcurse]).'">'. get_string('managetemplates', 'quizaccess_sebprogram') . '</a>');
        }

        $recordprograms = program::get_records_course($idcurse, 'id', true);
        $programlist = [];
        foreach ($recordprograms as $record) {
            $programlist[$record->id] = $record->title;
        }
        $mform->addElement('autocomplete', 'seb_program_autocomplete_program_quiz', 'Programs', $programlist, ['multiple' => true]);
        $mform->setType('seb_program_autocomplete_program_quiz', PARAM_RAW);

        // Si es un editar obtener los programas a seleccionar.
        if ($idquiz > 0) {
            $recordsprogramselect = $DB->get_records('quizaccess_seb_program_quiz', ['idquiz' => $idquiz]);
            $programselectlist = [];
            foreach ($recordsprogramselect as $record) {
                array_push($programselectlist, $record->idprogram);
            }
            $mform->getElement('seb_program_autocomplete_program_quiz')->setValue($programselectlist);
        }

        if ($mform->elementExists("security")) {
                $mform->removeElement("sebprogramheader_my", false);
            if (has_capability('quizaccess/sebprogram:manageprograms', context_course::instance($idcurse))) {
                $mform->insertElementBefore($mform->removeElement("seb_program_button_admin_programs_course", false), 'security');
                $mform->hideIf("seb_program_button_admin_programs_course", "seb_requiresafeexambrowser", "noteq",
                    settings_provider::USE_SEB_CONFIG_MANUALLY);
            }

                $mform->insertElementBefore($mform->removeElement("seb_program_autocomplete_program_quiz", false), 'security');
                $mform->hideIf("seb_program_autocomplete_program_quiz", "seb_requiresafeexambrowser", "noteq",
                    settings_provider::USE_SEB_CONFIG_MANUALLY);
        }

    }

    // Se ejecuta al salvar el cuestionario.
    public static function save_settings($quiz) {
        global $DB;

        $idprogramselects = $quiz->seb_program_autocomplete_program_quiz;
        $programselectlist = [];

        $recordsprogramselect = $DB->get_records('quizaccess_seb_program_quiz', ['idquiz' => $quiz->id]);
        foreach ($recordsprogramselect as $record) {
            array_push($programselectlist, $record->idprogram);
        }

        $insert = array_diff($idprogramselects, $programselectlist);
        $delete = array_diff($programselectlist, $idprogramselects);

        // Es un editar por tanto tengo que obtener los que deben salir seleccionados.
        if ($quiz->instance > 0) {

            $keysrecord = array_keys($recordsprogramselect);

            foreach ($delete as $value) {
                $posrecordprogram = array_search($value, array_column($recordsprogramselect, 'idprogram'));
                $idprogramquiz = $keysrecord[$posrecordprogram];

                $instance = new program_quiz($idprogramquiz);
                $instance->delete();
            }
        }

        // Insertar relacion programa - quiz.
        foreach ($insert as $value) {
            $data['idprogram'] = $value;
            $data['idquiz'] = $quiz->id;

            $persistent = new program_quiz(0, (object)$data);
            $persistent->create();
        }
    }

    // Se ejecuta al eliminar el cuestionario.
    public static function delete_settings($quiz) {

    }
}
