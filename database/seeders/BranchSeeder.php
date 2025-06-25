<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
    DB::table('branches')->insert([
        [
            'name' => 'Cabang Jakarta Timur',
            'internet_provider_id' => 1,
            'internet_customer_id' => 'PGI-00123',
            'cctv_type' => 1,
            'ip_cam_account_id' => 1,
            'phone_number' => '08123456789',
            'address' => 'Jl. Raya No. 123',
            'latitude' => -6.20123456,
            'longitude' => 106.81654321,
            'area_manager' => 'Budi Santoso',
            'branch_head' => 'Sari Dewi',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Jakarta Pusat',
            'internet_provider_id' => 2,
            'internet_customer_id' => 'PGI-002',
            'cctv_type' => 2,
            'ip_cam_account_id' => 2,
            'phone_number' => '081211115023',
            'address' => 'Jl. Menteng No.1',
            'latitude' => -6.20123456,
            'longitude' => 106.81654321,
            'area_manager' => 'Yanto',
            'branch_head' => 'Santi',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
    }

}
