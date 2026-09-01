<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_receives_permissions_through_an_assigned_role(): void
    {
        $permission = Permission::create(['name' => 'access admin']);
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->can('access admin'));
    }
}
