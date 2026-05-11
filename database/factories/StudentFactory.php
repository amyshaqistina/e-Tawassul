<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $kulliyyahs = [
            'Kulliyyah of Information & Communication Technology',
            'Kulliyyah of Engineering',
            'Kulliyyah of Economics & Management Sciences',
            'Kulliyyah of Education',
            'Kulliyyah of Architecture & Environmental Design',
            'Kulliyyah of Laws',
        ];

        $programmes = ['BCS', 'BIT', 'BEE', 'BME', 'BBA', 'BACC', 'BEd', 'LLB'];
        $mahallahs  = ['Mahallah Ali', 'Mahallah Uthman', 'Mahallah Salahuddin', 'Mahallah Asma', 'Mahallah Maryam', 'Mahallah Halimah'];

        $first = $this->faker->firstName();
        $last  = $this->faker->lastName();
        $studentId = (string) $this->faker->unique()->numberBetween(2000000, 2999999);

        return [
            'student_id'        => $studentId,
            'first_name'        => $first,
            'last_name'         => $last,
            'email'             => strtolower($first.'.'.$last.'@live.iium.edu.my'),
            'kulliyyah'         => $this->faker->randomElement($kulliyyahs),
            'programme'         => $this->faker->randomElement($programmes),
            'year_of_study'     => (string) $this->faker->numberBetween(1, 4),
            'mahallah'          => $this->faker->randomElement($mahallahs),
            'phone'             => '+60'.$this->faker->numerify('1#########'),
            'gender'            => $this->faker->randomElement(['Male', 'Female']),
            'nationality'       => $this->faker->randomElement(['Malaysian', 'Indonesian', 'Nigerian', 'Bangladeshi']),
            'date_of_birth'     => $this->faker->dateTimeBetween('-25 years', '-18 years'),
            'enrollment_status' => 'Active',
            'emergency_contact' => '+60'.$this->faker->numerify('1#########'),
            'status'            => 'active',
            'password'          => Hash::make('password'),
        ];
    }
}
