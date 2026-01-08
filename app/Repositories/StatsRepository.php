<?php

namespace App\Repositories;

use App\Models\Slip;

class StatsRepository
{
    public function all(): array
    {
        try {
            $wonPlayedCount = Slip::where('status', 'won')->where('played', true)->count();
            $totalPlayedCount = Slip::where('status', '!=', 'pending')->where('played', true)->count();

            $accuracy = $totalPlayedCount > 0 ? \Number::percentage( ($wonPlayedCount / $totalPlayedCount) * 100) : 0;
            $totalWonCount = Slip::where('status', 'won')->count();
            $totalSlipCount = Slip::where('status', '!=', 'pending')->count();

            $totalAccuracy = $totalWonCount > 0 ? \Number::percentage(($totalWonCount / $totalSlipCount) * 100) : 0;

            $profitData = Slip::query()
                ->where('status', 'won')
                ->where('played', true)
                ->selectRaw('ROUND(SUM((stake * odds) - stake)::numeric, 2) as profit')
                ->first();

            $totalProfitData = Slip::query()
                ->where('status', 'won')
                ->selectRaw('ROUND(SUM((stake * odds) - stake)::numeric, 2) as profit')
                ->first();

            $profit = $profitData->profit ?? 0;
            $totalProfit = $totalProfitData->profit ?? 0;

            return [
                'profit' => \Number::currency($profit, 'PLN', 'pl'),
                'total_profit' => \Number::currency($totalProfit, 'PLN', 'pl'),
                'accuracy' => $accuracy,
                'total_accuracy' => $totalAccuracy,
                'expert_pay' => $profit ? \Number::currency($profit * 0.15, in: 'PLN' ,locale: 'pl') : 0
            ];
        } catch (\Throwable $exception) {
            \Log::error('Error while getting stats:');
            \Log::error($exception);
            return [];
        }
    }
}
