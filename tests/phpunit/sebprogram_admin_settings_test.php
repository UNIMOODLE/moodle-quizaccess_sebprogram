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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * The testing class.
 *
 * @package     quizaccess_sebprogram
 * @copyright   2023 Proyecto UNIMOODLE
 * @author      UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/config.php");
require_once($CFG->dirroot . "/lib/adminlib.php");
require_once($CFG->dirroot . "/mod/quiz/accessrule/quiztimer/externallib.php");
require_once($CFG->dirroot . "/mod/quiz/accessrule/sebprogram/program_controller_course.php");
require_once($CFG->dirroot . "/mod/quiz/accessrule/sebprogram/program_controller.php");
require_once($CFG->dirroot . "/mod/quiz/accessrule/sebprogram/classes/admin_setting_display_programs.php");

use quizaccess_sebprogram\local\table\program_list_course;
use quizaccess_sebprogram\program;
use quizaccess_sebprogram\helper;
use quizaccess_sebprogram\program_controller_course;
use quizaccess_sebprogram\local\table\program_dependency_list;
use quizaccess_sebprogram\local\table\program_list;
use quizaccess_sebprogram\program_controller;
use quizaccess_sebprogram\privacy\provider;
class sebprogram_admin_settings_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    private static $course;
    private static $context;
    private static $coursecontext;
    private static $user;
    private const COURSE_START = 1706009000;
    private const COURSE_END = 1906009000;

    public function setUp(): void {
        global $USER, $PAGE;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        $_SERVER['REQUEST_METHOD'] = 'POST';

    }

    /**
     * Manage admin settings
     *
     * Manage admin settings
     *
     * @package    quizaccess_sebprogram
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \sebprogram_admin_settings::admin_settings
     * @param string $param
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_admin_settings() {
        global $DB;

        $adminsettings = new quizaccess_sebprogram_admin_setting_display_programs();
        // Get settings.
        $this->assertTrue($adminsettings->get_setting());
        // Get default settings.
        $this->assertTrue($adminsettings->get_defaultsetting());
        $adminsettings->write_setting('data');
        $outputhtml = $adminsettings->output_html('', '');
        $this->assertNotNull($outputhtml);
        // Get reason provider.
        $this->assertNotNull(provider::get_reason());
    }

}
