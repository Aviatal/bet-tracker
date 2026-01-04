<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bet extends Model
{
    protected $guarded = ['id'];
    public function home(): BelongsTo
    {
        return $this->belongsTo(Competitor::class, 'home_id');
    }

    public function away(): BelongsTo
    {
        return $this->belongsTo(Competitor::class, 'away_id');
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function selection(): BelongsTo
    {
        return $this->belongsTo(Selection::class);
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'timestamp',
        ];
    }
}
