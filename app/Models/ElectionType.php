<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'election_date',
        'active',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}