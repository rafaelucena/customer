<?php

namespace App\Services;

class OvertimeService
{
    /**
     * @param array $list
     * @param \DateTime $since
     * @param \DateTime $until
     * @return array
     */
    public function map(array $list, \DateTime $since, \DateTime $until): array
    {
        $groupType = $this->groupType($since, $until);
        return $this->groupList($list, $groupType);
    }

    /**
     * @param array $list
     * @return array
     */
    public function mapOne(array $list): array
    {
        $arrays = $this->groupList($list);
        return current($arrays);
    }

    /**
     * @param \DateTime $since
     * @param \DateTime $until
     * @return string|null
     */
    private function groupType(\DateTime $since, \DateTime $until): ?string
    {
        $days = $since->diff($until)->format("%a");

        if ($days >= 1 && $days <= 29) {
            return 'daily';
        } elseif ($days >= 30 && $days <= 89) {
            return 'weekly';
        } elseif ($days > 89) {
            return 'monthly';
        }

        return null;
    }

    /**
     * @param array $list
     * @param string $groupType
     * @return array
     */
    private function groupList(array $list, string $groupType = ''): array
    {
        $mapped = [];
        foreach ($list as $item) {
            $date = $item->getCreatedDate();

            $group = 'alltime';
            if ($groupType == 'daily') {
                $group = $date->format('Y-m-d');
            } elseif ($groupType === 'weekly') {
                $group = $date->format("W");
            } elseif ($groupType == 'monthly') {
                $group = $date->format('Y-m');
            }

            if (empty($mapped[$group])) {
                $mapped[$group]['review-count'] = 1;
                $mapped[$group]['score'][] = $item->getScore();
                $mapped[$group]['date-group'] = $group;
            } else {
                $mapped[$group]['review-count']++;
                $mapped[$group]['score'][] = $item->getScore();
            }
        }

        foreach ($mapped as $key => $item) {
            $mapped[$key]['average-score'] = round(array_sum($item['score']) / $item['review-count'], 1);
            unset($mapped[$key]['score']);
        }

        return array_values($mapped);
    }
}