@quizaccess_sebprogram @add_sebprogram @javascript
Feature: An admin add a new safe exam program from the site administration menu
  In order to add a new program to the safe exam browser
  As an admin
  I should be enabled to configure the safe exam browser programs in the site administration menu.

  Scenario: Configure a new safe exam program in the site administration menu
    Given I log in as "admin"
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
    Then I click on "Save changes" "button"
    And I should see "Changes saved"
    And I should see "excel" in the "generaltable" "table"
    And I wait "2" seconds