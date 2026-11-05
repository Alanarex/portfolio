<?php

declare(strict_types=1);

namespace Modules\Posts\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
