<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeder_configures_access_without_creating_users(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('permissions', 6);
        $this->assertSame(['web'], Role::query()->distinct()->pluck('guard_name')->all());
        $this->assertSame(['web'], Permission::query()->distinct()->pluck('guard_name')->all());
    }

    public function test_admin_receives_all_initial_permissions_through_the_gate(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();

        $user->assignRole('admin');

        $this->assertTrue($user->can('access admin'));
        $this->assertTrue($user->can('manage categories'));
        $this->assertTrue($user->can('manage brands'));
        $this->assertTrue($user->can('manage stores'));
        $this->assertTrue($user->can('manage products'));
        $this->assertTrue($user->can('manage offers'));
    }

    public function test_permissions_are_available_when_parent_seeding_suspends_model_events(): void
    {
        Model::withoutEvents(fn () => $this->seed(RolesAndPermissionsSeeder::class));
        $user = User::factory()->create();

        $user->assignRole('admin');

        $this->assertTrue($user->can('access admin'));
        $this->assertTrue($user->can('manage offers'));
    }

    public function test_editor_receives_only_catalog_permissions_through_the_gate(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();

        $user->assignRole('editor');

        $this->assertTrue($user->can('access admin'));
        $this->assertTrue($user->can('manage categories'));
        $this->assertTrue($user->can('manage brands'));
        $this->assertTrue($user->can('manage products'));
        $this->assertFalse($user->can('manage stores'));
        $this->assertFalse($user->can('manage offers'));
    }

    public function test_repeated_seeding_preserves_roles_permissions_and_existing_user_assignments(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $editor = User::factory()->create();
        $admin->assignRole('admin');
        $editor->assignRole('editor');
        $roleIds = Role::query()->orderBy('name')->pluck('id', 'name')->all();
        $permissionIds = Permission::query()->orderBy('name')->pluck('id', 'name')->all();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame($roleIds, Role::query()->orderBy('name')->pluck('id', 'name')->all());
        $this->assertSame($permissionIds, Permission::query()->orderBy('name')->pluck('id', 'name')->all());
        $this->assertDatabaseCount('model_has_roles', 2);
        $this->assertDatabaseCount('role_has_permissions', 10);
        $this->assertTrue($admin->refresh()->hasRole('admin'));
        $this->assertTrue($editor->refresh()->hasRole('editor'));
        $this->assertTrue($admin->can('manage offers'));
        $this->assertFalse($editor->can('manage offers'));
    }

    public function test_reseeding_restores_the_editor_permission_boundary(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('editor');
        Role::findByName('editor', 'web')->givePermissionTo('manage stores');
        $this->assertTrue($user->can('manage stores'));

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertFalse($user->refresh()->can('manage stores'));
    }
}
