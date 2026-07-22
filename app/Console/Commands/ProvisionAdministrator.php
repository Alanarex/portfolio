<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Security\SecurityAudit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class ProvisionAdministrator extends Command
{
    protected $signature = 'admin:provision {email? : Administrator email address}';

    protected $description = 'Provision the single private administrator without public registration';

    public function handle(): int
    {
        $email = Str::lower(trim((string) ($this->argument('email') ?: $this->ask('Administrator email'))));
        $password = (string) $this->secret('Administrator password');
        $confirmation = (string) $this->secret('Confirm administrator password');
        $validator = validator(compact('email', 'password', 'confirmation'), [
            'email' => ['required', 'email:rfc'],
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
            'confirmation' => ['same:password'],
        ]);

        if ($validator->fails()) {
            SecurityAudit::record('admin.provision.failed', ['reason' => 'validation']);
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        return DB::transaction(function () use ($email, $password): int {
            $administrator = User::query()->where('is_administrator', true)->lockForUpdate()->first();
            if ($administrator !== null && $administrator->email !== $email) {
                SecurityAudit::record('admin.provision.failed', ['reason' => 'administrator_exists']);
                $this->error('An administrator already exists.');

                return self::FAILURE;
            }

            $user = User::query()->firstOrNew(['email' => $email]);
            $user->forceFill([
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'is_administrator' => true,
                'remember_token' => null,
                'auth_version' => $user->exists ? $user->auth_version + 1 : 1,
            ])->save();
            SecurityAudit::record('admin.provision.succeeded', ['user_id' => $user->getKey()]);
            $this->info('Administrator provisioned.');

            return self::SUCCESS;
        });
    }
}
