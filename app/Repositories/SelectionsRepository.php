<?php

namespace App\Repositories;

use App\Models\Selection;

class SelectionsRepository
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Selection::all();
    }

    public function getByEvent(int $eventTypeId): \Illuminate\Database\Eloquent\Collection
    {
        return Selection::where('event_type_id', $eventTypeId)->get();
    }

    public static function store(string $name, int $eventTypeId): Selection
    {
        return Selection::firstOrCreate(['name' => ucfirst($name), 'event_type_id' => $eventTypeId]);
    }
}
