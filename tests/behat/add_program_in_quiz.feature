@quizaccess_sebprogram @add_sebprogram @javascript
Feature: A teacher add a new safe exam program from the quiz configuration
  In order to add a new program to the safe exam browser
  As a teacher
  I should be enabled to create and configure a quiz in the course page.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Test Sebprogram | testsebprogram | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | testsebprogram | editingteacher |
    And I log in as "teacher1"
    And I wait "2" seconds

  Scenario: Teacher set a safe exam program
    Given I am on "testsebprogram" course homepage with editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Quiz" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Quiz"
    And I click on "Safe Exam Browser" "link"
    And I select "Yes – Configure manually" from the "seb_requiresafeexambrowser" singleselect
    And I click on "Manage programs" "link"
    And I wait "2" seconds
    And I click on "Add new program" "button"
    And I wait "2" seconds
    And I set the following fields to these values:
      | Title  | excel |
      | Executable  | excel.exe |
      | Originalname | excel |
      | Path | D:\user\program files(x86)\AppData\Roaming\Excel\ |
    And I wait "1" seconds
    And I click on "Save changes" "button"
    And I should see "Changes saved"
    And I click on "Return to Quiz" "button"
    And I set the following fields to these values:
      | Name | Quiz example |
    And I select "Yes – Configure manually" from the "seb_requiresafeexambrowser" singleselect
    And I click on "Programs" "field"
    And I type "excel" in the focused autocomplete using JavaScript
    And I wait "1" seconds
    And I click on "excel" "list_item"
    And I wait "1" seconds
    And I press "Save and display"
    And I should see "This quiz has been configured so that students may only attempt it using the Safe Exam Browser"
    And I wait "2" seconds