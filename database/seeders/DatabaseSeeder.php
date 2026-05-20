<?php

namespace Database\Seeders;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────────
        foreach (['admin', 'agent', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── Admin ─────────────────────────────────────────────────────────────
        $admin = User::factory()->create([
            'name'       => 'Admin User',
            'email'      => 'admin@it-ticketing.app',
            'department' => 'IT',
        ]);
        $admin->assignRole('admin');

        // ── Agents ────────────────────────────────────────────────────────────
        $agents = User::factory(4)->create()->each(fn ($u) => $u->assignRole('agent'));

        // ── End users ─────────────────────────────────────────────────────────
        $users = User::factory(10)->create()->each(fn ($u) => $u->assignRole('user'));

        // ── Tickets ───────────────────────────────────────────────────────────
        $allRequesters = $users->merge(collect([$admin]));

        foreach ($allRequesters as $requester) {
            $count = rand(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $priority = collect(TicketPriority::cases())->random();
                $status   = collect(TicketStatus::cases())->random();

                $ticket = Ticket::create([
                    'subject'      => fake()->sentence(6),
                    'description'  => fake()->paragraphs(3, true),
                    'status'       => $status,
                    'priority'     => $priority,
                    'category'     => collect(TicketCategory::cases())->random(),
                    'requester_id' => $requester->id,
                    'assignee_id'  => $agents->random()->id,
                    'sla_due_at'   => now()->addHours($priority->slaHours()),
                    'resolved_at'  => $status === TicketStatus::Resolved ? now()->subHours(rand(1, 48)) : null,
                ]);

                // Add comments
                $commentCount = rand(0, 4);
                for ($j = 0; $j < $commentCount; $j++) {
                    Comment::create([
                        'ticket_id'   => $ticket->id,
                        'user_id'     => rand(0, 1) ? $requester->id : $agents->random()->id,
                        'body'        => fake()->paragraph(),
                        'is_internal' => false,
                        'created_at'  => now()->subMinutes(rand(5, 1440)),
                        'updated_at'  => now()->subMinutes(rand(1, 60)),
                    ]);
                }
            }
        }

        $this->command->info('✓ Seeded: roles, 1 admin, 4 agents, 10 users, and demo tickets');
        $this->command->info('  Admin login: admin@it-ticketing.app / password');
    }
}