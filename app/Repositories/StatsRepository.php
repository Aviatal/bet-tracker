<?php

namespace App\Repositories;

use App\Models\Slip;

class StatsRepository
{
    public function all(): array
    {
        try {
            $accuracy = Slip::query()->select(\DB::raw('COUNT(id) as wonslips'), \DB::raw("(SELECT COUNT(id) FROM slips WHERE status != 'pending') AS totalslipscount"))->where('status', 'won')->first();
            $profit = Slip::query()->select(\DB::raw('ROUND(SUM((stake * odds) - stake), 2) as profit'))->where('status', 'won')->first();
            return [
                'profit' => $profit?->getAttribute('profit') ?? 0,
                'accuracy' => $accuracy ? (($accuracy->getAttribute('wonslips') / $accuracy->getAttribute('totalslipscount')) * 100) : 0,
                'expert_pay' => $profit ? \Number::currency($profit->getAttribute('profit') * 0.15, in: 'PLN' ,locale: 'pl') : 0
            ];
        } catch (\Throwable $exception) {
            \Log::error('Error while getting stats:');
            \Log::error($exception);
            return [];
        }
    }
}
