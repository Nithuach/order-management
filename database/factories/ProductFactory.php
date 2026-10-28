<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => fake()->unique()->bothify('SKU-####'),
            'price' => fake()->randomFloat(2, 10, 1000),
            'tax_percentage' => 18,
            'stock_on_hand' => fake()->numberBetween(5, 100),
        ];
    }
}
