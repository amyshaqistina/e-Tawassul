<?php

namespace Database\Factories;

use App\Models\PublicUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicUserFactory extends Factory
{
    protected $model = PublicUser::class;

    public function definition(): array
    {
        return [
            'first_name'            => $this->faker->firstName(),
            'last_name'             => $this->faker->lastName(),
            'email'                 => $this->faker->unique()->safeEmail(),
            'view_public_dashboard' => true,
            'makes_donation'        => $this->faker->boolean(70),
        ];
    }
}
