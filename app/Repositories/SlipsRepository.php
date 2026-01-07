<?php

namespace App\Repositories;

use App\Models\Bet;
use App\Models\Slip;

class SlipsRepository
{
    public function createSlip(array $inputData) :Slip
    {
        $slip = Slip::query()
            ->create([
                'odds' => $inputData['odds'],
                'stake' => $inputData['stake']
            ]);
        foreach ($inputData['events'] as $event) {
            Bet::create([
               'event_date' => $event['event_date'],
                'home_id' => $event['home_id'],
                'away_id' => $event['away_id'],
                'event_type_id' => $event['event_type_id'],
                'selection_id' => $event['selection_id'],
                'discipline_id' => $event['discipline_id'],
                'slip_id' => $slip->getAttribute('id')
            ]);
        }
        return $slip;
    }

    public function index()
    {
        return Slip::query()
            ->with(['bets.home', 'bets.away', 'bets.eventType', 'bets.selection', 'bets.discipline'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function changeSlipStatus(Slip $slip, string $newStatus): bool
    {
        return $slip->update(['status' => $newStatus]);
    }

    public function updateSlipStake(Slip $slip, float $input): bool
    {
        return $slip->update(['stake' => $input]);
    }

    public function deleteSlip(Slip $slip): bool
    {
        return $slip->delete();
    }
}
