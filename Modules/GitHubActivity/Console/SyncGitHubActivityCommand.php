<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Console;

use Illuminate\Console\Command;
use Modules\GitHubActivity\Application\SyncGitHubActivity;

final class SyncGitHubActivityCommand extends Command
{
    protected $signature = 'portfolio:sync-github';

    protected $description = 'Fetch public GitHub repositories and activity for the home page';

    public function handle(SyncGitHubActivity $sync): int
    {
        if ($sync->execute()) {
            $this->info('GitHub activity synchronized.');

            return self::SUCCESS;
        }

        $this->warn('GitHub activity sync failed; the previous snapshot is kept.');

        return self::FAILURE;
    }
}
