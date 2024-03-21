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
namespace quizaccess_sebprogram\local\form;

use quizaccess_sebprogram\program;
use core\flexible_table;

/**
 * Form for manipulating with the template records.
 *
 */
class template extends \core\form\persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = 'quizaccess_sebprogram\\program';

    /** @var array All program list. */
    private $programlist = [];

    /** @var array Records program select list. */
    private $recordsprogramselect = [];

    /** @var array Program select list. */
    private $programselectlist = [];

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        global $DB;

        $mform->addElement('text', 'title', get_string('title', 'quizaccess_sebprogram'));
        $mform->addRule('title', get_string('required'), 'required', null, 'client');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('text', 'executable', get_string('executable', 'quizaccess_sebprogram'));
        $mform->addRule('executable', get_string('required'), 'required', null, 'client');
        $mform->setType('executable', PARAM_TEXT);

        $mform->addElement('text', 'originalname', get_string('originalname', 'quizaccess_sebprogram'));
        $mform->addRule('originalname', get_string('required'), 'required', null, 'client');
        $mform->setType('originalname', PARAM_TEXT);

        $mform->addElement('text', 'path', get_string('path', 'quizaccess_sebprogram'));
        $mform->addRule('path', get_string('required'), 'required', null, 'client');
        $mform->setType('path', PARAM_TEXT);

        $mform->addElement('selectyesno', 'display', get_string('display', 'quizaccess_sebprogram'));
        $mform->setType('display', PARAM_INT);

        $programid = $this->get_persistent()->get('id');

        $recordprograms = $DB->get_records('quizaccess_seb_program', ['courseid' => -1]);
        foreach ($recordprograms as $record) {
            // Cuando es nuevo se listan todos y cuando es modificar se listan todos menos el programa que se encuantra en edicion.
            if ( ($programid > -1 && $record->id != $programid) || ($programid == -1)  ) {
                $this->programlist[$record->id] = $record->title;
            }
        }
        $options = ['multiple' => true];
        $mform->addElement('autocomplete', 'my_autocomplete_program', get_string('dependency', 'quizaccess_sebprogram'),
            $this->programlist, $options);
        $mform->setType('my_autocomplete_program', PARAM_RAW);

        // Si es un editar obtener los programas dependientes del seleccionnado.
        if ($programid > 0) {
            $this->set_all_program_list($programid);
        }

        $this->add_action_buttons();
    }

    /**
     * Extra validation.
     *
     * @param  \stdClass $data Data to validate.
     * @param  array $files Array of files.
     * @param  array $errors Currently reported errors.
     * @return array of additional errors, or overridden errors.
     */
    protected function extra_validation($data, $files, array &$errors) {
        $newerrors = [];

        // Check title.
        if (empty($data->title)) {
            $newerrors['title'] = get_string('titlerequired', 'quizaccess_sebprogram');
        }
        if (empty($data->executable)) {
            $newerrors['executable'] = get_string('executablerequired', 'quizaccess_sebprogram');
        }
        if (empty($data->originalname)) {
            $newerrors['originalname'] = get_string('originalnamerequired', 'quizaccess_sebprogram');
        }
        if (empty($data->path)) {
            $newerrors['path'] = get_string('pathrequired', 'quizaccess_sebprogram');
        }

        return $newerrors;
    }

    /**
     * Get the program select list.
     *
     * @return mixed The program select list.
     */
    public function get_program_select_list() {
        return $this->programselectlist;
    }

    /**
     * Get the records from the program select.
     *
     * @return mixed
     */
    public function get_records_program_select() {
        return $this->recordsprogramselect;
    }

    /**
     * Set all program list.
     *
     * @param datatype $idprogram description
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    private function set_all_program_list($idprogram) {
        global $DB;
        $this->recordsprogramselect = $DB->get_records('quizaccess_sebprogram_depend', ['idprogram' => $idprogram]);
        foreach ($this->recordsprogramselect as $record) {
            array_push($this->programselectlist, $record->idprogram_dependency);
        }
        $this->_form->getElement('my_autocomplete_program')->setValue($this->programselectlist);
    }
}
