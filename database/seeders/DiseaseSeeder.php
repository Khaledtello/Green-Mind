<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diseases = [
            ['technical_name' => 'Tomato_Late_blight', 'ar_name' => 'لفحة البندورة المتأخرة', 'en_name' => 'Tomato Late Blight'],
            ['technical_name' => 'Tomato_Early_blight', 'ar_name' => 'لفحة البندورة المبكرة', 'en_name' => 'Tomato Early Blight'],
            ['technical_name' => 'Apple_scab', 'ar_name' => 'جرب التفاح', 'en_name' => 'Apple Scab'],
            ['technical_name' => 'Potato_Late_blight', 'ar_name' => 'لفحة البطاطا المتأخرة', 'en_name' => 'Potato Late Blight'],
        ];

        foreach ($diseases as $disease)
            Disease::create($disease);
    }
}
