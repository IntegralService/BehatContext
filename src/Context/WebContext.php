<?php

namespace IntegralService\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class WebContext extends MinkContext implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

   /**
    * @Then I wait :sec
    */
    public function wait($sec)
    {
       sleep($sec);
    }
     /**
      * Log in user
      * @Given I am logged in as :login with password :password
      */
    public function iAmLoggedInAsWithPassword($login, $password)
    {
        $session = $this->getSession();
        $baseUrl = $this->getMinkParameter('base_url');
        $session->visit(sprintf('%s/login', $baseUrl));
        $session->getPage()->findField('_username')->setValue($login);
        $session->getPage()->findField('_password')->setValue($password);
        $session->getPage()->findButton('Connexion')->click();
    }

    /**
     * Checks, that there is :text in any correspond html element
     * @Then I should see :text in any :element element
     */
    public function iShouldSeeInAnyElement($text, $element)
    {
        $container = $this->getSession()->getPage();
        $nodes     = $container->findAll('css', $element);

        if (null === $nodes) {
            throw new \Exception('The'.$element.'element is not found');
        } else {
            foreach ($nodes as $node) {
                if (strpos($node->getText(), $text)!== false) {
                    return;
                }
            }
            throw new \Exception('Your text was not found in element');
        }


    }
    /**
     * Checks, that there is X number of element that matches css selector
     * @Then I should see :num matching :selector elements
     */
    public function iShouldSeeMatchingElements($num, $selector)
    {
        $container = $this->getSession()->getPage();
        $nodes     = $container->findAll('css', $selector);
        $counter   = 0;
        if (null === $nodes) {
            throw new \Exception('The'.$element.'element is not found');
        } else {
            foreach ($nodes as $node) {
                $counter++;
            }
            if ($counter == $num) {
                return;
            }

            throw new \Exception('Didn\'t found the same number of element.Looked for '.$num.' but found '.$counter);

        }
    }

    /**
     * @Then /^I click on "([^"]*)"$/
     */
    public function iClickOn($element)
    {
       $page = $this->getSession()->getPage();
       $findName = $page->find("css", $element);
       if (!$findName) {
           throw new \Exception($element . " could not be found");
       } else {
           $findName->click();
       }
    }


    /**
     * Checks, that page contains specified number and text
     * Example: Then I should see "65 ans"
     * Example: And I should see "65 ans"
     *
     * @Then /^(?:|Age) format should be visible as "(\d+) (?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageContainsAgeFormat($text)
    {
        $this->assertSession()->pageTextContains($this->fixStepArgument($text));
    }

    /**
     * @When I press ":linkText" in the ":rowText" row
     */
    public function iPressInTheRow($linkText, $rowText)
    {
        $this->findRowByText($rowText)->pressButton($linkText);
    }

    /**
     * @When I follow ":linkText" in the ":rowText" row
     */
    public function iFollowInTheRow($linkText, $rowText)
    {
        $this->findRowByText($rowText)->clickLink($linkText);
    }

    /**
     * @When I follow ":linkText" by ":attributeName" in the ":rowText" row
     */
    public function iFollowByInTheRow($linkText, $attributeName, $rowText)
    {
        $row = $this->findRowByText($rowText);
        $element = $row->findLink($linkText);
        $attributeValue = $element->getAttribute($attributeName);

        $this->visit($attributeValue);
    }

    /**
     * @param $rowText
     * @return NodeElement
     */
    private function findRowByText($rowText)
    {
        $this->assertSession()->elementExists('css', sprintf('table tr:contains("%s")', $rowText));
        return $this->getSession()->getPage()->find('css', sprintf('table tr:contains("%s")', $rowText));
    }



    /**
     * Checks, that option from select with specified id|name|label|value is selected.
     *
     * @Then /^the "(?P<select>(?:[^"]|\\")*)" select field (?:contains|should contain) the "(?P<option>(?:[^"]|\\")*)" option$/
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option (?:is|should be) in the "(?P<select>(?:[^"]|\\")*)" (?:select) field$/
     */
    public function theSelectFieldShouldContainOption($select, $option)
    {
       $optionField = $this->getOptionFromSelectField($option, $select);

       if (null === $optionField) {
           throw new ExpectationException('Select option field with value|text "'.$option.'" does not exist in the select "'.$select.'"', $this->getSession());
       }
    }

    /**
     * Checks, that option from select with specified id|name|label|value is selected.
     *
     * @Then /^the "(?P<select>(?:[^"]|\\")*)" select field (?:does not contain|should not contain) the "(?P<option>(?:[^"]|\\")*)" option$/
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option (?:is not|should not be) in the "(?P<select>(?:[^"]|\\")*)" (?:select) field$/
     */
    public function theSelectFieldShouldNotContainOption($select, $option)
    {
       $optionField = $this->getOptionFromSelectField($option, $select);

       if (null !== $optionField) {
           throw new ExpectationException('Select option field with value|text "'.$option.'" exists in the select "'.$select.'"', $this->getSession());
       }
    }

    /**
     * Checks, that option from select with specified id|name|label|value is selected.
     *
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected/
     * @Then /^the option "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     * @Then /^"(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     */
    public function theOptionFromShouldBeSelected($option, $select)
    {
       $optionField = $this->getOptionFromSelectField($option, $select);

       if (null === $optionField) {
           throw new ElementNotFoundException($this->getSession(), 'select option field', 'id|name|label|value', $option);
       }

       if (!$optionField->isSelected()) {
           throw new ExpectationException('Select option field with value|text "'.$option.'" is not selected in the select "'.$select.'"', $this->getSession());
       }
    }

    /**
     * Checks, that option from select with specified id|name|label|value is not selected.
     *
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option from "(?P<select>(?:[^"]|\\")*)" (?:is not|should not be) selected/
     * @Then /^the option "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is not|should not be) selected$/
     * @Then /^"(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is not |should not be) selected$/
     */
    public function theOptionFromShouldNotBeSelected($option, $select)
    {
       $optionField = $this->getOptionFromSelectField($option, $select);

       if (null === $optionField) {
           throw new ElementNotFoundException($this->getSession(), 'select option field', 'id|name|label|value', $option);
       }

       if ($optionField->isSelected()) {
           throw new ExpectationException('Select option field with value|text "'.$option.'" is selected in the select "'.$select.'"', $this->getSession());
       }
    }

    /**
     * @param string $option
     * @param string $select
     * @return NodeElement
     * @throws ElementNotFoundException
     */
    private function getOptionFromSelectField($option, $select)
    {
        $selectField = $this->getSession()->getPage()->findField($select);
        if (null === $selectField) {
            throw new ElementNotFoundException($this->getSession(), 'select field', 'id|name|label|value', $select);
        }

        $optionField = $selectField->find('named', array(
            'option',
            $option,
        ));

        return $optionField;
    }

    /**
     * Fills in specified field with date
     * Example: When I fill in "field_ID" with date "now"
     * Example: When I fill in "field_ID" with date "-7 days"
     * Example: When I fill in "field_ID" with date "+7 days"
     * Example: When I fill in "field_ID" with date "-/+0 weeks"
     * Example: When I fill in "field_ID" with date "-/+0 years"
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with date "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function fillDateField($field, $value)
    {
            $date = new \DateTime($value);
            $this->getSession()->getPage()->findField($field)->setValue($date->format("d/m/Y"));
    }

    /**
     * Click some text
     *
     * @When /^I click on the text "([^"]*)"$/
     */
    public function iClickOnTheText($text)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//*[text()="'. $text .'"]')
        );
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
        }

        $element->click();

    }

    /**
     * @When I click the :arg1 element
     */
    public function iClickTheElement($selector)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $selector);

        if (empty($element)) {
            throw new Exception("No html element found for the selector ('$selector')");
        }

        $element->click();
    }

    /**
     * @When I scroll to top
     */
    public function iScrollToTop()
    {
        try {
            $this->getSession()->executeScript("(function(){window.scrollTo(0, 0);})();");
        } catch (Exception $e) {
            throw new \Exception("ScrollToTop failed");
        }
    }
}
