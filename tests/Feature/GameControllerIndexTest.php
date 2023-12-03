<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Support\Facades\Date;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GameControllerIndexTest extends TestCase
{
    /**
     * Test index without specifying any app / package ID.
     * Should return empty data.
     */
    public function testIndexWithoutAppId(): void
    {
        $this->url = route('games.index');

        $this->assertJsonGet(fn (AssertableJson $json) => $json
            ->has('data', 0)
            ->has('links')
            ->has('meta')
        );
    }

    public function testIndexWithAppId(): void
    {
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

        $this->url = route('games.index', [
            'app_id' => '1440191,70600',
            'package_id' => '27831',
        ]);
        $this->assertJsonGet(fn (AssertableJson $json) => $json
            ->has('data', 3)
            ->has('data.0', fn (AssertableJson $json) => $json
                ->has('id')
                ->where('name', 'Game 1')
                ->where('app_id', 1440191)
                ->where('package_id', null)
                ->has('steamgifts', fn (AssertableJson $json) => $json
                    ->has('id')
                    ->where('game_id', 1)
                    ->where('cv_reduced_at', Date::createFromTimestamp(1622505600)->toJSON())
                    ->where('cv_removed_at', null)
                    ->has('created_at')
                    ->has('updated_at')
                )
                ->has('created_at')
                ->has('updated_at')
            )
            ->has('data.1', fn (AssertableJson $json) => $json
                ->where('name', 'Game 2')
                ->where('app_id', 70600)
                ->where('package_id', null)
                ->etc()
            )
            ->has('data.2', fn (AssertableJson $json) => $json
                ->where('name', 'Game 4')
                ->where('app_id', null)
                ->where('package_id', 27831)
                ->etc()
            )
            ->has('links')
            ->has('meta')
            ->etc()
        );
    }
}
