<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'super_admin'],
            [
                'name' => 'المهندس الزراعي الرئيسي',
                'password' => 'password',
                'role' => UserRole::Engineer,
            ]
        );
    }
}
