@quizaccess_sebprogram @view_sebprogram @javascript
Feature: A student visualizes a quiz with a sebprogram configured
  In order to visualize a quiz with a sebprogram configured
  As a student
  I should be enabled to view a quiz in a course.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Test Sebprogram | testsebprogram | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | testsebprogram | student |
    And I log in as "admin"
    And I wait "2" seconds
    And I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "Safe Exam Browser access program" "link"
    And I wait "1" seconds
    And I click on "Add new program" "link"
    When I set the following fields to these values:
      | Title  | excel |
      | Executable  | excel.exe |
      | Originalname | excel |
      | Path | D:\user\program files(x86)\AppData\Roaming\Excel\ |
    And I wait "1" seconds
    And I click on "Save changes" "button"
    And I should see "Changes saved"
    And I should see "excel" in the "generaltable" "table"
    And I wait "2" seconds
    And I am on "testsebprogram" course homepage
    And I turn editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Quiz" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Quiz"
    And I set the following fields to these values:
      | Name | Quiz example |
    And I select "Yes – Configure manually" from the "seb_requiresafeexambrowser" singleselect
    And I wait "1" seconds
    And I click on "Programs" "field"
    And I type "excel" in the focused autocomplete using JavaScript
    And I wait "1" seconds
    And I click on "excel" "list_item"
    And I wait "1" seconds
    And I press "Save and display"
    And I should see "This quiz has been configured so that students may only attempt it using the Safe Exam Browser"
    And I wait "2" seconds
    And I click on "Questions" "link"
    And I turn editing mode off
    And I click on "Add" "link"
    And I click on "a new question" "link"
    And I click on "item_qtype_truefalse" "radio"
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    Then I set the following fields to these values:
      | Question name | question test |
      | Question text  | question text example |
    And I click on "id_submitbutton" "button" 

  Scenario: The student visualizes a quiz with a safe exam browser program configured
    Given I log in as "student1"
    And I am on "testsebprogram" course homepage
    And I wait "1" seconds
    When I click on "Quiz example" "link" in the "Quiz example" activity
    And I wait "1" seconds
    Then I should see "Launch Safe Exam Browser"
    And I wait "3" seconds