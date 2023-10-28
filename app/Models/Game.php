<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Game extends Model
{
    use HasFactory;

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 1000;

    /**
     * Request to steamgifts bundle-games for given page.
     *
     * @return false|array<string,mixed>
     */
    public static function syncBundleGames(int $page): array|false
    {
        $url = config('services.steamgifts.url.sync-bundle') . $page;

        try {
            $response = Http::get($url)->json();
            foreach ($response['results'] as $item) {
                $game = Game::when($item['app_id'], fn ($query, $appId) => $query->where('app_id', $appId))
                    ->when($item['package_id'], fn ($query, $packageId) => $query->where('package_id', $packageId))
                    ->first();

                if (is_null($game)) {
                    $game = new Game();
                    $game->app_id = $item['app_id'];
                    $game->package_id = $item['package_id'];
                }

                $game->name = $item['name'];
                $game->cv_reduced_at = $item['reduced_value_timestamp'];
                $game->cv_removed_at = $item['no_value_timestamp'];
                $game->last_checked_sg_at = now();
                $game->save();
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('[syncBundleGames] page ' . $page);
            Log::error($e->getMessage());

            return false;
        }
    }
}
