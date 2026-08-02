<?php

namespace Database\Seeders;

use App\Models\IrrigationSchedule;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IrrigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plants = Plant::whereNull('harvest_date')->get();

        foreach ($plants as $plant) {
            IrrigationSchedule::create([
                'plant_id' => $plant->id,
                'recommended_date' => Carbon::now()->subDays(rand(4, 8)),
                'actual_date' => Carbon::now()->subDays(rand(3, 7)),
                'is_manual_override' => (bool)rand(0, 1),
                'notes' => 'ري سابق مكتمل',
            ]);

            IrrigationSchedule::create([
                'plant_id' => $plant->id,
                'recommended_date' => Carbon::now()->addDays(rand(0, 4)),
                'actual_date' => null,
                'is_manual_override' => false,
                'notes' => 'موعد ري قادم',
            ]);
        }
    }
}
