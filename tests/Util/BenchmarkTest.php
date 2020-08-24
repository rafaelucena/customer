<?php

namespace App\Tests\Util;

use App\Entity\Review;
use App\Services\BenchmarkService;
use App\Services\OvertimeService;
use PHPUnit\Framework\TestCase;

class BenchmarkTest extends TestCase
{
    private function callFunction($function, $parameters)
    {
        $reflectionMethod = new \ReflectionMethod(BenchmarkService::class, $function);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs(new BenchmarkService(), $parameters);
    }

    public function testMedian()
    {
        $numbers = [
            1,
            3,
            5,
        ];
        $median = $this->callFunction('getMedian', [$numbers]);
        $this->assertEquals(3, $median);

        $numbers = [
            2,
            4,
            8,
            10,
            12,
            14,
        ];
        $median = $this->callFunction('getMedian', [$numbers]);
        $this->assertEquals(9, $median);
    }

    public function testQuartiles()
    {
        $numbers = [
            1,
            3,
            5,
            7,
            9,
        ];
        $quartile = $this->callFunction('quartiles', [$numbers, 1]);
        $this->assertEquals('bottom', $quartile);

        $quartile = $this->callFunction('quartiles', [$numbers, 5]);
        $this->assertEquals(null, $quartile);

        $quartile = $this->callFunction('quartiles', [$numbers, 8.5]);
        $this->assertEquals('top', $quartile);
    }
}