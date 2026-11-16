<?php

declare(strict_types=1);

return [
    'flash' => [
        'profile' => 'Profil enregistré.',
        'password' => 'Mot de passe modifié. Les autres appareils ont été déconnectés.',
        'session_revoked' => 'Session déconnectée.',
        'session_gone' => 'Cette session n’existe plus.',
        'others_revoked' => 'Tous les autres appareils ont été déconnectés.',
    ],
    'validation' => [
        'name_required' => 'Le nom est obligatoire.',
        'name_max' => 'Le nom ne peut pas dépasser 120 caractères.',
        'email_required' => 'L’adresse e-mail est obligatoire.',
        'email_invalid' => 'Saisissez une adresse e-mail valide.',
        'email_unavailable' => 'Cette adresse e-mail ne peut pas être utilisée.',
        'current_required' => 'Saisissez votre mot de passe actuel.',
        'current_wrong' => 'Le mot de passe actuel est incorrect.',
        'throttle' => 'Trop d’essais. Réessayez dans :seconds secondes.',
        'password_required' => 'Le nouveau mot de passe est obligatoire.',
        'password_max' => 'Le mot de passe est trop long.',
        'password_different' => 'Le nouveau mot de passe doit être différent de l’actuel.',
        'password_confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
    ],
];
