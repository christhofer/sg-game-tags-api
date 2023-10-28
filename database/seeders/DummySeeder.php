<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummySeeder extends Seeder
{
    /**
     * List of seeders for dummy data, used in console/reset.php.
     */
    public static $seeder = [
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(self::$seeder);
    }
}
