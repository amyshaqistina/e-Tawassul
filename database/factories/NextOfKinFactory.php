<?php

namespace Database\Factories;

use App\Models\NextOfKin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class NextOfKinFactory extends Factory
{
    protected $model = NextOfKin::class;

    public function definition(): array
    {
        return [
            'first_name'                  => $this->faker->firstName(),
            'last_name'                   => $this->faker->lastName(),
            'relationship_to_student'     => $this->faker->randomElement(['Father', 'Mother', 'Brother', 'Sister', 'Uncle', 'Aunt']),
            'email'                       => $this->faker->unique()->safeEmail(),
            'phone'                       => '+60'.$this->faker->numerify('1#########'),
            'access_level'                => 'standard',
            'emergency_contact_verified'  => true,
            'consent_date'                => now()->subMonths(2),
            'expiry_date'                 => now()->addYear(),
            'password'                    => Hash::make('password'),
        ];
    }
}
