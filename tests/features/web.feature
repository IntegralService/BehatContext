Feature: Web Feature

    ################
    # Page content #
    ################

    Scenario: Test content in any element matching a CSS selector (iShouldSeeInAnyElement)
        When I am on "web/test.html"
        Then I should see "second text div" in any "div.text-content" element

    Scenario: Test the number of element matching a CSS selector (iShouldSeeMatchingElements)
        When I am on "web/test.html"
        Then I should see "2" matching "div.text-content" elements

    Scenario: Test the presence of a "count" type of text, where the number doesn't matter (assertPageContainsAgeFormat)
        When I am on "web/test.html"
        Then Age format should be visible as "2 test"


    ##########
    # Inputs #
    ##########

    # To work properly, the input fields MUST be in a form and MUST have a "name"

    Scenario: Test the presence of a option in a select (theSelectFieldShouldContainOption)
        When I am on "web/test.html"
        Then the "first-select" select field should contain the "value1" option
        And  the "first-select" select field contains the "value2" option
        And  the "value4" option is in the "first-select" select field
        And  the "value6" option should be in the "first-select" select field

    Scenario: Test the absence of a option in a select (theSelectFieldShouldNotContainOption)
        When I am on "web/test.html"
        Then the "first-select" select field should not contain the "valueN" option
        And  the "first-select" select field does not contain the "valueN" option
        And  the "valueN" option is not in the "first-select" select field
        And  the "valueN" option should not be in the "first-select" select field

    Scenario: Test that an option in a select is selected (theOptionFromShouldBeSelected)
        When I am on "web/test.html"
        Then the "3" option from "first-select" is selected
        And  the "3" option from "first-select" should be selected
        And  the option "3" from "first-select" is selected
        And  the option "3" from "first-select" should be selected
        And  "3" from "first-select" is selected
        And  "3" from "first-select" should be selected

    Scenario: Test that an option in a select is not selected (theOptionFromShouldNotBeSelected)
        When I am on "web/test.html"
        Then the "4" option from "first-select" is not selected
        And  the "4" option from "first-select" should not be selected
        And  the option "4" from "first-select" is not selected
        And  the option "4" from "first-select" should not be selected
        And  "4" from "first-select" is not selected
        And  "4" from "first-select" should not be selected

    Scenario: Test filling a date field (fillDateField)
        When I am on "web/test.html"
        Then I fill in "date-input" with date "now"


    ################
    # Interactions #
    ################

    @javascript
    Scenario: Test clicking on an element selected by CSS (iClickOn)
        When I am on "web/test.html"
        Then I click on "div > span.clickable"
        Then I should not see "is clickable"

    @javascript
    Scenario: Test clicking on an element selected by CSS (iClickTheElement)
        When I am on "web/test.html"
        Then I click the "div > span.clickable" element
        Then I should not see "is clickable"

    @javascript
    Scenario: Test clicking on text (iClickOnTheText)
        When I am on "web/test.html"
        Then I click on the text "is clickable"
        Then I should not see "is clickable"

    @javascript
    Scenario: Test pressing a button in a table row (iPressInTheRow)
        When I am on "web/test.html"
        Then I press "Clickme" in the "Line 3" row
        Then I should see "You pressed right"

    Scenario: Test following a link in a table row (iFollowInTheRow)
        When I am on "web/test.html"
        Then I follow "Followme" in the "Line 2" row
        Then I should see "You are on another page"

    Scenario: Test following a link in a table row when the location is not in its href (iFollowByInTheRow)
        When I am on "web/test.html"
        Then I follow "Followme" by "data-href" in the "Line 1" row
        Then I should see "You are on another page"




    ########
    # TODO #
    ########

    # @Then I wait :sec
    # public function wait($sec)

    # @Given I am logged in as :login with password :password
    # public function iAmLoggedInAsWithPassword($login, $password)

    # @When I scroll to top
    # public function iScrollToTop()
