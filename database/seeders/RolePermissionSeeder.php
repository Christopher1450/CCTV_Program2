<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'view_roles',
            'manage_branches',
            'process_work_orders',
            'view_cctvs',
            'update_cctvs',
            'deactivate_cctvs',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['permission_name' => $perm]);
        }

        $roles = [
            'Super Admin',
            'Admin',
            'Staff IT',
            'Staff CCTV',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }

        $superAdmin = Role::where('role_name', 'Super Admin')->first();
        $admin = Role::where('role_name', 'Admin')->first();
        $staffIT = Role::where('role_name', 'Staff IT')->first();
        $staffCCTV = Role::where('role_name', 'Staff CCTV')->first();

        $superAdmin->permissions()->sync(Permission::pluck('id'));

        $admin->permissions()->sync(Permission::whereIn('permission_name', [
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'view_roles',
            'manage_branches',
        ])->pluck('id'));

        $staffIT->permissions()->sync(Permission::where('permission_name', 'process_work_orders')->pluck('id'));

        $staffCCTV->permissions()->sync(Permission::whereIn('permission_name', [
            'view_cctvs',
            'update_cctvs',
            'deactivate_cctvs',
        ])->pluck('id'));

        User::firstOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role_id' => $superAdmin->id,
            'status' => 1,
        ]);
    }
}
