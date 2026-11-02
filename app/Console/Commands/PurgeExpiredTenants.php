<?php

namespace App\Console\Commands;

use App\DeleteUser;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredTenants extends Command
{
    protected $signature = 'users:purge-expired {--dry-run : Compter les comptes sans les supprimer}';

    protected $description = 'Supprimer les comptes locataires deux ans après leur création';

    public function handle(DeleteUser $deleteUser): int
    {
        $count = 0;
        foreach (User::query()->where('role', 'locataire')->lazyById(100) as $candidate) {
            DB::transaction(function () use ($candidate, $deleteUser, &$count): void {
                $user = User::query()->lockForUpdate()->find($candidate->id);
                if (! $user || ! $user->hasExpired()) {
                    return;
                }
                if (! $this->option('dry-run')) {
                    $deleteUser->handle($user);
                }
                $count++;
            });
        }
        $this->info($count.($this->option('dry-run') ? ' compte(s) à supprimer.' : ' compte(s) supprimé(s).'));

        return self::SUCCESS;
    }
}
