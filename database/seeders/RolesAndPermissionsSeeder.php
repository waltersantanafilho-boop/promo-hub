<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(PermissionRegistrar $permissionRegistrar): void
    {
        $permissionRegistrar->forgetCachedPermissions();

        DB::transaction(function () use ($permissionRegistrar): void {
            $permissions = [
                'access admin',
                'manage categories',
                'manage brands',
                'manage stores',
                'manage products',
                'manage offers',
            ];

            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            $permissionRegistrar->forgetCachedPermissions();

            Role::findOrCreate('admin', 'web')->syncPermissions($permissions);
            Role::findOrCreate('editor', 'web')->syncPermissions([
                'access admin',
                'manage categories',
                'manage brands',
                'manage products',
            ]);
        });

        $permissionRegistrar->forgetCachedPermissions();
    }
}
