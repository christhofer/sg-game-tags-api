<?php

namespace App\Jobs;

use App\Models\GamedataSteamgifts;
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
        // insert new games, update existing
        $page = 1;
        while (true) {
            $response = GamedataSteamgifts::syncBundleGames($page);
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

        // remove games that are not in steamgifts bundle anymore
        // the games will have updated_at more than 1 hour ago
        $notBundledAnymore = GamedataSteamgifts::where('updated_at', '<', now()->subHour())
            ->with('game')
            ->get();
        if ($notBundledAnymore->count()) {
            info(
                'GameSyncSteamgifts: ' .
                $notBundledAnymore->count() .
                ' games are not bundled anymore, deleting:' .
                $notBundledAnymore->implode(
                    fn ($sg) => $sg->game->name . ' (' . ($sg->game->app_id ?? $sg->game->package_id) . ')',
                    ', '
                )
            );

            GamedataSteamgifts::where('updated_at', '<', now()->subHour())->delete();
        }
    }
}
