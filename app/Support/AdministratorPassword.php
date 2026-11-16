<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rules\Password;

/**
 * Politique de mot de passe de l'administrateur, partagée par `admin:provision` et le panneau de compte.
 * Le front (indicateur de robustesse) en reflète les mêmes critères : 12 caractères, majuscule, minuscule, chiffre, symbole.
 */
final class AdministratorPassword
{
    public const MIN_LENGTH = 12;

    public static function rule(): Password
    {
        return Password::min(self::MIN_LENGTH)->letters()->mixedCase()->numbers()->symbols();
    }
}
