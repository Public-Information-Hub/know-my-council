<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetSuperAdminCommand extends Command
{
    protected $signature = 'kmc:user:superadmin
        {--id= : User id}
        {--email= : User email address}
        {--handle= : User handle}
        {--remove : Remove superadmin access instead of granting it}';

    protected $description = 'Grant or revoke unrestricted superadmin access for a user.';

    public function handle(): int
    {
        $criteria = 0;
        $query = User::query();

        if ($this->option('id') !== null) {
            $criteria++;
            $query->whereKey($this->option('id'));
        }

        if ($this->option('email') !== null) {
            $criteria++;
            $query->whereRaw('lower(email) = ?', [Str::lower(trim((string) $this->option('email')))]);
        }

        if ($this->option('handle') !== null) {
            $criteria++;
            $query->whereRaw('lower(handle) = ?', [Str::lower(trim((string) $this->option('handle')))]);
        }

        if ($criteria !== 1) {
            $this->error('Specify exactly one of --id, --email, or --handle.');

            return self::FAILURE;
        }

        $user = $query->first();

        if (! $user instanceof User) {
            $this->error('No matching user was found.');

            return self::FAILURE;
        }

        $isRemove = (bool) $this->option('remove');
        $user->forceFill([
            'is_super_admin' => ! $isRemove,
        ])->save();

        $this->info(sprintf(
            'User %s (%s) is now %s.',
            $user->display_name ?? $user->name,
            $user->email,
            $isRemove ? 'not a superadmin' : 'a superadmin'
        ));

        return self::SUCCESS;
    }
}
