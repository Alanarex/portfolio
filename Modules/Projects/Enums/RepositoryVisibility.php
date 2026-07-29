<?php

declare(strict_types=1);

namespace Modules\Projects\Enums;

enum RepositoryVisibility: string
{
    case None = 'none';
    case Public = 'public';
    case Private = 'private';
}
