<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Console\Command;

class TicketStatsCommand extends Command
{
    protected $signature   = 'it-ticketing:stats';
    protected $description = 'Display current ticket statistics';

    public function handle(): int
    {
        $this->info('it-ticketing — Ticket Statistics');
        $this->line('');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total open',       Ticket::whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count()],
                ['In progress',      Ticket::where('status', TicketStatus::InProgress)->count()],
                ['On hold',          Ticket::where('status', TicketStatus::OnHold)->count()],
                ['Overdue (SLA)',     Ticket::overdue()->count()],
                ['Resolved today',   Ticket::whereDate('resolved_at', today())->count()],
                ['Total all time',   Ticket::count()],
            ]
        );

        $avg = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg')
            ->value('avg');

        $this->line('');
        $this->line("Average resolution time: <info>" . round((float) $avg, 1) . " hours</info>");

        return self::SUCCESS;
    }
}