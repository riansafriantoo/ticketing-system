<?php

namespace Database\Factories;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $category  = fake()->randomElement(AssetCategory::cases());
        $purchased = fake()->dateTimeBetween('-4 years', '-1 month');

        return [
            'asset_tag'      => 'AST-' . strtoupper(Str::random(6)),
            'name'           => $this->nameForCategory($category),
            'description'    => fake()->optional()->sentence(10),
            'category'       => $category,
            'status'         => fake()->randomElement(AssetStatus::cases()),
            'brand'          => fake()->randomElement(['Dell','HP','Lenovo','Apple','Samsung','Cisco','Logitech','Microsoft']),
            'model'          => strtoupper(fake()->bothify('??-####')),
            'serial_number'  => strtoupper(fake()->unique()->bothify('??########')),
            'purchase_date'  => $purchased,
            'purchase_cost'  => fake()->randomFloat(2, 100, 8000),
            'warranty_expiry'=> fake()->dateTimeBetween('-1 year', '+3 years'),
            'location'       => fake()->randomElement(['Office 1A','Server Room','Warehouse','IT Dept','Floor 2','Reception']),
            'notes'          => fake()->optional()->sentence(),
        ];
    }

    private function nameForCategory(AssetCategory $cat): string
    {
        return match($cat) {
            AssetCategory::Laptop    => fake()->randomElement(['ThinkPad X1','MacBook Pro','Dell XPS','HP EliteBook']),
            AssetCategory::Desktop   => fake()->randomElement(['OptiPlex 7090','ProDesk 600','ThinkCentre M90']),
            AssetCategory::Monitor   => fake()->randomElement(['27" 4K Monitor','24" FHD Display','32" Curved Monitor']),
            AssetCategory::Printer   => fake()->randomElement(['LaserJet Pro','OfficeJet 250','MFC-L8900CDW']),
            AssetCategory::Network   => fake()->randomElement(['Cisco Switch 24-Port','TP-Link Router','Ubiquiti AP']),
            AssetCategory::Phone     => fake()->randomElement(['iPhone 14','Galaxy S23','Pixel 7']),
            default                  => ucfirst($cat->value) . ' ' . fake()->word(),
        };
    }

    public function assigned(): static
    {
        return $this->state(['status' => AssetStatus::Active]);
    }

    public function unassigned(): static
    {
        return $this->state([
            'status'      => AssetStatus::InStorage,
            'assigned_to' => null,
            'assigned_at' => null,
        ]);
    }

    public function underRepair(): static
    {
        return $this->state(['status' => AssetStatus::UnderRepair]);
    }
}