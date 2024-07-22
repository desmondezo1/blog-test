<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Create admin User
        User::create([
            'name' => 'Admin',
            'email' => 'desmond@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Seed in other users
        User::factory()->count(50)->create();
    }
}