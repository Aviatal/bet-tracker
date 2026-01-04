<?php

namespace App\Repositories;

use App\Models\Selection;

class SelectionsRepository
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Selection::all();
    }

    public static function store(string $name): Selection
    {
        return Selection::firstOrCreate(['name' => ucfirst($name)]);
    }
}
