<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(TicketStatus::cases());

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'category_id' => Category::factory(),
            'author_id' => User::factory(),
            'assignee_id' => fake()->optional(0.6)->passthrough(User::factory()),
            'department_id' => Department::factory(),
            'status' => $status,
            'priority' => fake()->randomElement(Priority::cases()),
            'resolved_at' => $status === TicketStatus::Done ? fake()->dateTimeBetween('-1 month') : null,
        ];
    }
}
