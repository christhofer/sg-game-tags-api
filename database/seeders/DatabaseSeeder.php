<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * List of seeders for required data, used in console/reset.php.
     */
    public static $seeder = [
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(self::$seeder);
    }
}
