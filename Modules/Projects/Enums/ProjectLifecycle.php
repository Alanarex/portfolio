<?php

declare(strict_types=1);

namespace Modules\Projects\Enums;

enum ProjectLifecycle: string
{
    case Unspecified = 'unspecified';
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Maintained = 'maintained';
}
