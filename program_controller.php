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

use core\notification;
use quizaccess_sebprogram\local\table\program_list;

/**
 * Class for manipulating with the template records.
 */
class program_controller {
    /**
     * View action.
     */
    const ACTION_VIEW = 'view';

    /**
     * Add action.
     */
    const ACTION_ADD = 'add';

    /**
     * Edit action.
     */
    const ACTION_EDIT = 'edit';

    /**
     * Delete action.
     */
    const ACTION_DELETE = 'delete';


    /**
     * Locally cached $OUTPUT object.
     * @var \bootstrap_renderer
     */
    protected $output;

    /**
     * region_manager constructor.
     */
    public function __construct() {
        global $OUTPUT;

        $this->output = $OUTPUT;
    }

    /**
     * Execute required action.
     *
     * @param string $action Action to execute.
     */
    public function execute($action) {

        switch($action) {
            case self::ACTION_ADD:
            case self::ACTION_EDIT:
                $this->edit($action, optional_param('id', null, PARAM_INT));
                break;

            case self::ACTION_DELETE:
                $this->delete(required_param('id', PARAM_INT));
                break;

            default:
                $this->view();
                break;
        }
    }

    /**
     * Set external page for the manager.
     */
    protected function set_external_page() {
        admin_externalpage_setup('quizaccess_sebprogram/view');
    }

    /**
     * Return record instance.
     *
     * @param int $id
     * @param \stdClass|null $data
     *
     * @return \quizaccess_sebprogram\program
     */
    protected function get_instance($id = 0, \stdClass $data = null) {
        return new program($id, $data);
    }

    /**
     * Get instance of program_dependency.
     *
     * @param int $id description
     * @param \stdClass $data description
     * @return program_dependency
     */
    private function get_instance_dependency($id = 0, \stdClass $data = null) {
        return new program_dependency($id, $data);
    }

    /**
     * Print out all records in a table.
     */
    protected function display_all_records() {
        $records = program::get_records(['courseid' => -1], 'id');

        $table = new program_list();
        $table->display($records);
    }

    /**
     * Returns a text for create new record button.
     * @return string
     */
    protected function get_create_button_text() : string {
        return get_string('addprogram', 'quizaccess_sebprogram');
    }

    /**
     * Returns form for the record.
     *
     * @param \quizaccess_sebprogram\template|null $instance
     *
     * @return \quizaccess_sebprogram\local\form\template
     */
    protected function get_form($instance) : \quizaccess_sebprogram\local\form\template {
        global $PAGE;

        return new \quizaccess_sebprogram\local\form\template($PAGE->url->out(false), ['persistent' => $instance]);
    }

    /**
     * View page heading string.
     * @return string
     */
    protected function get_view_heading() : string {
        return get_string('managetemplates', 'quizaccess_sebprogram');
    }

    /**
     * New record heading string.
     * @return string
     */
    protected function get_new_heading() : string {
        return get_string('newprogram', 'quizaccess_sebprogram');
    }

    /**
     * Edit record heading string.
     * @return string
     */
    protected function get_edit_heading() : string {
        return get_string('edittemplate', 'quizaccess_sebprogram');
    }

    /**
     * Returns base URL for the manager.
     * @return string
     */
    public static function get_base_url() : string {
        return '/mod/quiz/accessrule/sebprogram/view.php';
    }

