<?php

namespace Database\Factories;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $priority = fake()->randomElement(TicketPriority::cases());
        $status   = fake()->randomElement(TicketStatus::cases());

        return [
            'uuid'         => (string) Str::uuid(),
            'subject'      => fake()->sentence(6),
            'description'  => fake()->paragraphs(2, true),
            'status'       => $status,
            'priority'     => $priority,
            'category'     => fake()->randomElement(TicketCategory::cases()),
            'requester_id' => User::factory(),
            'assignee_id'  => null,
            'asset_id'     => null,
            'sla_due_at'   => now()->addHours($priority->slaHours()),
            'resolved_at'  => $status === TicketStatus::Resolved ? now()->subHours(rand(1, 48)) : null,
            'sla_breached' => false,
        ];
    }

    public function open(): static     { return $this->state(['status' => TicketStatus::Open]); }
    public function overdue(): static  { return $this->state(['status' => TicketStatus::Open, 'sla_due_at' => now()->subHours(2)]); }
    public function resolved(): static { return $this->state(['status' => TicketStatus::Resolved, 'resolved_at' => now()->subHours(rand(1,24))]); }
    public function critical(): static { return $this->state(['priority' => TicketPriority::Critical]); }
} 