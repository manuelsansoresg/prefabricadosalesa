<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
        Role::query()->firstOrCreate(['name' => 'editor']);

        $admin = User::query()->firstOrCreate(
            ['email' => 'manuelsansoresg@gmail.com'],
            [
                'name' => 'Manuel Sansores',
                'password' => 'demor00txx',
            ],
        );

        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
    }
}
