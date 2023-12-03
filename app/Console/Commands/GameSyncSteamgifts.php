<?php

namespace App\Console\Commands;

use App\Models\GamedataSteamgifts;
use Illuminate\Console\Command;

class GameSyncSteamgifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:game:sync:steamgifts {--from=1 : The page number to start} {--to=50 : The page number to end}';

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

        $page = (int) $this->option('from');
        $to = (int) $this->option('to');

        $errorPages = [];

        while ($page <= $to) {
            $paddedPage = str_pad(strval($page), 2, ' ', STR_PAD_LEFT);
            $this->output->write("<comment>Page:</comment> {$paddedPage}");

            [$duration, $response] = $this->measureElapsedTime(function () use ($page) {
                return GamedataSteamgifts::syncBundleGames($page);
            });

            $paddedDuration = str_pad(strval($duration), 5, ' ', STR_PAD_LEFT);

            if ($response === false) {
                // request failed
                $this->output->write(" ..... <error>ERROR</error> after {$paddedDuration} seconds!");
                $this->newLine();
                $errorPages[] = $page;
            } else {
                // request success
                $this->output->write(" ..... <info>Done</info> in {$paddedDuration} seconds!");
                $this->newLine();
                // exit condition
                if (count($response['results']) !== $response['per_page']) {
                    break;
                }
            }

            ++$page;
        }

        $this->info('Sync Completed!');

        if (count($errorPages) > 0) {
            $this->error('Error Pages: ' . implode(', ', $errorPages));
        }
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
