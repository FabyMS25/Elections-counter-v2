<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingTable extends Model
{
    use HasFactory;

    protected $fillable = ['code','number','from_name','to_name','registered_citizens','computed_records','annulled_records','enabled_records','status','institution_id'];
    protected $casts = ['registered_citizens' => 'integer','number' => 'integer',
                    'computed_records' => 'integer','annulled_records' => 'integer','enabled_records'=>'integer'];

    public const STATUS_ACTIVE = 'activo';
    public const STATUS_CLOSED = 'cerrado';
    public const STATUS_PENDING = 'pendiente';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_CLOSED => 'Cerrado',
            self::STATUS_PENDING => 'Pendiente',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($votingTable) {
            if (empty($votingTable->code)) {
                $votingTable->code = self::generateUniqueCode();
            }
        });
    }

    protected static function generateUniqueCode(): string
    {
        $prefix = 'VT';
        $number = 1;        
        $lastCode = self::orderBy('id', 'desc')->value('code');
        if ($lastCode) {
            $lastNumber = (int) substr($lastCode, 2);
            $number = $lastNumber + 1;
        }        
        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function managers()
    {
        return $this->hasMany(Manager::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

}
