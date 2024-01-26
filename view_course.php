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
 * Version information for the quizaccess_sebprogram plugin.
 *
 * @package    quizaccess_sebprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use quizaccess_sebprogram\local\table\program_list;
use quizaccess_sebprogram\program;

require_once('../../../../config.php');
require_once("program_controller_course.php");
require_once($CFG->libdir . '/adminlib.php');

$action = optional_param('action', 'view', PARAM_ALPHANUMEXT);
$courseid = required_param('course', PARAM_INT);

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new \moodle_exception('coursemisconf');
}

require_login($course, false);
if (has_capability('quizaccess/sebprogram:manageprograms',  context_course::instance($courseid))) {
    $PAGE->set_context(context_course::instance($course->id));

    $PAGE->navbar->add(
        $course->shortname,
        new moodle_url('/course/view.php', ['id' => $course->id])
    );
    $PAGE->navbar->add(
        get_string('managetemplates', 'quizaccess_sebprogram'),
        new moodle_url('/mod/quiz/accessrule/sebprogram/view_course.php', ['id' => $course->id])
    );

    $manager = new \quizaccess_sebprogram\program_controller_course($courseid);
    $manager->execute($action);
}
