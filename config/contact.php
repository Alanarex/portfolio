<?php

declare(strict_types=1);

/*
 * Coordonnées publiques de la section contact, utilisées par SettingsDatabaseSeeder.
 * L'e-mail et le téléphone ne sont jamais versionnés : ils viennent de l'environnement
 * (voir .env.example). Le profil LinkedIn public a une valeur par défaut, surchargeable.
 * Une valeur vide n'affiche aucun canal correspondant.
 */
return [
    'email' => env('CONTACT_EMAIL'),
    'phone' => env('CONTACT_PHONE'),
    'linkedin_url' => env('CONTACT_LINKEDIN_URL') ?: 'https://www.linkedin.com/in/alaa-khalil-dev/',
    'whatsapp_url' => env('CONTACT_WHATSAPP_URL'),
    'scheduling_url' => env('CONTACT_SCHEDULING_URL'),
];
