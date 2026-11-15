<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Settings\Models\PublicFeatureFlag;
use Modules\Settings\Models\SiteSetting;
use Modules\Settings\Models\SocialLink;

final class SettingsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'fr', 'label' => 'Français', 'direction' => 'ltr', 'status' => 'active'],
            ['code' => 'en', 'label' => 'English', 'direction' => 'ltr', 'status' => 'active'],
            ['code' => 'ar', 'label' => 'العربية', 'direction' => 'rtl', 'status' => 'readiness'],
        ] as $locale) {
            DB::table('locales')->updateOrInsert(
                ['code' => $locale['code']],
                [...$locale, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        $this->contact();
    }

    /**
     * Réglages de la section contact. Idempotent et jamais destructif : ce qui a été modifié dans le
     * tableau de bord n'est pas écrasé, et une coordonnée vide dans l'environnement n'ajoute rien.
     * Seuls les profils déjà publics (GitHub, GitLab) ont une valeur par défaut ; l'e-mail, le
     * téléphone et les autres liens viennent de config/contact.php (variables CONTACT_*).
     */
    private function contact(): void
    {
        $settings = SiteSetting::query()->firstOrCreate(['key' => 'main'], [
            'site_name' => 'Alaa Khalil',
            'default_locale' => 'fr',
            'show_email' => false,
            'show_phone' => false,
        ]);

        $email = config('contact.email');
        if (filled($email) && blank($settings->contact_email)) {
            $settings->forceFill(['contact_email' => $email, 'show_email' => true])->save();
        }
        $phone = config('contact.phone');
        if (filled($phone) && blank($settings->contact_phone)) {
            $settings->forceFill(['contact_phone' => $phone, 'show_phone' => true])->save();
        }

        $githubUser = config('services.github.username');
        $gitlabUser = config('services.gitlab.username');
        $gitlabBase = rtrim((string) config('services.gitlab.url', 'https://gitlab.com'), '/');
        $links = [
            'github' => filled($githubUser) ? 'https://github.com/'.$githubUser : null,
            'gitlab' => filled($gitlabUser) ? $gitlabBase.'/'.$gitlabUser : null,
            'linkedin' => config('contact.linkedin_url'),
            'whatsapp' => config('contact.whatsapp_url'),
            'scheduling' => config('contact.scheduling_url'),
        ];
        $order = 10;
        foreach ($links as $platform => $url) {
            if (filled($url)) {
                SocialLink::query()->firstOrCreate(
                    ['site_setting_id' => $settings->id, 'platform' => $platform],
                    ['url' => $url, 'is_enabled' => true, 'is_public' => true, 'sort_order' => $order],
                );
            }
            $order += 10;
        }

        // Formulaire de contact (actif seulement si un e-mail de réception existe) et grille d'activité.
        foreach (['contact' => true, 'activity' => true] as $key => $enabled) {
            PublicFeatureFlag::query()->firstOrCreate(
                ['site_setting_id' => $settings->id, 'key' => $key],
                ['enabled' => $enabled],
            );
        }
    }
}
