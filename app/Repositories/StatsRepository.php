<?php

namespace App\Repositories;

use App\Models\Bet;

class StatsRepository
{
    public function all(): array
    {
        $accuracy = Bet::select(\DB::raw('COUNT(id) as wonbets'), \DB::raw('(SELECT COUNT(id) FROM bets) AS totalbetscount'))->where('status', 'won')->first();
        $profit = Bet::select(\DB::raw('ROUND(SUM((stake * odds) - stake), 2) as profit'))->where('status', 'won')->first();
        return [
            'profit' => $profit?->getAttribute('profit') ?? 0,
            'accuracy' => $accuracy ? (($accuracy->getAttribute('wonbets') / $accuracy->getAttribute('totalbetscount')) * 100) : 0,
        ];
    }
}
