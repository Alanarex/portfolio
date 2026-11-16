<?php

declare(strict_types=1);

return [
    'password' => [
        'letters' => 'The password must contain at least one letter.',
        'mixed' => 'The password must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The password must contain at least one number.',
        'symbols' => 'The password must contain at least one symbol.',
        'uncompromised' => 'This password has appeared in a data leak. Choose another one.',
    ],
    'min' => ['string' => 'The password must be at least :min characters.'],
];
