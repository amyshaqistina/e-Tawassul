<?php

namespace Database\Factories;

use App\Models\Crisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class CrisisFactory extends Factory
{
    protected $model = Crisis::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['accident', 'illness', 'natural_disaster', 'family_emergency']);

        $descriptions = [
            'accident'        => 'Student involved in severe motorcycle accident requiring extended hospitalization.',
            'illness'         => 'Student diagnosed with critical illness, in ICU under intensive care.',
            'natural_disaster'=> 'Family home affected by severe flooding, urgent shelter and recovery support needed.',
            'family_emergency'=> "Student's parent passed away unexpectedly; family needs bereavement and funeral support.",
            'death'           => "Student's tragic passing, family requires support for funeral arrangements.",
        ];

        $target = $this->faker->randomElement([20000, 30000, 50000, 75000]);
        $raised = $this->faker->numberBetween(0, (int)($target * 0.95));

        return [
            'crisis_type'        => $type,
            'crisis_description' => $descriptions[$type] ?? 'Critical crisis affecting student welfare.',
            'crisis_details'     => $this->faker->paragraph(3),
            'impact_level'       => $this->faker->randomElement(['critical', 'high', 'medium', 'low']),
            'location'           => $this->faker->randomElement(['Gombak Campus, Selangor', 'Kuantan Campus, Pahang', 'Kuala Lumpur', 'Pekan, Pahang']),
            'date_reported'      => $this->faker->dateTimeBetween('-3 months', 'now'),
            'status'             => 'active',
            'donation_target'    => $target,
            'donation_raised'    => $raised,
        ];
    }
}
