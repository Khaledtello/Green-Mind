<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Plant extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'crop_id',
        'name',
        'planting_date',
        'harvest_date',
        'quantity',
        'health_status',
        'notes',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'harvest_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'quantity', 'health_status', 'notes'])
            ->logOnlyDirty()
            ->useLogName('plant');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(DiagnosisHistory::class);
    }

    public function irrigationSchedules()
    {
        return $this->hasMany(IrrigationSchedule::class);
    }
}