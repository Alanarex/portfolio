<?php

declare(strict_types=1);

namespace Modules\Contact\Data;

final readonly class ContactSubmissionData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $subject,
        public string $message,
        public string $locale,
    ) {}
}
