<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosisHistory extends Model
{
    use HasFactory;

    protected $table = 'diagnosis_history';

    protected $fillable = [
        'user_id',
        'plant_id',
        'disease_name_technical',
        'disease_name_arabic',
        'confidence_percentage',
        'original_image_path',
        'grad_cam_image_path',
        'treatment',
    ];

    protected $casts = [
        'confidence_percentage' => 'decimal:2',
    ];


    protected function originalImagePath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset('storage/' . $value) : null,
        );
    }

    protected function gradCamImagePath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset('storage/' . $value) : null,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
