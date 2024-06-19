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
defined('MOODLE_INTERNAL') || die();

$string['addprogram'] = 'Add new program';
$string['cantdelete'] = 'The program can\'t be deleted as it has been used.';
$string['cantdeletedependency'] = 'The program can\'t be deleted ';
$string['cantedit'] = 'The program can\'t be edited as it has been used for one or more quizzes.';
$string['content'] = 'Program';
$string['dependency'] = 'Dependency';
$string['description'] = 'Description';
$string['numberofuses'] = 'Number of uses';
$string['display'] = 'Display';
$string['downloadsebconfig'] = 'Download SEB config file';
$string['duplicatetemplate'] = 'A program with the same name already exists.';
$string['edittemplate'] = 'Edit program';
$string['enabled'] = 'Enabled';
$string['error:ws:nokeyprovided'] = 'At least one Safe Exam Browser key must be provided.';
$string['error:ws:quiznotexists'] = 'Quiz not found matching course module ID: {$a}';
$string['event:accessprevented'] = "Quiz access was prevented";
$string['event:templatecreated'] = 'SEB program was created';
$string['event:templatedeleted'] = 'SEB program was deleted';
$string['event:templatedisabled'] = 'SEB program was disabled';
$string['event:templateenabled'] = 'SEB program was enabled';
$string['event:templateupdated'] = 'SEB program was updated';
$string['executable'] = 'Executable';
$string['executablerequired'] = 'Executable Required';
$string['exitsebbutton'] = 'Exit Safe Exam Browser';
$string['invalidprogram'] = "Invalid SEB config template";
$string['manage_programs'] = 'Safe Exam Browser programs access';
$string['managetemplates'] = 'Manage programs';
$string['name'] = 'Name';
$string['originalname'] = 'Originalname';
$string['originalnamerequired'] = 'Originalname Required';
$string['title'] = 'Title';
$string['titlerequired'] = 'Title Required';
$string['newprogram'] = 'New program';
$string['noconfigfound'] = 'No SEB config could be found for quiz with cmid: {$a}';
$string['path'] = 'Path';
$string['pathrequired'] = 'Path Required';
$string['pluginname'] = 'Safe Exam Browser access program';
$string['seb'] = 'Safe Exam Browser';
$string['seb:managetemplates'] = 'Manage SEB configuration program';
$string['sebprogram:manageprograms'] = 'Manage SEB configuration program';
$string['seb_requiresafeexambrowser'] = 'Require the use of Safe Exam Browser';
$string['used'] = 'In use';
$string['returntoquiz'] = 'Return to Quiz';
$string['title_help'] = 'Application title.';
$string['executable_help'] = 'File name of the executable, which should not contain any parts of a file system path,
    only the filename of the file (like calc.exe).';
$string['originalname_help'] = 'Original file name of the executable. Some files
    don\'t have this metadata information. If it is available, SEB will prioritize this string over the Executable file name string';
$string['path_help'] = 'Path to the directory of the executable process excluding the file name.';
$string['dependency_help'] = 'List of other programs, that this one need to work properly';
