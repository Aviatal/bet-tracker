<?php

namespace App\Repositories;

use App\Models\Discipline;

class DisciplinesRepository
{
    //TODO: add interface
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Discipline::all();
    }

    public function store(string $name): Discipline
    {
        return Discipline::firstOrCreate(['name' => ucfirst($name)]);
    }
}
