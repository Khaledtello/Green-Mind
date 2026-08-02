<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Disease extends Model
{
    use LogsActivity;

    protected $fillable = ['technical_name', 'ar_name', 'en_name'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['technical_name', 'ar_name', 'en_name'])
            ->logOnlyDirty()
            ->useLogName('disease');
    }

    public function plants()
    {
        return $this->hasMany(Plant::class);
    }
}
