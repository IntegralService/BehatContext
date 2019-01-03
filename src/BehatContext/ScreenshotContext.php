<?php

namespace IntegralService\BehatContext;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;

class ScreenshotContext extends RawMinkContext
{
    static protected $images = [];
    static protected $mail_to;
    static protected $mail_from;
    static protected $mail_subject;
    static protected $mailer_host;
    static protected $mailer_port;
    static protected $mailer_encryption;
    static protected $mailer_username;
    static protected $mailer_password;
    static protected $tmpPath;
    static protected $mailer;

    public function __construct(
        array $mail_to,
        $mail_from,
        $mail_subject = 'Tests failing',
        $mailer_host = 'smtp.gmail.com',
        $mailer_port = 465,
        $mailer_encryption = 'ssl',
        $mailer_username = null,
        $mailer_password = null,
        $tmpPath = '/tmp'
    ) {
        self::$mail_to           = $mail_to;
        self::$mail_from         = $mail_from;
        self::$mail_subject      = $mail_subject;
        self::$mailer_host       = $mailer_host;
        self::$mailer_port       = $mailer_port;
        self::$mailer_encryption = $mailer_encryption;
        self::$mailer_username   = $mailer_username;
        self::$mailer_password   = $mailer_password;
        self::$tmpPath           = $tmpPath;
    }

    /**
     * @AfterSuite
     **/
    public static function sendScreenshots()
    {
        if (!empty(self::$images)) {
            $text = "";

            foreach (self::$images as $name => $path) {
                $text = sprintf("%s\n%s", $text, $name);
            }

            self::sendEmail($text, self::$images);
        }
    }

    /**
     * @AfterScenario
     **/
    public function createScreenshotForFailure(AfterScenarioScope $event)
    {
        $results = $event->getTestResult();

        if (!$results->isPassed()) {
            $this->createScreenshot($event);
        }
    }

   /**
    * @Then I send an email with a screenshot
    * @Then I send an email with a screenshot and subject :message
    */
    public function iSendEmailWithScreenshot($subject = "Behat screenshot")
    {
        $path = $this->createScreenshot();

        if ($path) {
            self::sendEmail($path, [$path], $subject);
        }
    }

    /**
     * @param string $content
     */
    protected static function sendEmail($content, $attachments, $subject = null)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject ? $subject : self::$mail_subject)
            ->setFrom(self::$mail_from)
            ->setTo(self::$mail_to)
            ->setBody($content)
        ;

        foreach ($attachments as $attachment) {
            $message->attach(\Swift_Attachment::fromPath($attachment));
        }

        self::$mailer->send($message);
    }

    /**
     * @param AfterScenarioScope $event
     */
    protected function createScreenshot($event = null)
    {
        $path = null;

        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $bin  = $this->getSession()->getDriver()->getScreenshot();

            $path = sprintf(self::$tmpPath.DIRECTORY_SEPARATOR.'%s.png', md5($bin));
            file_put_contents($path, $bin);

            if ($event) {
                self::registerScreenshot($event, $path);
            }

            $this->registerMailer();
        }

        return $path;
    }

    /**
     * @param AfterScenarioScope $event
     * @param type $path
     */
    protected static function registerScreenshot(AfterScenarioScope $event, $path)
    {
        $name = sprintf("%s:%s", $event->getFeature()->getFile(), $event->getScenario()->getLine());

        self::$images[$name] = $path;
    }

    /**
     *
     */
    protected function registerMailer()
    {
        self::$mailer = self::$mailer ?: $this->getMailer();
    }

    /**
     * @return \Swift_Mailer
     */
    protected function getMailer()
    {
        $transport = \Swift_SmtpTransport::newInstance(self::$mailer_host, self::$mailer_port, self::$mailer_encryption)
            ->setUsername(self::$mailer_username)
            ->setPassword(self::$mailer_password)
        ;

        return \Swift_Mailer::newInstance($transport);
    }
}
