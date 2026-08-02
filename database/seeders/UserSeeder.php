<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'مدير المشتل',
            'username' => 'admin',
            'password' => 'password',
            'role'     => UserRole::Admin,
        ]);

        User::create([
            'name'     => 'المهندس الزراعي الرئيسي',
            'username' => 'engineer',
            'password' => 'password',
            'role'     => UserRole::Engineer,
        ]);

        User::factory()->count(5)->create(['role' => UserRole::Farmer]);
    }
}
