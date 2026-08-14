<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HarvestedInventory extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'plant_id',
        'harvest_quantity',
        'current_quantity',
        'storage_location'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['current_quantity', 'storage_location'])
            ->logOnlyDirty()
            ->useLogName('inventory');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function usages()
    {
        return $this->hasMany(InventoryUsage::class);
    }
}
