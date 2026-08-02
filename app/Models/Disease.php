<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    protected $fillable = ['technical_name', 'ar_name', 'en_name'];

    public function plants()
    {
        return $this->hasMany(Plant::class);
    }
}
