<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Get the steamgifts data for the game.
     */
    public function steamgifts(): HasOne
    {
        return $this->hasOne(GamedataSteamgifts::class);
    }
}
