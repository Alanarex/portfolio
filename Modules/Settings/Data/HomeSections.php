<?php

declare(strict_types=1);

namespace Modules\Settings\Data;

/**
 * Sections de la page d'accueil que l'on peut afficher ou masquer depuis les paramètres.
 * Chaque section est stockée comme drapeau public « section_<id> » ; un drapeau absent vaut « affichée ».
 */
final class HomeSections
{
    /** @var array<string, string> identifiant d'ancre => libellé du tableau de bord */
    public const LABELS = [
        'actualites' => 'Actualités',
        'projets' => 'Projets à la une',
        'preuves' => 'Résultats vérifiés',
        'experience' => 'Parcours',
        'competences' => 'Compétences',
        'activite' => 'Activité Git',
        'formation' => 'Formation et langues',
        'atelier' => 'Atelier 3D',
        'interets' => 'En dehors du code',
        'contact' => 'Contact',
    ];

    /** @return list<string> */
    public static function ids(): array
    {
        return array_keys(self::LABELS);
    }

    public static function flagKey(string $id): string
    {
        return 'section_'.$id;
    }

    /** @param array<string, bool> $featureFlags */
    public static function visible(array $featureFlags, string $id): bool
    {
        return $featureFlags[self::flagKey($id)] ?? true;
    }
}
