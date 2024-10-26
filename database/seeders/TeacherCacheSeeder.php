<?php

namespace Database\Seeders;

use App\Models\ClubDeportivo\TeacherCache;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherCacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TeacherCache::factory()->count(60)->create();
    }
}
