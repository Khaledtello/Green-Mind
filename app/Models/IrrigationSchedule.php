<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrrigationSchedule extends Model
{
    use HasFactory;

    protected $table = 'irrigation_schedule';

    protected $fillable = [
        'plant_id',
        'recommended_date',
        'actual_date',
        'is_manual_override',
        'notes',
    ];

    protected $casts = [
        'recommended_date' => 'date',
        'actual_date' => 'date',
        'is_manual_override' => 'boolean',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}