    /**
     * Execute edit action.
     *
     * @param string $action Could be edit or create.
     * @param null|int $id Id of the region or null if creating a new one.
     */
    protected function edit($action, $id = null) {
        global $PAGE;

        $PAGE->set_url(new \moodle_url(static::get_base_url(), ['action' => $action, 'id' => $id]));
        $instance = null;

        if ($id) {
            $instance = $this->get_instance($id);
        }

        $form = $this->get_form($instance);

        if ($form->is_cancelled()) {
            redirect(new \moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatsebprogram']));
        } else if ($data = $form->get_data()) {
            unset($data->submitbutton);
            $data->timemodified = time();
            try {

                $selecteditems = $data->my_autocomplete_program;
                $programselectlist = $form->get_program_select_list();

                $insert = array_diff($selecteditems, $programselectlist);
                $delete = array_diff($programselectlist, $selecteditems);

                if (empty($data->id)) { // Create program.

                    $data->courseid = -1;

                    if ($this->program_unique_name_check($data)) {
                        notification::error(get_string('duplicatetemplate', 'quizaccess_sebprogram'));
                        redirect(new \moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatsebprogram']));
                    }
                    $data->timecreated = time();
                    $persistent = $this->get_instance(0, $data);
                    $programcreated = $persistent->create();

                    $this->create_program_dependency($programcreated->get('id'), $insert);

                } else {// Update program.
                    $instance->from_record($data);
                    $instance->update();

                    $this->create_program_dependency($data->id, $insert);
                    $this->delete_program_dependency($data->id, $delete, $form->get_records_program_select());

                }
                notification::success(get_string('changessaved'));
            } catch (\Exception $e) {
                notification::error($e->getMessage());
            }
            redirect(new \moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatsebprogram']));

        } else {
            if (empty($instance)) {
                $form->set_data(['display' => 1]);
                $this->header($this->get_new_heading());
            } else {
                $this->header($this->get_edit_heading());
            }
        }

        $form->display();
        $this->footer();
    }

    /**
     * Execute delete action.
     *
     * @param int $id ID of the region.
     */
    protected function delete($id) {
        global $DB;
        require_sesskey();
        $instance = $this->get_instance($id);

        $dependencyrecords = $DB->get_records("quizaccess_sebprogram_depend", ['idprogram_dependency' => $id]);

        if ($instance->can_delete()) {
            $instance->delete();
            foreach ($dependencyrecords as $dependencyrecord) {
                $DB->delete_records("quizaccess_sebprogram_depend", ['id' => $dependencyrecord->id]);
            }
            notification::success(get_string('deleted'));

            redirect(new \moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatsebprogram']));
        } else {
            notification::warning(get_string('cantdelete', 'quizaccess_sebprogram'));
            redirect(new \moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatsebprogram']));
        }
    }

    /**
     * Execute view action.
     */
    protected function view() {
        global $PAGE;

        $this->header($this->get_view_heading());
        $this->print_add_button();
        $this->display_all_records();

        $this->footer();
    }

    /**
     * Print out add button.
     */
    protected function print_add_button() {
        echo $this->output->single_button(
            new \moodle_url(static::get_base_url(), ['action' => self::ACTION_ADD]),
            $this->get_create_button_text()
        );
    }

    /**
     * Print out page header.
     * @param string $title Title to display.
     */
    protected function header($title) {
        echo $this->output->header();
        echo $this->output->heading($title);
    }

    /**
     * Print out the page footer.
     *
     * @return void
     */
    protected function footer() {
        echo $this->output->footer();
    }


    /**
     * Get the text for the create program button.
     *
     * @return string
     */
    protected function get_create_program_button_text() : string {
        return get_string('addprogram', 'quizaccess_sebprogram');
    }

    /**
     * Create program dependency
     *
     * @param datatype $idprogram description
     * @param datatype $insert description
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    private function create_program_dependency($idprogram, $insert) {
        foreach ($insert as $value) {
            $data['idprogram'] = $idprogram;
            $data['idprogram_dependency'] = $value;

            $persistent = $this->get_instance_dependency(0, (object)$data);
            $persistent->create();
        }
    }

    /**
     * Delete program dependency.
     *
     * @param datatype $idprogram description
     * @param datatype $delete description
     * @param array $records description
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    private function delete_program_dependency($idprogram, $delete, $records) {

        $keysrecord = array_keys($records);

        foreach ($delete as $value) {
            $posrecordprogram = array_search($value, array_column($records, 'idprogram_dependency'));
            $iddependency = $keysrecord[$posrecordprogram];

            $instance = $this->get_instance_dependency($iddependency);
            $instance->delete();
        }
    }

    /**
     * Searches for another program with the same name to prevent duplicates
     * @param object $programdata program data
     * @return bool indicating if record exists.
     */
    protected function program_unique_name_check($programdata) : bool {
        global $DB;

        $existsprogam = false;
        if ($DB->get_record_select('quizaccess_seb_program',
            'courseid = ? AND ' . $DB->sql_compare_text('title') . ' = ?' ,
            [$programdata->courseid, $programdata->title], '*')) {
                $existsprogam = true;
            }
        return $existsprogam;
    }
}
