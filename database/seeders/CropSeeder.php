<?php

namespace Database\Seeders;

use App\Models\Crop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Crop::create(['name_ar' => 'بندورة', 'name_en' => 'Tomato', 'base_irrigation_days' => 3]);
        Crop::create(['name_ar' => 'بطاطا', 'name_en' => 'Potato', 'base_irrigation_days' => 4]);
        Crop::create(['name_ar' => 'تفاح', 'name_en' => 'Apple', 'base_irrigation_days' => 5]);
    }
}
