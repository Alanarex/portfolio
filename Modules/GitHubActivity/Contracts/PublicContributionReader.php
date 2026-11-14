<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Contracts;

use Modules\GitHubActivity\Data\ContributionGridData;

interface PublicContributionReader
{
    public function grid(): ?ContributionGridData;
}
