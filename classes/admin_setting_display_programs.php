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

use quizaccess_sebprogram\helper;
use quizaccess_sebprogram\program;

/**
 * Class for glossary display formats management.
 *
 * @package quizaccess_sebprogram
 * @copyright
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_sebprogram_admin_setting_display_programs extends admin_setting {
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('seb_program_display_programs', 'Display Programs', '', '');
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '', does not write anything
     *
     * @param string $data Unused
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query='') {
        global $CFG, $OUTPUT, $DB;

        $murl = new moodle_url('/mod/quiz/accessrule/sebprogram/view.php', ['action' => 'add']);
        $str = html_writer::link($murl, get_string('addprogram', 'quizaccess_sebprogram'),
            ['class' => 'btn btn-primary', 'style' => 'margin-bottom:10px;']);

        $table = new html_table();
        $table->head = [
            get_string('title', 'quizaccess_sebprogram'),
            get_string('executable', 'quizaccess_sebprogram'),
            get_string('originalname', 'quizaccess_sebprogram'),
            get_string('dependency', 'quizaccess_sebprogram'),
            get_string('numberofuses', 'quizaccess_sebprogram'),
            get_string('actions'),
        ];
        $table->align = ['left', 'center'];

        $records = $DB->get_records('quizaccess_seb_program', ['courseid' => -1]);

        foreach ($records as $record) {
            $count = $DB->count_records("quizaccess_seb_program_quiz", ['idprogram' => $record->id]);
            $actions = [];

            $actions[] = helper::format_icon_link(
                new \moodle_url('/mod/quiz/accessrule/sebprogram/view.php', [
                    'id'        => $record->id,
                    'action'    => 'edit',
                ]),
                't/edit',
                get_string('edit')
            );

            $actions[] = helper::format_icon_link(
                new \moodle_url('/mod/quiz/accessrule/sebprogram/view.php', [
                    'id'        => $record->id,
                    'action'    => 'delete',
                    'sesskey'   => sesskey(),
                ]),
                't/delete',
                get_string('delete'),
                null,
                [
                'data-action' => 'delete',
                'data-id' => $record->id,
                'class' => ($count ?? 1) == 0 ? 'action-icon' : 'action-icon disabled',
                ]
            );

            $results = program::get_records_generic_dependency($record->id);

            $dependency = '';
            foreach ($results as $result) {
                $dependency .= '<div><strong>'. $result->title .'</strong></div>';
            }

            $table->data[] = [
                $record->title,
                $record->executable,
                $record->originalname,
                $dependency,
                $count,
                implode('&nbsp;', $actions),
            ];
        }

        $str .= html_writer::table($table);

        return highlight($query, $str);
    }
}
