<?php

declare(strict_types=1);

namespace Modules\Projects\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
