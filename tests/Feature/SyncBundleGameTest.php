<?php

namespace Tests\Feature;

use App\Jobs\GameSyncSteamgifts;
use App\Models\Game;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncBundleGameTest extends TestCase
{
    /**
     * Test function syncBundleGames on Game model, should send GEt request
     * to Steamgifts, and update table games.
     */
    public function testSyncBundleGame(): void
    {
        $lastCheck = today()->subDay();
        // generate existing data games
        $this->travelTo($lastCheck, function () {
            Game::factory()->create([
                'name' => 'Game 1',
                'app_id' => 1440191,
                'package_id' => null,
                'cv_reduced_at' => 1622505600,
                'cv_removed_at' => null,
            ]);
            Game::factory()->create([
                'name' => 'Game 2',
                'app_id' => 70600,
                'package_id' => null,
                'cv_reduced_at' => 1383192000,
                'cv_removed_at' => null,
            ]);
            Game::factory()->create([
                'name' => 'Game 3',
                'app_id' => 70617,
                'package_id' => null,
                'cv_reduced_at' => 1543881600,
                'cv_removed_at' => null,
            ]);
            Game::factory()->create([
                'name' => 'Game 4',
                'app_id' => null,
                'package_id' => 27831,
                'cv_reduced_at' => 1622505600,
                'cv_removed_at' => null,
            ]);
        });

        $url = config('services.steamgifts.url.sync-bundle');

        // https://laravel.com/docs/10.x/http-client#preventing-stray-requests
        Http::preventStrayRequests();

        Http::fake([
            $url . 1 => Http::response([
                'page' => 1,
                'per_page' => 3,
                'results' => [
                    [
                        'name' => 'Game 1',
                        'app_id' => 1440191,
                        'package_id' => null,
                        'reduced_value_timestamp' => 1622505600,
                        'no_value_timestamp' => null,
                    ],
                    [
                        'name' => 'Game 2',
                        'app_id' => 70600,
                        'package_id' => null,
                        'reduced_value_timestamp' => 1383192000,
                        'no_value_timestamp' => 1512086400,
                    ],
                    [
                        'name' => 'Game 3',
                        'app_id' => 70617,
                        'package_id' => null,
                        'reduced_value_timestamp' => null,
                        'no_value_timestamp' => 1543881600,
                    ],
                ],
            ]),
            $url . 2 => Http::response([
                'page' => 2,
                'per_page' => 3,
                'results' => [
                    [
                        'name' => 'Game 5',
                        'app_id' => 96540,
                        'package_id' => null,
                        'reduced_value_timestamp' => 1660435200,
                        'no_value_timestamp' => null,
                    ],
                ],
            ]),
        ]);

        $this->freezeSecond(function () use ($lastCheck) {
            GameSyncSteamgifts::dispatch();

            // unchanged, only update last_checked_sg_at
            $this->assertDatabaseHas('games', [
                'name' => 'Game 1',
                'app_id' => 1440191,
                'package_id' => null,
                'cv_reduced_at' => 1622505600,
                'cv_removed_at' => null,
                'last_checked_sg_at' => now()->format('Y-m-d H:i:s'),
            ]);
            // added cv_removed_at
            $this->assertDatabaseHas('games', [
                'name' => 'Game 2',
                'app_id' => 70600,
                'package_id' => null,
                'cv_reduced_at' => 1383192000,
                'cv_removed_at' => 1512086400,
                'last_checked_sg_at' => now()->format('Y-m-d H:i:s'),
            ]);
            // removed cv_reduced_at, added cv_removed_at
            $this->assertDatabaseHas('games', [
                'name' => 'Game 3',
                'app_id' => 70617,
                'package_id' => null,
                'cv_reduced_at' => null,
                'cv_removed_at' => 1543881600,
                'last_checked_sg_at' => now()->format('Y-m-d H:i:s'),
            ]);
            // unchanged because don't exist in api response
            $this->assertDatabaseHas('games', [
                'name' => 'Game 4',
                'app_id' => null,
                'package_id' => 27831,
                'cv_reduced_at' => 1622505600,
                'cv_removed_at' => null,
                'last_checked_sg_at' => $lastCheck->format('Y-m-d H:i:s'),
            ]);
            // newly added game
            $this->assertDatabaseHas('games', [
                'name' => 'Game 5',
                'app_id' => 96540,
                'package_id' => null,
                'cv_reduced_at' => 1660435200,
                'cv_removed_at' => null,
                'last_checked_sg_at' => now()->format('Y-m-d H:i:s'),
            ]);
        });

        Http::assertSentCount(2);
    }
}
