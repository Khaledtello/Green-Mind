<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryUsage extends Model
{
    protected $fillable = [
        'harvested_inventory_id',
        'user_id',
        'quantity_used',
        'reason'
    ];

    public function inventory()
    {
        return $this->belongsTo(HarvestedInventory::class, 'harvested_inventory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
