<?php

declare(strict_types=1);

namespace Modules\Settings\Infrastructure;

use Modules\Settings\Contracts\ContactRecipientReader;
use Modules\Settings\Models\SiteSetting;

final class DatabaseContactRecipientReader implements ContactRecipientReader
{
    public function recipient(): ?string
    {
        $settings = SiteSetting::query()
            ->where('key', 'main')
            ->with('featureFlags')
            ->first();

        if ($settings === null || blank($settings->contact_email)) {
            return null;
        }

        $enabled = $settings->featureFlags
            ->firstWhere('key', 'contact')
            ?->enabled === true;

        return $enabled ? $settings->contact_email : null;
    }
}
