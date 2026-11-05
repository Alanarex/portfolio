<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Contracts;

use Modules\GitHubActivity\Data\PublicGitHubActivityData;

interface PublicGitHubActivityReader
{
    public function current(): ?PublicGitHubActivityData;
}
