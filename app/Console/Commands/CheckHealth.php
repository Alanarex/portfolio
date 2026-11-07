<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Health\HealthChecker;
use Illuminate\Console\Command;

final class CheckHealth extends Command
{
    protected $signature = 'portfolio:health';

    protected $description = 'Check the database, cache, Redis and storage used by the portfolio';

    public function handle(HealthChecker $health): int
    {
        $result = $health->run();

        foreach ($result['checks'] as $name => $status) {
            $this->line(sprintf('%-10s %s', $name, $status));
        }

        return $result['status'] === HealthChecker::OK ? self::SUCCESS : self::FAILURE;
    }
}
