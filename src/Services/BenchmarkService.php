<?php

namespace App\Services;

class BenchmarkService
{
    /**
     * @param array $target
     * @param array $list
     * @return array
     */
    public function map(array $target, array $list): array
    {
        $map['target'] = $target;
        $map['hotels'] = $list;
        $map['hotels'][] = $map['target'];

        $return = [];
        $return['hotel_average'] = $map['target']['average-score'] * 10;

        $others = [
            'count' => 0,
            'reviews' => [],
        ];
        foreach ($map['hotels'] as $item) {
            if ($item['hotel'] == $map['target']['hotel']) {
                continue;
            }
            $others['count']++;
            $others['reviews'][] = $item['average-score'] * 10;
        }

        $others['average'] = round(array_sum($others['reviews']) / $others['count'], 2);
        $return['average_of_all_other_hotels'] = $others['average'];
        $return['quarter_indicator'] = $this->quartiles($others['reviews'], $return['hotel_average']);

        return $return;
    }

    /**
     * @param array $numbers
     * @param float $target
     * @return string|null
     */
    private function quartiles(array $numbers, float $target): ?string
    {
        sort($numbers);
        $second = $this->getMedian($numbers);

        $subarray = [];
        foreach ($numbers as $number) {
            if ($number < $second) {
                $subarray['first'][] = $number;
            } elseif ($number > $second) {
                $subarray['third'][] = $number;
            }
        }
        $first = $this->getMedian($subarray['first']);
        $third = $this->getMedian($subarray['third']);

        if ($target < $first) {
            return 'bottom';
        } elseif ($target > $third) {
            return 'top';
        }

        return null;
    }

    /**
     * @param array $numbers
     * @return float
     */
    private function getMedian(array $numbers): float
    {
        sort($numbers);
        $count = count($numbers);
        $middle = floor(($count-1)/2);

        $median = 0;
        if ($count % 2) {
            $median = $numbers[$middle];
        } else {
            $median = (($numbers[$middle] + $numbers[$middle + 1])/2);
        }

        return round($median, 2);
    }
}