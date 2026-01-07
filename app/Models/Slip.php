<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slip extends Model
{
    protected $guarded = ['id'];

    public function bets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bet::class);
    }
}
