<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Employer;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'email' => 'admin@admin.com',
            'user_type' => 2,
            'is_active' => true
        ]);

        User::factory()->create([
            'email' => 'employer@employer.com',
            'user_type' => 1,
            'is_active' => true
        ]);

        Admin::factory()->create([
            'name' => 'Admin User',
            'user_id' => 1
        ]);

        Employer::factory()->create([
            'name' => 'Employer User',
            'user_id' => 2
        ]);
    }
}
