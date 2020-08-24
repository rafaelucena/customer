<?php

// tests/Util/CalculatorTest.php
namespace App\Tests\Util;

// use App\Util\Calculator;

use App\Entity\Review;
use App\Services\OvertimeService;
use PHPUnit\Framework\TestCase;

class OvertimeTest extends TestCase
{
    private function callFunction($function, $parameters)
    {
        $reflectionMethod = new \ReflectionMethod(OvertimeService::class, $function);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs(new OvertimeService(), $parameters);
    }

    public function testGroupType()
    {
        $firstDate = new \DateTime();
        $secondDate = clone $firstDate;
        $secondDate->modify('+28 days');

        $groupBy = $this->callFunction('groupType', [$firstDate, $secondDate]);
        $this->assertEquals($groupBy, 'daily');

        $secondDate->modify('+28 days');
        $groupBy = $this->callFunction('groupType', [$firstDate, $secondDate]);
        $this->assertEquals($groupBy, 'weekly');

        $secondDate->modify('+180 days');
        $groupBy = $this->callFunction('groupType', [$firstDate, $secondDate]);
        $this->assertEquals($groupBy, 'monthly');
    }

    public function testGroupList()
    {
        $daysAgo = random_int(180, 350);
        $period = 28;
        $firstDate = new \DateTime();
        $firstDate->modify(sprintf('-%d days', $daysAgo));
        $secondDate = clone $firstDate;
        $secondDate->modify(sprintf('+%d days', $period));

        $list = [];
        for ($x = 0; $x < 100; $x++) {
            $date = new \DateTime();
            $interval = random_int($daysAgo, $daysAgo + $period);
            $date->modify(sprintf('-%d days', $interval));

            $item = new Review();
            $item->setScore(random_int(1, 10));
            $item->setCreatedDate($date);

            $list[] = $item;
        }

        $groupBy = $this->callFunction('groupList', [$list, 'daily']);
        $this->assertIsArray($groupBy);
        $this->assertArrayHasKey('review-count', $groupBy[0]);
        $this->assertArrayHasKey('date-group', $groupBy[0]);
        $this->assertArrayHasKey('average-score', $groupBy[0]);
    }
}