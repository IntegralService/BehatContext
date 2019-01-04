<?php

namespace IntegralService\BehatContext;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;

/**
 *
 * @author Julien Rouvier <julien@integral-service.fr>
 */
class CoverageContext extends RawMinkContext
{
    /**
     * @var PHP_CodeCoverage
     */
    private static $coverage;

    /** @BeforeSuite */
    public static function setup()
    {
        $filter = new Filter();
        $filter->addDirectoryToWhitelist(__DIR__ . "/../../src");
        self::$coverage = new CodeCoverage(null, $filter);
    }

    /** @AfterSuite */
    public static function tearDown()
    {
        $writer = new Clover();
        $writer->process(self::$coverage, __DIR__ . "/../../results/behat_coverage.xml");
    }

    /**
     * @param BeforeScenarioScope $scope
     * @return string
     */
    private function getCoverageKeyFromScope(BeforeScenarioScope $scope)
    {
        $name = $scope->getFeature()->getTitle() . '::' . $scope->getScenario()->getTitle();

        return $name;
    }

    /**
     * @BeforeScenario
     */
    public function startCoverage(BeforeScenarioScope $scope)
    {
        self::$coverage->start($this->getCoverageKeyFromScope($scope));
    }

    /** @AfterScenario */
    public function stopCoverage()
    {
        self::$coverage->stop();
    }
}
