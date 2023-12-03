<?php

namespace Tests\Feature;

use App\Jobs\GameSyncSteamgifts;
use App\Models\Game;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncBundleGameTest extends TestCase
{
    /**
     * Test function syncBundleGames on Game model, should send GET request
     * to Steamgifts, and update table games.
     */
    public function testSyncBundleGame(): void
    {
        $lastCheck = today()->subDay();
        // generate existing data games
        $this->travelTo($lastCheck, function () {
            Game::factory()
                ->hasSteamgifts([
                    'cv_reduced_at' => Date::createFromTimestamp(1622505600),
                    'cv_removed_at' => null,
                ])
                ->create([
                    'name' => 'Game 1',
                    'app_id' => 1440191,
                    'package_id' => null,
                ]);
            Game::factory()
                ->hasSteamgifts([
                    'cv_reduced_at' => Date::createFromTimestamp(1383192000),
                    'cv_removed_at' => null,
                ])
                ->create([
                    'name' => 'Game 2',
                    'app_id' => 70600,
                    'package_id' => null,
                ]);
            Game::factory()
                ->hasSteamgifts([
                    'cv_reduced_at' => Date::createFromTimestamp(1543881600),
                    'cv_removed_at' => null,
                ])
                ->create([
                    'name' => 'Game 3',
                    'app_id' => 70617,
                    'package_id' => null,
                ]);
            Game::factory()
                ->hasSteamgifts([
                    'cv_reduced_at' => Date::createFromTimestamp(1622505600),
                    'cv_removed_at' => null,
                ])
                ->create([
                    'name' => 'Game 4',
                    'app_id' => null,
                    'package_id' => 27831,
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
                    // game 4 removed from bundle
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

        $this->freezeSecond(function () {
            GameSyncSteamgifts::dispatch();
            $games = Game::all();

            // table games should not change
            $this->assertDatabaseHas('games', [
                'id' => $games[0]->id,
                'name' => 'Game 1',
                'app_id' => 1440191,
                'package_id' => null,
            ]);
            $this->assertDatabaseHas('games', [
                'id' => $games[1]->id,
                'name' => 'Game 2',
                'app_id' => 70600,
                'package_id' => null,
            ]);
            $this->assertDatabaseHas('games', [
                'id' => $games[2]->id,
                'name' => 'Game 3',
                'app_id' => 70617,
                'package_id' => null,
            ]);
            $this->assertDatabaseHas('games', [
                'id' => $games[3]->id,
                'name' => 'Game 4',
                'app_id' => null,
                'package_id' => 27831,
            ]);
            $this->assertDatabaseHas('games', [
                'id' => $games[4]->id,
                'name' => 'Game 5',
                'app_id' => 96540,
                'package_id' => null,
            ]);

            // table gamedata_steamgifts should change
            $this->assertDatabaseHas('gamedata_steamgifts', [
                'game_id' => $games[0]->id,
                'cv_reduced_at' => Date::createFromTimestamp(1622505600)->toDateTimeString(),
                'cv_removed_at' => null,
            ]);
            // added cv_removed_at
            $this->assertDatabaseHas('gamedata_steamgifts', [
                'game_id' => $games[1]->id,
                'cv_reduced_at' => Date::createFromTimestamp(1383192000)->toDateTimeString(),
                'cv_removed_at' => Date::createFromTimestamp(1512086400)->toDateTimeString(),
            ]);
            // removed cv_reduced_at, added cv_removed_at
            $this->assertDatabaseHas('gamedata_steamgifts', [
                'game_id' => $games[2]->id,
                'cv_reduced_at' => null,
                'cv_removed_at' => Date::createFromTimestamp(1543881600)->toDateTimeString(),
            ]);
            // removed because don't exist in api response
            $this->assertDatabaseMissing('gamedata_steamgifts', [
                'game_id' => $games[3]->id,
            ]);
            // newly added game
            $this->assertDatabaseHas('gamedata_steamgifts', [
                'game_id' => $games[4]->id,
                'cv_reduced_at' => Date::createFromTimestamp(1660435200)->toDateTimeString(),
                'cv_removed_at' => null,
            ]);
        });

        Http::assertSentCount(2);
    }
}
