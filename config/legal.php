<?php

declare(strict_types=1);

/*
 * Informations des mentions légales. Aucune valeur personnelle n'est versionnée :
 * elles sont renseignées par l'environnement (voir .env.example).
 */
return [
    'publisher_name' => env('LEGAL_PUBLISHER_NAME'),
    'publisher_status' => env('LEGAL_PUBLISHER_STATUS', 'Personne physique'),
    'publisher_email' => env('LEGAL_PUBLISHER_EMAIL'),
    'director' => env('LEGAL_PUBLICATION_DIRECTOR'),
    'host_name' => env('LEGAL_HOST_NAME', 'OVH SAS'),
    'host_address' => env('LEGAL_HOST_ADDRESS', '2 rue Kellermann, 59100 Roubaix, France'),
    'host_url' => env('LEGAL_HOST_URL', 'https://www.ovhcloud.com'),
    'updated_at' => env('LEGAL_UPDATED_AT'),
];
