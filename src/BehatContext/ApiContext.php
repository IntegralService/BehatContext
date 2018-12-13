<?php

namespace IntegralService\BehatContext;

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * APIContext
 *
 * @author Julien Rouvier <julien@integral-service.fr>
 */
class ApiContext extends RawMinkContext
{
    public $token;
    public $refresh;

    use KernelDictionary;

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
            throw new ExpectationException("User \"$username\" was not found", $this->getSession());
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
        $files        = [];
        $method       = "POST";
        $parameters   = [];
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
        return $this->kernel
                    ->getContainer()
                    ->get('lexik_jwt_authentication.encoder')
                    ->encode([
                        'username' => $user->getUsername(),
                        'email'    => $user->getUsername(),
                        'exp'      => $this->getTokenExpiryDateTime(),
                    ]
        );
    }

    /**
     *
     * @return Datetime
     */
    private function getTokenExpiryDateTime()
    {
        $tokenTtl = $this->kernel->getContainer()->getParameter('lexik_jwt_authentication.token_ttl');
        $now      = new \DateTime();
        $now->add(new \DateInterval('PT'.$tokenTtl.'S'));

        return $now->format('U');
    }

    /**
     * @Then the JSON should contain JWT Token
     */
    public function theJsonShouldContainJwtToken()
    {
        $jsontoken     = $this->getMinkContext()->getSession()->getPage()->getContent();
        $this->token   = \json_decode($jsontoken, true)['token'];
        $this->refresh = \json_decode($jsontoken, true)['refresh_token'];
        $this->iAddHeaderEqualTo("Authorization", "Bearer ".$this->token);
    }

    /**
     * @Then the JSON should not contain a valid JWT Token
     */
    public function theJsonShouldNotContainAValidJwtToken()
    {
        $jsontoken   = $this->getMinkContext()->getSession()->getPage()->getContent();
        $this->token = \json_decode($jsontoken, true);
        $this->assertArrayNotHasKey('token', $this->token);
        $this->assertArrayNotHasKey('refresh', $this->token);
    }
}
