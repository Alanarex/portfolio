<?php

declare(strict_types=1);

namespace Modules\Settings\Contracts;

use Modules\Settings\Data\PublicSettingsData;

interface PublicSettingsReader
{
    public function forLocale(string $locale): ?PublicSettingsData;
}
