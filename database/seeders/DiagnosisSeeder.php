<?php

namespace Database\Seeders;

use App\Models\DiagnosisHistory;
use App\Models\Disease;
use App\Models\Plant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiagnosisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $plants = Plant::whereNull('harvest_date')->get();
        $diseases = Disease::all();

        for ($i = 1; $i <= 120; $i++) {
            $plant = $plants->random();

            $disease = $plant->disease_id ? Disease::find($plant->disease_id) : $diseases->random();

            $date = rand(0, 100) < 60 ? Carbon::now()->subDays(rand(0, 6)) : Carbon::now()->subDays(rand(8, 30));
            $confidence = rand(0, 100) < 70 ? rand(9000, 9900) / 100 : rand(4500, 8500) / 100;

            DiagnosisHistory::create([
                'user_id' => $users->random()->id,
                'plant_id' => $plant->id,
                'disease_name_technical' => $disease->technical_name,
                'disease_name_arabic' => $disease->ar_name,
                'disease_name_english' => $disease->en_name,
                'confidence_percentage' => $confidence,
                'original_image_path' => 'diagnoses/seeded_image.jpg',
                'grad_cam_image_path' => 'diagnoses/seeded_gradcam.jpg',
                'treatment' => 'علاج تجريبي وهمي تم توليده للتجربة.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
