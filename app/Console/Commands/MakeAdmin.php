<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

#[Signature('user:make-admin {email : Email do usuário existente}')]
#[Description('Atribui a role admin a um usuário existente, sem alterar sua senha')]
class MakeAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error("Usuário não encontrado para o email: {$email}.");

            return self::FAILURE;
        }

        $role = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();

        if ($role === null) {
            $this->error('Role admin não configurada. Execute: php artisan db:seed --class=RolesAndPermissionsSeeder');

            return self::FAILURE;
        }

        $user->assignRole($role);

        $this->info("Role admin atribuída ao usuário {$user->email}.");

        return self::SUCCESS;
    }
}
