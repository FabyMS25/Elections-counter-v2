<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'province_id','latitude','longitude'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    
    public function localities()
    {
        return $this->hasMany(Locality::class);
    }
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    // public function institutions()
    // {
    //     return $this->hasMany(Institution::class);
    // }
}
