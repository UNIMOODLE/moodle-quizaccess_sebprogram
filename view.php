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

use quizaccess_sebprogram\local\table\program_list;
use quizaccess_sebprogram\program;

require_once('../../../../config.php');
require_once("program_controller.php");
require_once($CFG->libdir . '/adminlib.php');

$action = optional_param('action', 'view', PARAM_ALPHANUMEXT);

require_login();

$PAGE->set_context(context_system::instance());

$PAGE->navbar->add(
    get_string('activitymodules'),
    new moodle_url('/admin/category.php', ['category' => 'modsettings'])
);
$PAGE->navbar->add(
    'Quiz',
    new moodle_url('/admin/category.php', ['category' => 'modsettingsquizcat'])
);
$PAGE->navbar->add(
    get_string('pluginname', 'quizaccess_sebprogram'),
    new moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatsebprogram'])
);

$manager = new \quizaccess_sebprogram\program_controller();
$manager->execute($action);
