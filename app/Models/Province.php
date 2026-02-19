<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id','latitude','longitude'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

    // public function institutions(): HasMany
    // {
    //     return $this->hasMany(Institution::class);
    // }

}
