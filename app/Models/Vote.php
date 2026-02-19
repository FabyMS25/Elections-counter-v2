<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'percentage',
        'voting_table_id',
        'candidate_id',
        'election_type_id',
        'user_id',
        'verified_at'
    ];
    protected $casts = [
        'quantity' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function candidate(){
        return $this->belongsTo(Candidate::class);
    }

    public function votingTable(){
        return $this->belongsTo(VotingTable::class);
    }
        
    public function electionType(){
        return $this->belongsTo(ElectionType::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getTallyAttribute(){
        $quantity = $this->quantity;
        $groups = floor($quantity / 5);
        $remaining = $quantity % 5;
        
        $tally = '';
        for ($i = 0; $i < $groups; $i++) {
            $tally .= '卌 ';
        }
        
        if ($remaining > 0) {
            $tally .= str_repeat('| ', $remaining);
        }
        
        return trim($tally);
    }

    public function getVisualTallyAttribute(){
        $quantity = $this->quantity;
        $groups = floor($quantity / 5);
        $remaining = $quantity % 5;
        
        $visual = '';
        for ($i = 0; $i < $groups; $i++) {
            $visual .= '<span class="tally-group">□ □ □ □ □</span> ';
        }
        
        if ($remaining > 0) {
            $visual .= '<span class="tally-remaining">' . str_repeat('■ ', $remaining) . '</span>';
        }
        
        return trim($visual);
    }
}