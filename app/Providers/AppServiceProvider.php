<?php

namespace App\Providers;

use App\Models\Crop;
use App\Models\Disease;
use App\Models\IrrigationSchedule;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'Plant'              => Plant::class,
            'User'               => User::class,
            'Crop'               => Crop::class,
            'Disease'            => Disease::class,
            'IrrigationSchedule' => IrrigationSchedule::class,
        ]);
    }
}
