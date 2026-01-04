<?php

namespace App\Repositories;

use App\Models\Competitor;

class CompetitorsRepository
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Competitor::all();
    }

    public static function store(string $name): Competitor
    {
        return Competitor::firstOrCreate(['name' => ucfirst($name)]);
    }
}
