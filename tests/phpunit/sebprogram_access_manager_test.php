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
require_once($CFG->dirroot . "/mod/quiz/accessrule/sebprogram/lib.php");


use quizaccess_sebprogram\access_manager;
use quizaccess_sebprogram\quiz_settings;
class sebprogram_access_manager_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    /**
     * @var \stdClass
     */
    private static $course;
    
    /**
     * @var \stdClass
     */
    private static $coursecontext;

    /**
     * @var \stdClass
     */
    private static $user;

    /**
     * @var int
     */
    private static $reviewattempt;
    
    /**
     * @var int
     */
    private static $timeclose;

    /**
     * @var \stdClass
     */
    private static $quiz;

    /**
     * Course start.
     */
    private const COURSE_START = 1706009000;

    /**
     * Course end.
     */
    private const COURSE_END = 1906009000;

    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );

        $_SERVER['REQUEST_METHOD'] = 'POST';
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$reviewattempt = 0x10010;
        self::$timeclose = 0;
    }

    /**
     * Add program
     *
     * Add a seb program
     *
     * @package    quizaccess_sebprogram
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \sebprogram_access_manager::access_manager
     * @dataProvider dataprovider
     * @param string $param
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_access_manager($param) {
        global $DB, $SITE;
        
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        self::$quiz = $quizgenerator->create_instance(['course' => self::$course->id,
            'seb_program_autocomplete_program_quiz' => [1],
            ]);
        $quizobj = \quiz::create(self::$quiz->id, self::$user->id);
        $cm = get_coursemodule_from_instance('quiz', self::$quiz->id, self::$course->id);

        $accessmanager = new access_manager($quizobj);
        
        $expiretime = null;
        if ($param > 0) {
            $_SERVER['HTTP_USER_AGENT'] = $param;
            $_SERVER['HTTP_X_SAFEEXAMBROWSER_CONFIGKEYHASH'] = $param;
            $_SERVER['HTTP_X_SAFEEXAMBROWSER_REQUESTHASH'] = $param;
            $expiretime = time();
        }
        $this->assertNotNull(quizaccess_sebprogram\helper::get_seb_file_headers($expiretime));
        $this->assertIsBool($accessmanager->validate_config_key());

        // Check capability.
        $accessmanager->can_bypass_seb();

        $accessmanager->get_valid_config_key();

        $this->assertNotNull($accessmanager->get_quiz());

        $this->assertIsBool($accessmanager->should_validate_basic_header());
        $this->assertIsBool($accessmanager->should_validate_config_key());
        $this->assertIsBool($accessmanager->validate_session_access());
        $this->assertIsBool($accessmanager->should_redirect_to_seb_config_link());
        $accessmanager->clear_session_access();
        $quizsettings = new quiz_settings();
        $quizsettings->get_by_quiz_id(self::$quiz->id);
        $this->assertNull($quizsettings->get_config_by_quiz_id(self::$quiz->id));
        $this->assertNull($quizsettings->get_config_key());
        $quizsettings->get_config();
        $reflectionmethod = new \ReflectionMethod(quiz_settings::class, 'after_create');
        $reflectionmethod->setAccessible(true);
        $persistentprogram = $reflectionmethod->invoke($quizsettings);

        $reflectionmethod = new \ReflectionMethod(quiz_settings::class, 'before_create');
        $reflectionmethod->setAccessible(true);
        $persistentprogram = $reflectionmethod->invoke($quizsettings);

        $reflectionmethod = new \ReflectionMethod(quiz_settings::class, 'before_update');
        $reflectionmethod->setAccessible(true);
        $persistentprogram = $reflectionmethod->invoke($quizsettings);
        
        $reflectionmethod = new \ReflectionMethod(quiz_settings::class, 'before_validate');
        $reflectionmethod->setAccessible(true);
        $persistentprogram = $reflectionmethod->invoke($quizsettings);
        
        $this->assertNotNull(get_quiz_id($cm->id));
    }
    public static function dataprovider(): array {
        return [
            [0],
            [1],
        ];
    }

}
