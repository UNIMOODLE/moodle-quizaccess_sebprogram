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

namespace quizaccess_sebprogram;

use core\persistent;
use rest;

class program_dependency extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'quiz_seb_program_dependecy';

    /** @var property_list $plist The SEB config represented as a Property List object. */
    private $plistprogram;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'idprogram' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'idprogram_dependency' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
        ];
    }

}
