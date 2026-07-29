<?php

declare(strict_types=1);

namespace Modules\Profile\Contracts;

interface PublicCvReader
{
    public function available(string $locale): bool;
}
