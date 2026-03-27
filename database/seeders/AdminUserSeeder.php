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
        $phone = (string) env('ADMIN_USER_PHONE', '08165144936');
        $email = (string) env('ADMIN_USER_EMAIL', 'admin@email.com');
        $name = (string) env('ADMIN_USER_NAME', 'Tunde Adebayo');
        $password = (string) env('ADMIN_USER_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password)
            ]
        );

        $admin->assignRole('admin');
    }
}
