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
require_once($CFG->dirroot . "/mod/quiz/accessrule/quiztimer/externallib.php");
require_once($CFG->dirroot . "/mod/quiz/accessrule/sebprogram/program_controller_course.php");
require_once($CFG->dirroot . "/mod/quiz/accessrule/sebprogram/program_controller.php");

use quizaccess_sebprogram\local\table\program_list_course;
use quizaccess_sebprogram\program;
use quizaccess_sebprogram\helper;
use quizaccess_sebprogram\program_controller_course;
use quizaccess_sebprogram\local\table\program_dependency_list;
use quizaccess_sebprogram\local\table\program_list;
use quizaccess_sebprogram\program_controller;
use quizaccess_sebprogram\program_dependency;
class sebprogram_tables_test extends \advanced_testcase {

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
        $PAGE->set_url(new \moodle_url('mod/newmodule/view.php', []));
    }

    /**
     * Manage tables
     *
     * Manage tables, forms and their programs
     *
     * @package    quizaccess_sebprogram
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \sebprogram_tables::tables
     * @dataProvider dataprovider
     * @param string $param
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_tables($param) {
        global $DB;

        $programcontrollercourse = new program_controller_course(self::$course->id);
        $programlistcourse = new program_list_course();
        $programdepencencelist = new program_dependency_list();
        $programlist = new program_list();
        $programdependency = new program_dependency();

        $moduleinstance = new \stdClass();
        $moduleinstance->course = self::$course->id;
        $datadecoded = json_decode($param);
        // Create seb program.
        $dataprogram = new \stdClass();
        $dataprogram->title = $datadecoded->title;
        $dataprogram->executable = $datadecoded->executable;
        $dataprogram->numberofuses = $datadecoded->numberofuses;
        $dataprogram->display = $datadecoded->display;
        $dataprogram->courseid = self::$course->id;
        $dataprogram->originalname = $datadecoded->originalname;
        $dataprogram->path = $datadecoded->path;

        // In case of error comment the next line and uncheck the other one
        // Check if seb_config is valid
        helper::is_valid_seb_config($param) ? $DB->insert_record('quizaccess_seb_program', $dataprogram) : print  "Not valid seb config";

        // Create program course persistent.
        $reflectionmethod = new \ReflectionMethod(program_controller_course::class, 'get_instance');
        $reflectionmethod->setAccessible(true);
        $persistentprogram = $reflectionmethod->invoke($programcontrollercourse, 0, $dataprogram);
        $persistentprogram->create();

        // Create program dependency persistent.
        $reflectionmethod = new \ReflectionMethod(program_controller_course::class, 'get_instance_dependency');
        $reflectionmethod->setAccessible(true);
        $programdepstd = new stdClass();
        $programdepstd->idprogram = $persistentprogram->get('id');
        $programdepstd->idprogram_dependency = 1;
        $persistentprogramdep = $reflectionmethod->invoke($programcontrollercourse, 0, $programdepstd);
        $persistentprogramdep->create();

        // Template course.
        $mform = new \quizaccess_sebprogram\local\form\template_course(null, ['persistent' => $persistentprogram]);
        $mform->get_program_select_list();
        $mform->get_records_program_select();

        $mform = new \quizaccess_sebprogram\local\form\template(null, ['persistent' => $persistentprogram]);
        $mform->get_program_select_list();
        $mform->get_records_program_select();

        // TABLE.
        // Column table.
        $reflectionmethod = new \ReflectionMethod(program_dependency_list::class, 'col_title');
        $reflectionmethod->setAccessible(true);
        $columntable = $reflectionmethod->invoke($programdepencencelist, $persistentprogram);
        $this->assertNotNull($columntable);
        // Column executable.
        $reflectionmethod = new \ReflectionMethod(program_dependency_list::class, 'col_executable');
        $reflectionmethod->setAccessible(true);
        $columnexecutable = $reflectionmethod->invoke($programdepencencelist, $persistentprogram);
        $this->assertNotNull($columnexecutable);
        // Column name.
        $reflectionmethod = new \ReflectionMethod(program_dependency_list::class, 'col_originalname');
        $reflectionmethod->setAccessible(true);
        $columnname = $reflectionmethod->invoke($programdepencencelist, $persistentprogram);
        $this->assertNotNull($columnname);
        // Column actions.
        $reflectionmethod = new \ReflectionMethod(program_dependency_list::class, 'col_actions');
        $reflectionmethod->setAccessible(true);
        $columnactions = $reflectionmethod->invoke($programdepencencelist, $persistentprogram);
        $this->assertNotNull($columnactions);

        // PROGRAM LIST.
        // Column table.
        $reflectionmethod = new \ReflectionMethod(program_list::class, 'col_title');
        $reflectionmethod->setAccessible(true);
        $columntable = $reflectionmethod->invoke($programlist, $persistentprogram);
        $this->assertNotNull($columntable);
        // Column executable.
        $reflectionmethod = new \ReflectionMethod(program_list::class, 'col_executable');
        $reflectionmethod->setAccessible(true);
        $columnexecutable = $reflectionmethod->invoke($programlist, $persistentprogram);
        $this->assertNotNull($columnexecutable);
        // Column name.
        $reflectionmethod = new \ReflectionMethod(program_list::class, 'col_originalname');
        $reflectionmethod->setAccessible(true);
        $columnname = $reflectionmethod->invoke($programlist, $persistentprogram);
        $this->assertNotNull($columnname);
        // Column actions.
        $reflectionmethod = new \ReflectionMethod(program_list::class, 'col_actions');
        $reflectionmethod->setAccessible(true);
        $columnactions = $reflectionmethod->invoke($programlist, $persistentprogram);
        $this->assertNotNull($columnactions);
        // Column dependency.
        $reflectionmethod = new \ReflectionMethod(program_list::class, 'col_dependency');
        $reflectionmethod->setAccessible(true);
        $columndependency = $reflectionmethod->invoke($programlist, $persistentprogram);
        $this->assertNotNull($columndependency);

        // PROGRAM LIST COURSE.
        // Column table.
        $reflectionmethod = new \ReflectionMethod(program_list_course::class, 'col_title');
        $reflectionmethod->setAccessible(true);
        $columntable = $reflectionmethod->invoke($programlistcourse, $persistentprogram);
        $this->assertNotNull($columntable);
        // Column executable.
        $reflectionmethod = new \ReflectionMethod(program_list_course::class, 'col_executable');
        $reflectionmethod->setAccessible(true);
        $columnexecutable = $reflectionmethod->invoke($programlistcourse, $persistentprogram);
        $this->assertNotNull($columnexecutable);
        // Column name.
        $reflectionmethod = new \ReflectionMethod(program_list_course::class, 'col_originalname');
        $reflectionmethod->setAccessible(true);
        $columnname = $reflectionmethod->invoke($programlistcourse, $persistentprogram);
        $this->assertNotNull($columnname);
        // Column actions.
        $reflectionmethod = new \ReflectionMethod(program_list_course::class, 'col_actions');
        $reflectionmethod->setAccessible(true);
        $columnactions = $reflectionmethod->invoke($programlistcourse, $persistentprogram);
        $this->assertNotNull($columnactions);
        // Column dependency.
        $reflectionmethod = new \ReflectionMethod(program_list_course::class, 'col_dependency');
        $reflectionmethod->setAccessible(true);
        $columndependency = $reflectionmethod->invoke($programlistcourse, $persistentprogram);
        $this->assertNotNull($columndependency);
        // Number of uses.
        $reflectionmethod = new \ReflectionMethod(program_list_course::class, 'col_numberofuses');
        $reflectionmethod->setAccessible(true);
        $numberofuses = $reflectionmethod->invoke($programlistcourse, $persistentprogram);
        $this->assertIsNumeric($numberofuses);
        // Check program was saved.
        $this->assertNotNull($DB->get_records('quizaccess_seb_program_quiz'));

    }
    public static function dataprovider(): array {
        return [
            ['{"title":"excel", "executable": "excel.exe", "originalname": "excel", "display": 1, "numberofuses" : 2, "path":"C://user/excel.exe"}'],
        ];
    }

}
