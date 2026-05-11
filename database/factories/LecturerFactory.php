<?php

namespace Database\Factories;

use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class LecturerFactory extends Factory
{
    protected $model = Lecturer::class;

    public function definition(): array
    {
        $first = $this->faker->firstName();
        $last  = $this->faker->lastName();
        return [
            'first_name' => $first,
            'last_name'  => $last,
            'email'      => strtolower($first.'.'.$last.'@iium.edu.my'),
            'department' => $this->faker->randomElement([
                'KICT - Computer Science', 'KICT - Information Systems',
                'KOE - Electrical', 'KOE - Mechatronics',
                'KENMS - Business Administration', 'KOL - Civil Law',
            ]),
            'password'   => Hash::make('password'),
        ];
    }
}
