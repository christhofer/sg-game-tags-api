<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GamedataSteamgifts extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamedata_steamgifts';

    /**
     * The attributes that should be cast to native types.
     * https://laravel.com/docs/master/eloquent-mutators#attribute-casting.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'cv_reduced_at' => 'datetime',
        'cv_removed_at' => 'datetime',
    ];

    /**
     * Get the game that owns the steamgifts data.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

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
                $game->save();

                $dataSteamgifts = $game->steamgifts ?? new GamedataSteamgifts();
                $dataSteamgifts->game_id = $game->id;
                $dataSteamgifts->cv_reduced_at = isset($item['reduced_value_timestamp'])
                    ? Date::createFromTimestamp($item['reduced_value_timestamp'])
                    : null;
                $dataSteamgifts->cv_removed_at = isset($item['no_value_timestamp'])
                    ? Date::createFromTimestamp($item['no_value_timestamp'])
                    : null;
                $dataSteamgifts->updated_at = now();
                $dataSteamgifts->save();
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('[syncBundleGames] page ' . $page);
            Log::error($e->getMessage());

            return false;
        }
    }
}
