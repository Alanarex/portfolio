<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Security\SecurityAudit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

final class ResetAdministratorPassword extends Command
{
    protected $signature = 'admin:reset-password';

    protected $description = 'Interactively reset the administrator password and revoke active sessions';

    public function handle(): int
    {
        $administrator = User::query()->where('is_administrator', true)->first();
        if ($administrator === null) {
            $this->error('No administrator exists.');
            SecurityAudit::record('admin.password_reset.failed', ['reason' => 'administrator_missing']);

            return self::FAILURE;
        }

        $password = (string) $this->secret('New administrator password');
        $confirmation = (string) $this->secret('Confirm administrator password');
        $validator = validator(compact('password', 'confirmation'), [
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
            'confirmation' => ['same:password'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            SecurityAudit::record('admin.password_reset.failed', ['reason' => 'validation']);

            return self::FAILURE;
        }

        DB::transaction(static function () use ($administrator, $password): void {
            $administrator->forceFill([
                'password' => Hash::make($password),
                'remember_token' => null,
                'auth_version' => $administrator->auth_version + 1,
            ])->save();
        });

        SecurityAudit::record('admin.password_reset.succeeded', ['user_id' => $administrator->getKey()]);
        $this->info('Administrator password reset and active sessions revoked.');

        return self::SUCCESS;
    }
}
