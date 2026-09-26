<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's production-safe baseline data.
     */
    public function run(): void
    {
        foreach (['ADMIN_USER_PHONE', 'ADMIN_USER_EMAIL', 'ADMIN_USER_NAME', 'ADMIN_USER_PASSWORD'] as $key) {
            if (blank(env($key))) {
                throw new RuntimeException("Set {$key} before running the production seeder.");
            }
        }

        $this->call([
            RoleSeeder::class,
            SkillSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
