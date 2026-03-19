<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@lednicka.test',
            'password' => bcrypt('password'),
            'enabled' => true,
        ]);
        $admin->assignRole('admin');

        $employee = User::create([
            'name' => 'Jan Novák',
            'email' => 'jan@lednicka.test',
            'password' => bcrypt('password'),
            'enabled' => true,
        ]);
        $employee->assignRole('employee');
    }
}

