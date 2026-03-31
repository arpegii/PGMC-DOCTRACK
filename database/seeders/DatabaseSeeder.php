<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UnitsTableSeeder::class,  // This will create all units including Admin Office
            DocumentTypeSeeder::class, // This will create all document types
        ]);
    }
}