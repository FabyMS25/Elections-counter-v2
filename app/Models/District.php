<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = ['name', 'number', 'municipality_id', 'latitude', 'longitude', 'active'];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }
    
    public function institutions()
    {
        return $this->hasMany(Institution::class);
    }
}

