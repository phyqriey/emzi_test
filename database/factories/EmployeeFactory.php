<?php
namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->numerify('01#-########'),  // Malaysian phone format
            'position' => $this->faker->randomElement([
                'Software Engineer', 'Project Manager', 'Data Analyst', 
                'Accountant', 'HR Executive', 'Marketing Specialist'
            ]),
            'department' => $this->faker->randomElement([
                'IT', 'Finance', 'Human Resources', 'Marketing', 'Operations'
            ]),
            'salary' => $this->faker->randomFloat(2, 3000, 15000),  // Adjusted salary range for Malaysia
            'hired_at' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
        ];
    }
}
