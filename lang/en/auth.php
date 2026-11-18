<?php

declare(strict_types=1);

return [
    'failed' => 'Incorrect credentials.',
    'throttle' => 'Too many attempts. Try again in :minutes min.',
    'forgot' => [
        'sent' => 'If an administrator account matches this address, an email with a reset link has just been sent. The link expires quickly.',
        'invalid' => 'This reset link is invalid or has expired. Please request a new one.',
        'throttle' => 'Too many requests. Try again later.',
        'done' => 'Password changed. Sign in with the new password.',
    ],
    'mail' => [
        'subject' => 'Administrator password reset',
        'greeting' => 'Hello,',
        'intro' => 'A password reset was requested for the portfolio administration.',
        'action' => 'Choose a new password',
        'expires' => 'This link is single-use and expires in :minutes minutes.',
        'ignore' => 'If you did not request this, ignore this message: your password stays unchanged.',
    ],
];
