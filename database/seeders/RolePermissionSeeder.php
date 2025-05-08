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
        // Final Permission list sesuai ERD
        $permissions = [
            // user & role
            'view_users', 'create_users', 'update_users', 'delete_users',
            'view_roles',

            // master data
            'manage_branches', 'manage_providers', 'manage_ipcam', 'manage_cctv_positions',

            // cctv
            'view_cctvs', 'update_cctvs', 'deactivate_cctvs',

            // work orders
            'create_work_orders', 'process_work_orders', 'close_work_orders'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['permission_name' => $perm]);
        }

        // Roles
        $roles = ['Super Admin', 'Admin', 'Staff IT', 'Staff CCTV'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role]);
        }

        // Assign Permissions to Roles
        $superAdmin = Role::where('role_name', 'Super Admin')->first();
        $admin      = Role::where('role_name', 'Admin')->first();
        $staffIT    = Role::where('role_name', 'Staff IT')->first();
        $staffCCTV  = Role::where('role_name', 'Staff CCTV')->first();

        $superAdmin->permissions()->sync(Permission::pluck('id')->toArray());

        $admin->permissions()->sync(Permission::whereIn('permission_name', [
            'view_users', 'create_users', 'update_users', 'delete_users',
            'view_roles',
            'manage_branches', 'manage_providers', 'manage_ipcam', 'manage_cctv_positions'
        ])->pluck('id'));

        $staffIT->permissions()->sync(Permission::whereIn('permission_name', [
            'process_work_orders', 'close_work_orders'
        ])->pluck('id'));

        $staffCCTV->permissions()->sync(Permission::whereIn('permission_name', [
            'create_work_orders',
            'view_cctvs', 'update_cctvs', 'deactivate_cctvs'
        ])->pluck('id'));

        // Default Users
        User::firstOrCreate(['username' => 'superadmin'], [
            'name'     => 'Super Admin',
            'password' => Hash::make('password'),
            'role_id'  => $superAdmin->id,
            'status'   => 1,
        ]);

        User::firstOrCreate(['username' => 'admin'], [
            'name'     => 'Admin',
            'password' => Hash::make('admin'),
            'role_id'  => $admin->id,
            'status'   => 1,
        ]);

        User::firstOrCreate(['username' => 'staffit'], [
            'name'     => 'Staff IT',
            'password' => Hash::make('it'),
            'role_id'  => $staffIT->id,
            'status'   => 1,
        ]);

        User::firstOrCreate(['username' => 'staffcctv'], [
            'name'     => 'Staff CCTV',
            'password' => Hash::make('cctv'),
            'role_id'  => $staffCCTV->id,
            'status'   => 1,
        ]);
    }
}
