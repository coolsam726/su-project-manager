<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Projects\Models\Department::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

