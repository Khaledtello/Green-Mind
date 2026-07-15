<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Crop extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name_ar',
        'name_en',
        'base_irrigation_days',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_ar', 'name_en', 'base_irrigation_days'])
            ->logOnlyDirty()
            ->useLogName('crop');
    }

    public function plants()
    {
        return $this->hasMany(Plant::class, 'crop_id');
    }
}