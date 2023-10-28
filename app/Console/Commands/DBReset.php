<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DummySeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DBReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset {--D|dummy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop database, rerun migration, seeds data.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $resetStart = microtime(true);

        $this->createDatabaseIfNotExists();

        $this->migrateFresh();

        DB::transaction(function () {
            $this->seedRequired();

            if ($this->option('dummy')) {
                $this->seedDummy();
            }
        });

        $this->deleteDir();

        $resetTime = $this->getElapsedTime($resetStart);
        $this->line("<info>Total time:</info> ({$resetTime}ms)");
    }

    /**
     * Create database if not exists.
     */
    private function createDatabaseIfNotExists(): void
    {
        /** @var string */
        $username = config('database.connections.mysql.username');
        /** @var string */
        $password = config('database.connections.mysql.password');
        /** @var string */
        $dbName = config('database.connections.mysql.database');
        $this->line("<comment>Create database if not exists:</comment> {$dbName}");

        try {
            // Test database connection
            DB::connection()->getPdo();
            $this->line('<info>Database OK!</info>');
        } catch (\Exception $e) {
            $timeStart = microtime(true);
            $pdo = new \PDO('mysql:host=localhost', $username, $password);
            $result = $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbName}");
            $processTime = $this->getElapsedTime($timeStart);
            if ($result == true) {
                $this->line("<info>Database created:</info>  ({$processTime}ms)");
            }
        }
        $this->line('');
    }

    /**
     * Call migrate fresh and output elapsed time.
     */
    private function migrateFresh(): void
    {
        $this->line('<comment>Running migrate fresh</comment>');
        $migrationStart = microtime(true);
        Artisan::call('migrate:fresh');
        $migrationTime = $this->getElapsedTime($migrationStart);
        $this->line("<info>Database migrated:</info> {$migrationTime}ms");
        $this->line('');
    }

    /**
     * Call db seed required data seeder.
     */
    private function seedRequired(): void
    {
        $seedRequiredStart = microtime(true);
        $this->line('<info>Seeding Required Data</info>');
        $this->callSeeders(DatabaseSeeder::$seeder);
        $seedRequiredTime = $this->getElapsedTime($seedRequiredStart);
        $this->line("<info>Required data seeded:</info> {$seedRequiredTime}ms");
        $this->line('');
    }

    /**
     * Call db seed dummy data seeder.
     */
    private function seedDummy(): void
    {
        $seedDummyStart = microtime(true);
        $this->line('<info>Seeding Dummy Data</info>');
        $this->callSeeders(DummySeeder::$seeder);
        $seedDummyTime = $this->getElapsedTime($seedDummyStart);
        $this->line("<info>Dummy data seeded:</info> {$seedDummyTime}ms");
        $this->line('');
    }

    /**
     * Delete directory in the storage.
     */
    private function deleteDir(): void
    {
        $deleteDirStart = microtime(true);
        $this->line('<info>Deleting Storage Directories</info>');
        $this->deleteDirectories([
            'users',
            'path', // generated from testing
        ]);

        $deleteDirTime = $this->getElapsedTime($deleteDirStart);
        $this->line("<info>Storage directory cleared:</info> {$deleteDirTime}ms");
        $this->line('');
    }

    /**
     * Run DB seed command and output progress.
     *
     * @param array<int,string> $seeders
     */
    private function callSeeders(array $seeders): void
    {
        $countSeeders = count($seeders);

        foreach ($seeders as $key => $value) {
            $padCount = strlen((string) $countSeeders);
            $index = str_pad(($key + 1) . '', $padCount, '0', STR_PAD_LEFT);
            $this->line("($index/$countSeeders) <comment>Seeding:</comment> $value");
            $startTime = microtime(true);
            Artisan::call('db:seed', ['--class' => $value]);
            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);
            $spaces = str_pad('', $padCount * 2 + 3, ' ', STR_PAD_LEFT);
            $this->line("$spaces <info>Seeded:</info>  {$runTime}ms");
        }
    }

    /**
     * Get elapsed time from given start time.
     */
    private function getElapsedTime(float $startTime): string
    {
        return number_format((microtime(true) - $startTime) * 1000, 2);
    }

    /**
     * Run command to delete storage directory.
     *
     * @param array<int,string> $directories
     */
    private function deleteDirectories(array $directories): void
    {
        $countSeeders = count($directories);
        foreach ($directories as $key => $dir) {
            $padCount = strlen((string) $countSeeders);
            $index = str_pad(($key + 1) . '', $padCount, '0', STR_PAD_LEFT);
            $this->line("($index/$countSeeders) <comment>Deleting:</comment> $dir");
            $startTime = microtime(true);
            File::deleteDirectory("storage/app/public/$dir");
            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);
            $spaces = str_pad('', $padCount * 2 + 3, ' ', STR_PAD_LEFT);
            $this->line("$spaces <info>Deleted:</info>  {$runTime}ms");
        }
    }
}
