<?php

declare(strict_types=1);

return [
    'flash' => [
        'profile' => 'Profile saved.',
        'password' => 'Password changed. Other devices have been signed out.',
        'session_revoked' => 'Session signed out.',
        'session_gone' => 'This session no longer exists.',
        'others_revoked' => 'All other devices have been signed out.',
    ],
    'validation' => [
        'name_required' => 'The name is required.',
        'name_max' => 'The name may not be longer than 120 characters.',
        'email_required' => 'The email address is required.',
        'email_invalid' => 'Enter a valid email address.',
        'email_unavailable' => 'This email address cannot be used.',
        'current_required' => 'Enter your current password.',
        'current_wrong' => 'The current password is incorrect.',
        'throttle' => 'Too many attempts. Try again in :seconds seconds.',
        'password_required' => 'The new password is required.',
        'password_max' => 'The password is too long.',
        'password_different' => 'The new password must be different from the current one.',
        'password_confirmed' => 'The confirmation does not match the new password.',
    ],
];
