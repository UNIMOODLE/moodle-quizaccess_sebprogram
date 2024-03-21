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
 * Behat quizaccess_sebprogram-related steps definitions.
 *
 * @package    quizaccess_sebprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * hybridteaching-related steps definitions.
 *
 * @package    quizaccess_sebprogram
 * @copyright  2024 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class behat_sebprogram extends behat_base {

    /**
     * @When /^I type "([^"]*)" in the focused autocomplete using JavaScript$/
     */
    public function i_type_in_focused_autocomplete_using_javascript($value) {
        $javascript = <<<JS
        var focusedElement = document.activeElement;
        if (focusedElement.tagName === 'INPUT' && focusedElement.getAttribute('data-fieldtype') === 'autocomplete') {
            focusedElement.value = '{$value}';
            var inputEvent = new Event('input', { bubbles: true });
            focusedElement.dispatchEvent(inputEvent);
        } else {
            throw new Error('The focused element is not an autocomplete input.');
        }
    JS;

        $this->getSession()->executeScript($javascript);
    }
}
