<?php

namespace IntegralService\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behatch\Context\RestContext;
use Symfony\Component\Security\Core\User\UserInterface;
use PHPUnit\Framework\Assert as Assertions;

/**
 * APIContext
 *
 * @author Julien Rouvier <julien@integral-service.fr>
 */
class APIContext extends RestContext implements KernelAwareContext
{
    public $token;
    public $refresh;

    use KernelDictionary;

    /**
     * @Then the JSON should contain JWT Token
     */
    public function theJsonShouldContainJwtToken()
    {
        $jsontoken = $this->getMinkContext()->getSession()->getPage()->getContent();
        $this->token = \json_decode($jsontoken, true)['token'];
        $this->refresh = \json_decode($jsontoken, true)['refresh_token'];
        $this->iAddHeaderEqualTo("Authorization", "Bearer ".$this->token);
    }

    /**
     * @Then the JSON should not contain a valid JWT Token
     */
    public function theJsonShouldNotContainAValidJwtToken()
    {
        $jsontoken = $this->getMinkContext()->getSession()->getPage()->getContent();
        $this->token = \json_decode($jsontoken, true);
        $this->assertArrayNotHasKey('token', $this->token);
        $this->assertArrayNotHasKey('refresh', $this->token);
    }

   /**
    * @Given I am authenticated as :username
    * @Given I am authenticated as :username with provider :provider
    */
    public function iAmAuthenticatedAs($username, $provider = 'fos_user.user_provider.username_email')
    {
        try {
            $user = $this->kernel->getContainer()->get($provider)->loadUserByUsername($username);
        } catch (\Exception $e) {
            $user = null;
        }

        // if user not found
        if($user === null) {
            throw new \Behat\Mink\Exception\ExpectationException("User \"$username\" was not found", $this->getSession());
        }else{
            $this->token = $this->getToken($user);
            $this->iAddHeaderEqualTo("Authorization", "Bearer ".$this->token);
        }

    }

    /**
     * Sends a HTTP request with a some parameters
     *
     * @then I ask a refreshed token to :url
     */
    public function iAskARefreshedTokenTo( $url)
    {
        $files = [];
        $parameters = [];

        $method = "POST";

        $parameters[] = sprintf('%s=%s', "refresh_token", $this->refresh);
        parse_str(implode('&', $parameters), $parameters);

        return $this->request->send(
            $method,
            $this->locatePath($url),
            $parameters,
            $files
        );
    }

    /**
     *
     * @param User $user
     * @return type
     */
    public function getToken(UserInterface $user)
    {
        return $this->kernel->getContainer()->get('lexik_jwt_authentication.encoder')
               ->encode([
                   'username' => $user->getUsername(),
                   'email' => $user->getUsername(),
                   'exp' => $this->getTokenExpiryDateTime(),
               ]);
    }

    /**
     *
     * @return Datetime
     */
    private function getTokenExpiryDateTime()
    {
        $tokenTtl = $this->kernel->getContainer()->getParameter('lexik_jwt_authentication.token_ttl');
        $now = new \DateTime();
        $now->add(new \DateInterval('PT'.$tokenTtl.'S'));

        return $now->format('U');
    }

    /**
     * @Then /^I should get an email on "(?P<email>[^"]+)" with:$/
     */
    public function iShouldGetAnEmail($email, \Behat\Gherkin\Node\PyStringNode $text)
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

            $headers = $message->getHeaders();
            $correctXToHeader = false;
            if ($headers->has('X-Swift-To')) {
                $correctXToHeader = array_key_exists($email,
                    $headers->get('X-Swift-To')->getFieldBodyModel()
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
                    'An email has been found for "%s" but without '.
                    'the text "%s".', $email, $text->getRaw()
                );
            }
        }

        throw new \Behat\Mink\Exception\ExpectationException($error, $this->getSession());
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

        $match = $this->lastMatchedEmail;
        $crawler = new \Symfony\Component\DomCrawler\Crawler($match->getBody());

        $linkEl = $crawler->selectLink($linkSelector);
        Assertions::assertNotNull($linkEl);

        $link = $linkEl->attr('href');
        Assertions::assertNotNull($link);

        $this->getSession()->visit($link);
    }

    /**
     * @return type
     * @throws UnsupportedDriverActionException
     * @throws RuntimeException
     */
    private function getSymfonyProfile()
    {
        /** @var DriverInterface */
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof \Behat\Symfony2Extension\Driver\KernelDriver) {
            throw new \Behat\Mink\Exception\UnsupportedDriverActionException(
                'You need to tag the scenario with '.
                '"@mink:symfony2". Using the profiler is not '.
                'supported by %s', $driver
            );
        }

        $profile = $driver->getClient()->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'The profiler is disabled. Activate it by setting '.
                'framework.profiler.only_exceptions to false and '.
                'framework.profiler.collect to true in your config'
            );
        }

        return $profile;
    }

}
