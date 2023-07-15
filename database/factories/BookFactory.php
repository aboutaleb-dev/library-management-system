<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()) . ' ' . ucfirst(fake()->unique()->word());
        $writer = fake()->unique()->firstName() . ' ' . fake()->unique()->lastName();
        return [
            'name' => $name,
            'writer' => $writer,
            'image' => fake()->imageUrl(300, 500, null, false, $name . "\n\n" . 'by ' . $writer),
            'stock' => fake()->numberBetween(0, 10),
        ];
    }
}
