<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace IntegralService\Context;

/**
 *
 * @author Julien Rouvier <julien@integral-service.fr>
 */
trait CoverageTrait
{

    use Behat\Behat\Hook\Scope\BeforeScenarioScope;
    use SebastianBergmann\CodeCoverage\CodeCoverage;
    use SebastianBergmann\CodeCoverage\Filter;
    use SebastianBergmann\CodeCoverage\Report\Clover;


    /**
     * @var PHP_CodeCoverage
     */
    private static $coverage;

    /** @BeforeSuite */
    public static function setup()
    {
        $filter         = new Filter();
        $filter->addDirectoryToWhitelist(__DIR__ . "/../../src");
        self::$coverage = new CodeCoverage(null, $filter);
    }

    /** @AfterSuite */
    public static function tearDown()
    {
        $writer = new Clover();
        $writer->process(self::$coverage, __DIR__ . "/../../results/behat_coverage.xml");
    }

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
