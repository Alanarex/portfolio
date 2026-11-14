<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Data;

use DateTimeImmutable;

final readonly class ContributionGridData
{
    /**
     * @param  list<list<array{date: string, count: int, level: int}>>  $weeks  Columns of 7 days, Sunday first; days after today are omitted.
     * @param  array<string, int>  $totals  Contributions per source over the displayed period.
     * @param  list<array{month: string, count: int}>  $months
     */
    public function __construct(
        public array $weeks,
        public int $total,
        public array $totals,
        public array $months,
        public DateTimeImmutable $syncedAt,
    ) {}
}
