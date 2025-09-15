<?php

namespace Database\Factories;

use App\Models\Actor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Actor>
 */
class ActorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Actor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'description' => fake()->paragraph(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->address(),
            'height' => fake()->randomElement(['5ft 6in', '5ft 8in', '6ft', '5ft 4in', '5ft 10in']),
            'weight' => fake()->randomElement(['120lbs', '150lbs', '180lbs', '200lbs', '140lbs']),
            'gender' => fake()->randomElement(['male', 'female']),
            'age' => fake()->numberBetween(18, 80),
        ];
    }
}
