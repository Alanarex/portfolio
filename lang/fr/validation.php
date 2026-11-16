<?php

declare(strict_types=1);

// Messages de la règle Password (politique de l'administrateur) ; les autres règles ont leurs messages dans les requêtes.
return [
    'password' => [
        'letters' => 'Le mot de passe doit contenir au moins une lettre.',
        'mixed' => 'Le mot de passe doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
        'symbols' => 'Le mot de passe doit contenir au moins un symbole.',
        'uncompromised' => 'Ce mot de passe est apparu dans une fuite de données. Choisissez-en un autre.',
    ],
    'min' => ['string' => 'Le mot de passe doit contenir au moins :min caractères.'],
];
