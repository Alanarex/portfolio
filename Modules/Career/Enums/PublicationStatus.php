<?php

declare(strict_types=1);

namespace Modules\Career\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
