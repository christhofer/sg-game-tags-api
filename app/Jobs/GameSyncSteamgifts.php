<?php

namespace App\Jobs;

use App\Models\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GameSyncSteamgifts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $page = 1;
        while (true) {
            $response = Game::syncBundleGames($page);
            ++$page;

            if ($page >= 100) {
                break;
            }
            if ($response === false) {
                continue; // ignore error, continue to next page, let tommorow's job handle it
            }
            if (count($response['results']) !== $response['per_page']) {
                break;
            }
        }
    }
}
