<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (Role::exists()) {
            return;
        }

        $permissions = collect([
            ['name' => 'Manage Users', 'key' => 'manage_users'],
            ['name' => 'Manage Providers', 'key' => 'manage_providers'],
            ['name' => 'Manage Content', 'key' => 'manage_content'],
            ['name' => 'Manage Payments', 'key' => 'manage_payments'],
            ['name' => 'Manage Support', 'key' => 'manage_support'],
        ])->map(fn (array $data) => Permission::create($data));

        $superAdmin = Role::create(['name' => 'Super Admin']);
        foreach ($permissions as $permission) {
            RolePermission::create(['role_id' => $superAdmin->id, 'permission_id' => $permission->id]);
        }

        $supportAgent = Role::create(['name' => 'Support Agent']);
        foreach ($permissions->whereIn('key', ['manage_support', 'manage_users']) as $permission) {
            RolePermission::create(['role_id' => $supportAgent->id, 'permission_id' => $permission->id]);
        }

        $admin = User::where('user_type', 'admin')->first();
        if ($admin) {
            UserRole::create(['user_id' => $admin->id, 'role_id' => $superAdmin->id]);
        }
    }
}
