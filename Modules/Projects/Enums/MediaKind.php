<?php

declare(strict_types=1);

namespace Modules\Projects\Enums;

enum MediaKind: string
{
    case Image = 'image';
    case Screenshot = 'screenshot';
    case Poster = 'poster';
    case Document = 'document';
    case Cv = 'cv';
}
