<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'address', 'locality_id', 'district_id', 'zone_id',
        'registered_citizens', 'total_computed_records', 'total_annulled_records',
        'total_enabled_records', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'registered_citizens' => 'integer',
        'total_computed_records' => 'integer',
        'total_annulled_records' => 'integer',
        'total_enabled_records' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode()
    {
        $prefix = 'INST';
        $maxId = static::max('id') ?? 0;
        $nextId = $maxId + 1;
        return $prefix . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function votingTables()
    {
        return $this->hasMany(VotingTable::class);
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function updateTotals()
    {
        $this->update([
            'total_computed_records' => $this->votingTables()->sum('computed_records'),
            'total_annulled_records' => $this->votingTables()->sum('annulled_records'),
            'total_enabled_records' => $this->votingTables()->sum('enabled_records'),
            'registered_citizens' => $this->votingTables()->sum('registered_citizens'),
        ]);
    }
}