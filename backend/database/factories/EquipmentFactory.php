<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['MacBook Pro 14"', 'Dell Monitor 27"', 'Logitech MX Keys', 'iPhone 15', 'HP LaserJet Pro']),
            'type' => fake()->randomElement(['laptop', 'monitor', 'keyboard', 'phone', 'printer']),
            'serial_number' => fake()->unique()->bothify('??-####-####'),
            'status' => EquipmentStatus::Available,
            'assigned_to' => null,
        ];
    }
}
