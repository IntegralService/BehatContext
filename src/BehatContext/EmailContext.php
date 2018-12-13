<?php

namespace IntegralService\BehatContext;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Driver\KernelDriver;
use PHPUnit\Framework\Assert as Assertions;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Translation\Exception\LogicException;

/**
 * EmailContext
 *
 * @author Julien Rouvier <julien@integral-service.fr>
 */
class EmailContext extends RawMinkContext
{

    protected $lastMatchedEmail;

    use KernelDictionary;

    /**
     * @Then /^I should get an email on "(?P<email>[^"]+)" with:$/
     */
    public function iShouldGetAnEmail($email, PyStringNode $text)
    {
        $error     = sprintf('No message sent to "%s"', $email);
        $profile   = $this->getSymfonyProfile();
        $collector = $profile->getCollector('swiftmailer');

        foreach ($collector->getMessages() as $message) {

            // Checking the recipient email and the X-Swift-To
            // header to handle the RedirectingPlugin.
            // If the recipient is not the expected one, check
            // the next mail.
            $correctRecipient = array_key_exists(
                    $email, $message->getTo()
            );

            $headers          = $message->getHeaders();
            $correctXToHeader = false;
            if ($headers->has('X-Swift-To')) {
                $correctXToHeader = array_key_exists($email, $headers->get('X-Swift-To')->getFieldBodyModel()
                );
            }

            if (!$correctRecipient && !$correctXToHeader) {
                continue;
            }

            try {
                // checking the content
                $assertion = Assertions::assertContains(
                                $text->getRaw(), $message->getBody()
                );

                // If we're here, the assertion was OK
                $this->lastMatchedEmail = $message;

                return $assertion;
            } catch (AssertException $e) {
                $error = sprintf(
                        'An email has been found for "%s" but without ' .
                        'the text "%s".', $email, $text->getRaw()
                );
            }
        }

        throw new ExpectationException($error, $this->getSession());
    }

    /**
     * Assumes an email has been identified by a previous step,
     * e.g. through 'I should get an email on "test@test.com" with:'.
     *
     * @When /^I click on the "([^"]*)" link in the email$/
     */
    public function iGoToInTheEmail($linkSelector)
    {
        if (!$this->lastMatchedEmail) {
            throw new LogicException('No matched email found from previous step');
        }

        $match   = $this->lastMatchedEmail;
        $crawler = new Crawler($match->getBody());

        $linkEl = $crawler->selectLink($linkSelector);
        Assertions::assertNotNull($linkEl);

        $link = $linkEl->attr('href');
        Assertions::assertNotNull($link);

        $this->getSession()->visit($link);
    }

    /**
     * @return type
     * @throws UnsupportedDriverActionException
     * @throws \RuntimeException
     */
    private function getSymfonyProfile()
    {
        /** @var DriverInterface */
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof KernelDriver) {
            throw new UnsupportedDriverActionException(
                'You need to tag the scenario with ' .
                '"@mink:symfony2". Using the profiler is not ' .
                'supported by %s', $driver
            );
        }

        $profile = $driver->getClient()->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'The profiler is disabled. Activate it by setting ' .
                'framework.profiler.only_exceptions to false and ' .
                'framework.profiler.collect to true in your config'
            );
        }

        return $profile;
    }

}
