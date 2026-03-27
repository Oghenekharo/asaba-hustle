<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['phone' => '08165144936'],
            [
                'name' => 'Tunde Adebayo',
                'email' => 'admin@email.com',
                'password' => Hash::make('password')
            ]
        );

        $admin->assignRole('admin');
    }
}
