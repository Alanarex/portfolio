<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Infrastructure;

use DateTimeImmutable;
use Modules\GitHubActivity\Contracts\PublicContributionReader;
use Modules\GitHubActivity\Data\ContributionGridData;
use Modules\GitHubActivity\Models\ContributionCalendar;

/** Merges every stored calendar (GitHub + GitLab) into one 53-week grid ending today. */
final class DatabasePublicContributionReader implements PublicContributionReader
{
    private const WEEKS = 53;

    public function grid(): ?ContributionGridData
    {
        $calendars = ContributionCalendar::query()->get();
        if ($calendars->isEmpty()) {
            return null;
        }

        $merged = [];
        $totals = [];
        $syncedAt = null;
        foreach ($calendars as $calendar) {
            $syncedAt = $syncedAt === null || $calendar->synced_at->toDateTimeImmutable() < $syncedAt ? $calendar->synced_at->toDateTimeImmutable() : $syncedAt;
            $totals[$calendar->source] = 0;
            foreach ($calendar->days as $date => $count) {
                $merged[$date] = ($merged[$date] ?? 0) + $count;
            }
        }

        $today = new DateTimeImmutable('today');
        // Sunday of the first displayed week.
        $start = $today->modify('-'.((int) $today->format('w') + 7 * (self::WEEKS - 1)).' days');

        $max = 0;
        foreach ($merged as $date => $count) {
            if ($date >= $start->format('Y-m-d') && $date <= $today->format('Y-m-d')) {
                $max = max($max, $count);
            }
        }

        $weeks = [];
        $months = [];
        $total = 0;
        for ($week = 0; $week < self::WEEKS; $week++) {
            $column = [];
            for ($weekday = 0; $weekday < 7; $weekday++) {
                $day = $start->modify('+'.($week * 7 + $weekday).' days');
                if ($day > $today) {
                    break;
                }
                $count = $merged[$day->format('Y-m-d')] ?? 0;
                $total += $count;
                $months[$day->format('Y-m')] = ($months[$day->format('Y-m')] ?? 0) + $count;
                $column[] = ['date' => $day->format('Y-m-d'), 'count' => $count, 'level' => $this->level($count, $max)];
            }
            $weeks[] = $column;
        }

        foreach ($calendars as $calendar) {
            $totals[$calendar->source] = array_sum(array_filter(
                $calendar->days,
                fn (string $date): bool => $date >= $start->format('Y-m-d') && $date <= $today->format('Y-m-d'),
                ARRAY_FILTER_USE_KEY,
            ));
        }

        $monthList = [];
        foreach ($months as $month => $count) {
            $monthList[] = ['month' => $month, 'count' => $count];
        }

        return new ContributionGridData($weeks, $total, $totals, $monthList, $syncedAt ?? new DateTimeImmutable);
    }

    private function level(int $count, int $max): int
    {
        if ($count <= 0 || $max <= 0) {
            return 0;
        }

        return min(4, max(1, (int) ceil($count / $max * 4)));
    }
}
