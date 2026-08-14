<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Disease;
use App\Models\HarvestedInventory;
use App\Models\InventoryUsage;
use App\Models\Plant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $crops = Crop::all();

        foreach ($crops as $crop) {
            for ($i = 1; $i <= 10; $i++) {
                $isHarvested = rand(0, 100) < 20;
                $isDiseased = !$isHarvested && rand(0, 100) < 40;
                $plantingDate = Carbon::now()->subDays(rand(10, 90));

                $plant = Plant::create([
                    'user_id' => $users->random()->id,
                    'crop_id' => $crop->id,
                    'name' => "دفعة {$crop->name_ar} - $i",
                    'planting_date' => $plantingDate,
                    'harvest_date' => $isHarvested ? Carbon::now()->subDays(rand(1, 5)) : null,
                    'quantity' => rand(50, 500),
                    'disease_id' => $isDiseased ? Disease::inRandomOrder()->first()->id : null,
                    'notes' => 'بيانات تم توليدها تلقائياً للتجربة',
                ]);

                if ($isHarvested) {
                    $harvestQty = rand(500, 2000);
                    $usedQty = rand(0, $harvestQty);

                    $inventory = HarvestedInventory::create([
                        'plant_id'         => $plant->id,
                        'harvest_quantity' => $harvestQty,
                        'current_quantity' => $harvestQty - $usedQty, // المتبقي
                        'storage_location' => 'مستودع ' . rand(1, 3),
                    ]);

                    if ($usedQty > 0) {
                        InventoryUsage::create([
                            'harvested_inventory_id' => $inventory->id,
                            'user_id' => $users->random()->id,
                            'quantity_used' => $usedQty,
                            'reason' => ['بيع للسوق المحلي', 'تلف بسبب التخزين', 'نقل لمستودع آخر'][rand(0, 2)],
                        ]);
                    }
                }
            }
        }
    }
}
