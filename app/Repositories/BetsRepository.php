<?php

namespace App\Repositories;

use App\Models\Bet;

class BetsRepository
{
    public function createBet(array $inputData) :Bet
    {
        return Bet::create($inputData);
    }

    public function index()
    {
        return Bet::query()
            ->select([
                'bets.id',
                'bets.status',
                \DB::raw("CONCAT(home.name, ' - ', away.name) AS event"),
                'event_types.name AS event_type',
                'selections.name AS selection',
                'odds',
                'stake',
            ])
            ->join('competitors AS away', 'away.id', '=', 'bets.away_id')
            ->join('competitors AS home', 'home.id', '=', 'bets.home_id')
            ->join('event_types', 'event_types.id', '=', 'bets.event_type_id')
            ->join('selections', 'selections.id', '=', 'bets.selection_id')
            ->get();
    }

    public function changeBetStatus(Bet $bet, string $newStatus): bool
    {
        return $bet->update(['status' => $newStatus]);
    }
}
