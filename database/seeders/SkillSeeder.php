<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [

            [
                'name' => 'Cleaning',
                'description' => 'House and office cleaning services'
            ],

            [
                'name' => 'Plumbing',
                'description' => 'Pipe repair and installation'
            ],

            [
                'name' => 'Electrical',
                'description' => 'Electrical wiring and repairs'
            ],

            [
                'name' => 'Cooking',
                'description' => 'Private cooking and catering'
            ],

            [
                'name' => 'Gardening',
                'description' => 'Garden maintenance services'
            ],

            [
                'name' => 'Moving Help',
                'description' => 'Help with moving and lifting'
            ],

            [
                'name' => 'Carpentry',
                'description' => 'Furniture repair and carpentry'
            ],

            [
                'name' => 'Painting',
                'description' => 'Interior and exterior painting'
            ]

        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate($skill);
        }
    }
}
