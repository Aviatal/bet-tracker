<?php

namespace App\Repositories;

use App\Models\EventType;

class EventTypesRepository
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return EventType::all();
    }

    public static function store(string $name): EventType
    {
        return EventType::firstOrCreate(['name' => ucfirst($name)]);
    }
}
