<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class GameSyncSteamgifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:game:sync:steamgifts {--page=1 : The page number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh CV values for all games to Steamgifts.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->comment('Syncing Bundle Games');

        $page = (int) $this->option('page');

        while (true) {
            $paddedPage = str_pad(strval($page), 2, ' ', STR_PAD_LEFT);
            $this->output->write("<comment>Page:</comment> {$paddedPage}");

            [$duration, $response] = $this->measureElapsedTime(function () use ($page) {
                return Game::syncBundleGames($page);
            });

            $paddedDuration = str_pad(strval($duration), 5, ' ', STR_PAD_LEFT);
            $this->output->write(" ..... <info>OK!</info> in {$paddedDuration} seconds");
            $this->newLine();

            ++$page;

            if (count($response['results']) !== $response['per_page']) {
                break;
            }
        }

        $this->info('Sync Completed!');
    }

    /**
     * Do something and measure elapsed time.
     *
     * @return array<int, mixed> [duration, result]
     */
    private function measureElapsedTime(\Closure $callback): array
    {
        $start = microtime(true);
        $result = $callback();

        $duration = $this->getElapsedTime($start);

        return [$duration, $result];
    }

    /**
     * Get elapsed time from given start time.
     */
    private function getElapsedTime(float $startTime): string
    {
        return number_format(microtime(true) - $startTime, 2);
    }
}
