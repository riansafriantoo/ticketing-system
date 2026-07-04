<?php

namespace Database\Seeders;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Enums\TicketCaseType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatusNew;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
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
            'department' => 'IPI',
        ]);
        $admin->assignRole('admin');

        // ── Agents ────────────────────────────────────────────────────────────
        $agents = User::factory(4)->create(['department' => 'IPI'])->each(fn ($u) => $u->assignRole('agent'));

        // ── End users ─────────────────────────────────────────────────────────
        $users = User::factory(10)->create(['department' => 'PERSERO'])->each(fn ($u) => $u->assignRole('user'));

        // ── Assets ────────────────────────────────────────────────────────────
        $assets = Asset::factory(30)->create();
 
        // Assign some assets to users
        $assignableAssets = $assets->where(fn ($a) => $a->status->isAvailableForAssignment())->take(15);
        $allUsers = $users->merge(collect([$admin]))->values();
 
        foreach ($assignableAssets as $index => $asset) {
            $assignee = $allUsers[$index % $allUsers->count()];
            $asset->update([
                'assigned_to' => $assignee->id,
                'assigned_at' => now()->subDays(rand(1, 180)),
                'status'      => AssetStatus::Active->value,
            ]);
 
            AssetAssignment::create([
                'asset_id'    => $asset->id,
                'user_id'     => $assignee->id,
                'assigned_by' => $admin->id,
                'assigned_at' => $asset->assigned_at,
                'notes'       => 'Initial assignment',
            ]);
        }
 
        // Add some maintenance records
        $assets->take(10)->each(function ($asset) use ($admin) {
            AssetMaintenance::create([
                'asset_id'     => $asset->id,
                'performed_by' => $admin->id,
                'type'         => fake()->randomElement(['service','repair','inspection']),
                'description'  => fake()->sentence(10),
                'cost'         => fake()->optional()->randomFloat(2, 50, 500),
                'vendor'       => fake()->optional()->company(),
                'performed_at' => fake()->dateTimeBetween('-1 year', 'now'),
            ]);
        });

        // ── Tickets ───────────────────────────────────────────────────────────
        $allRequesters = $users->merge(collect([$admin]));
        $ticketAssets  = $assets->random(min(10, $assets->count()));

        foreach ($allRequesters as $requester) {
            $count = rand(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $priority = collect(TicketPriority::cases())->random();
                $status   = collect(TicketStatusNew::cases())->random();
                $case_type   = collect(TicketCaseType::cases())->random();
                $asset    = rand(0, 1) ? $ticketAssets->random() : null;

                $ticket = Ticket::create([
                    'subject'      => fake()->sentence(6),
                    'description'  => fake()->paragraphs(3, true),
                    'status'       => $status,
                    'priority'     => $priority,
                    'case_type'    => $case_type,
                    'requester_id' => $requester->id,
                    'assignee_id'  => $agents->random()->id,
                    'asset_id'     => $asset?->id,
                    'sla_due_at'   => now()->addHours($priority->slaHours()),
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