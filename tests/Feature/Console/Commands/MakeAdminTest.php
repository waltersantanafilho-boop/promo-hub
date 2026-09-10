<?php

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MakeAdminTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_promotes_an_existing_user_without_changing_the_password_or_creating_users(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $passwordHash = $user->password;

        $this->artisan('user:make-admin', ['email' => $user->email])
            ->expectsOutput("Role admin atribuída ao usuário {$user->email}.")
            ->assertSuccessful();

        $this->assertTrue($user->refresh()->hasRole('admin'));
        $this->assertTrue($user->can('access admin'));
        $this->assertSame($passwordHash, $user->password);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_repeated_promotion_preserves_existing_roles_without_duplicate_assignments(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('editor');
        $this->artisan('user:make-admin', ['email' => $user->email])->assertSuccessful();

        $this->artisan('user:make-admin', ['email' => $user->email])->assertSuccessful();

        $this->assertTrue($user->refresh()->hasAllRoles(['admin', 'editor']));
        $this->assertDatabaseCount('model_has_roles', 2);
    }

    public function test_missing_email_returns_an_error_without_creating_or_promoting_users(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $passwordHash = $user->password;

        $this->artisan('user:make-admin', ['email' => 'missing@example.com'])
            ->expectsOutput('Usuário não encontrado para o email: missing@example.com.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('model_has_roles', 0);
        $this->assertSame($passwordHash, $user->refresh()->password);
    }

    public function test_missing_admin_role_returns_seeder_instructions_without_changing_the_user(): void
    {
        $user = User::factory()->create();
        $passwordHash = $user->password;

        $this->artisan('user:make-admin', ['email' => $user->email])
            ->expectsOutput('Role admin não configurada. Execute: php artisan db:seed --class=RolesAndPermissionsSeeder')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('model_has_roles', 0);
        $this->assertDatabaseCount('roles', 0);
        $this->assertDatabaseCount('permissions', 0);
        $this->assertSame($passwordHash, $user->refresh()->password);
    }
}
