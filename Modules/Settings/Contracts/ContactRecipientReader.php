<?php

declare(strict_types=1);

namespace Modules\Settings\Contracts;

interface ContactRecipientReader
{
    public function recipient(): ?string;
}
