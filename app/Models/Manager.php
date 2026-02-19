<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'id_card',
        'role',
        'voting_table_id',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votingTable(): BelongsTo
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function institution()
    {
        return $this->hasOneThrough(
            Institution::class,
            VotingTable::class,
            'id', // Foreign key on voting_tables table
            'id', // Foreign key on institutions table
            'voting_table_id',
            'institution_id'
        );
    }
}