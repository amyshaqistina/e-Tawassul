<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'admin_name'  => $this->faker->name(),
            'email'       => $this->faker->unique()->safeEmail(),
            'role'        => 'admin',
            'active'      => true,
            'permissions' => ['verify_crisis', 'verify_death', 'trigger_ldms', 'manage_donations'],
            'password'    => Hash::make('password'),
        ];
    }
}
