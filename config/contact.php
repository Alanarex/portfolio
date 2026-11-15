<?php

declare(strict_types=1);

/*
 * Coordonnées publiques de la section contact, utilisées par SettingsDatabaseSeeder.
 * Aucune valeur personnelle n'est versionnée : elles viennent de l'environnement
 * (voir .env.example). Une valeur vide n'affiche aucun canal correspondant.
 */
return [
    'email' => env('CONTACT_EMAIL'),
    'phone' => env('CONTACT_PHONE'),
    'linkedin_url' => env('CONTACT_LINKEDIN_URL'),
    'whatsapp_url' => env('CONTACT_WHATSAPP_URL'),
    'scheduling_url' => env('CONTACT_SCHEDULING_URL'),
];
