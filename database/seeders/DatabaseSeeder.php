<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Portrait', 'Événement', 'Voyage', 'Vidéo'] as $i => $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $i + 1]
            );
        }

        SiteSetting::current();
    }
}