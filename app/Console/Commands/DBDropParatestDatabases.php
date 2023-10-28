<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DBDropParatestDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:drop-paratest {--p=16}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all databases created for paratest.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $numProcesses = (int) $this->option('p');

        $dbName = config('database.connections.mysql.database');
        for ($i = 1; $i <= $numProcesses; ++$i) {
            $this->line('Deleting: ' . $dbName . '_test_' . $i);
            DB::statement('DROP DATABASE IF EXISTS ' . $dbName . '_test_' . $i);
        }
    }
}
