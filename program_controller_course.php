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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_sebprogram;

use core\notification;
use moodle_url;
use quizaccess_sebprogram\local\table\program_list_course;

/**
 * Class for manipulating with the template records.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class program_controller_course {
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

    private $courseid;


    /**
     * Locally cached $OUTPUT object.
     * @var \bootstrap_renderer
     */
    protected $output;

    /**
     * region_manager constructor.
     */
    public function __construct($courseid) {
        global $OUTPUT;
        $this->courseid = $courseid;
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

    private function get_instance_dependency($id = 0, \stdClass $data = null) {
        return new program_dependency($id, $data);
    }

    /**
     * Print out all records in a table.
     */
    protected function display_all_records() {
        global $DB;

        $records = program::get_records_course($this->courseid, 'id');

        $table = new program_list_course();
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
     * @param \quizaccess_sebprogram\template_course|null $instance
     *
     * @return \quizaccess_sebprogram\local\form\template_course
     */
    protected function get_form($instance) : \quizaccess_sebprogram\local\form\template_course {
        global $PAGE;

        return new \quizaccess_sebprogram\local\form\template_course($PAGE->url->out(false), ['persistent' => $instance]);
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
        return ''.new moodle_url("/mod/quiz/accessrule/sebprogram/view_course.php",
            ['course' => optional_param('course', 0, PARAM_INT)]).'';
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
            redirect(new \moodle_url(static::get_base_url()));
        } else if ($data = $form->get_data()) {
            unset($data->submitbutton);
            try {

                $selecteditems = $data->my_autocomplete_program;
                $programselectlist = $form->get_program_select_list();

                $insert = array_diff($selecteditems, $programselectlist);
                $delete = array_diff($programselectlist, $selecteditems);

                if (empty($data->id)) { // Create program.

                    $data->courseid = $this->courseid;

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
            redirect(new \moodle_url(static::get_base_url()));
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

        $dependencyrecords = $DB->get_records("quiz_seb_program_dependecy", ['idprogram_dependency' => $id]);

        if ($instance->can_delete()) {
            $instance->delete();
            foreach ($dependencyrecords as $dependencyrecord) {
                $DB->delete_records("quiz_seb_program_dependecy", ['id' => $dependencyrecord->id]);
            }
            notification::success(get_string('deleted'));

            redirect(new \moodle_url(static::get_base_url()));
        } else {
            notification::warning(get_string('cantdelete', 'quizaccess_sebprogram'));
            redirect(new \moodle_url(static::get_base_url()));
        }
    }

    /**
     * Execute view action.
     */
    protected function view() {
        global $PAGE;

        // Avoid 'This page did not call $PAGE->set_url(...)' warning.
        // Details at https://tracker.moodle.org/browse/MDL-75450.
        $PAGE->set_url(new \moodle_url(static::get_base_url()));

        $this->header($this->get_view_heading());
        $this->print_add_button();
        $this->display_all_records();

        // JS for Template management.
        // $PAGE->requires->js_call_amd('quizaccess_sebprogram/managetemplates', 'setup');

        $this->footer();
    }

    /**
     * Print out add button.
     */
    protected function print_add_button() {

        // Ignored for now session_start();.
        $urlquiz = $_SESSION['urleditquiz'];
        echo $this->output->single_button(
            $urlquiz,
            'Return to Quiz'
        );
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
     * Returns a text for create new record button.
     * @return string
     */
    protected function get_create_program_button_text() : string {
        return get_string('addprogram', 'quizaccess_sebprogram');
    }

    private function create_program_dependency($idprogram, $insert) {
        foreach ($insert as $value) {
            $data['idprogram'] = $idprogram;
            $data['idprogram_dependency'] = $value;

            $persistent = $this->get_instance_dependency(0, (object)$data);
            $persistent->create();
        }
    }

    private function delete_program_dependency($idprogram, $delete, $records) {

        $keysrecord = array_keys($records);

        foreach ($delete as $value) {
            $posrecordprogram = array_search($value, array_column($records, 'idprogram_dependency'));
            $iddependency = $keysrecord[$posrecordprogram];

            $instance = $this->get_instance_dependency($iddependency);
            $instance->delete();
        }
    }

}
