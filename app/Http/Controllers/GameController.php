<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameCollection;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): GameCollection
    {
        $result = Game::whereIn('app_id', explode(',', $request->get('app_id') ?? '0'))
            ->orWhereIn('package_id', explode(',', $request->get('package_id') ?? '0'))
            ->with('steamgifts')
            ->paginate();

        return new GameCollection($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //
    // }

    /**
     * Display the specified resource.
     */
    // public function show(Game $game)
    // {
    //
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Game $game)
    // {
    //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Game $game)
    // {
    //
    // }
}
