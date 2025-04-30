<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Mendapatkan Role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        // Membuat User Admin jika belum ada
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password')
            ]
        );

        // Menambahkan Role ke User jika belum ada
        if (!$adminUser->hasRole($adminRole->name)) {
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        // Membuat User biasa jika belum ada
        $normalUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Normal User',
                'password' => bcrypt('password')
            ]
        );

        // Menambahkan Role ke User jika belum ada
        if (!$normalUser->hasRole($userRole->name)) {
            $normalUser->roles()->syncWithoutDetaching([$userRole->id]);
        }
    }
}
