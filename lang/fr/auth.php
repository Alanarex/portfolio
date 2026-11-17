<?php

declare(strict_types=1);

return [
    'failed' => 'Identifiants incorrects.',
    'throttle' => 'Trop de tentatives. Réessayez dans :minutes min.',
    'forgot' => [
        'sent' => 'Si un compte administrateur correspond à cette adresse, un e-mail contenant un lien de réinitialisation vient d’être envoyé. Le lien expire rapidement.',
        'invalid' => 'Ce lien de réinitialisation est invalide ou a expiré. Faites une nouvelle demande.',
        'throttle' => 'Trop de demandes. Réessayez plus tard.',
        'done' => 'Mot de passe modifié. Connectez-vous avec le nouveau mot de passe.',
    ],
    'mail' => [
        'subject' => 'Réinitialisation du mot de passe administrateur',
        'greeting' => 'Bonjour,',
        'intro' => 'Une demande de réinitialisation du mot de passe de l’administration du portfolio a été faite.',
        'action' => 'Choisir un nouveau mot de passe',
        'expires' => 'Ce lien est à usage unique et expire dans :minutes minutes.',
        'ignore' => 'Si vous n’êtes pas à l’origine de cette demande, ignorez ce message : votre mot de passe reste inchangé.',
    ],
];
