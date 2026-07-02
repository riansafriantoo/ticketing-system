<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TicketExportService
{
    /**
     * Build the filtered ticket query for export, scoped by the
     * requesting user's role.
     *
     * Agents/admins can export any ticket matching the filters.
     * Regular users only ever see their own tickets, mirroring the
     * same restriction already applied on the tickets index page —
     * exporting never leaks data a user couldn't already see on screen.
     *
     * @param  array{
     *   date_from?: string|null,
     *   date_to?: string|null,
     *   status?: string|null,
     *   priority?: string|null,
     *   category?: string|null,
     *   assignee?: int|null,
     * } $filters
     */
    public function filteredQuery(array $filters, User $requester): Builder
    {
        return Ticket::query()
            ->with(['requester', 'assignee'])
            ->when(!$requester->isAgent(), fn ($q) => $q->forRequester($requester->id))
            ->dateRange($filters['date_from'] ?? null, $filters['date_to'] ?? null)
            ->when(!empty($filters['status']),   fn ($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['priority']), fn ($q) => $q->where('priority', $filters['priority']))
            ->when(!empty($filters['requester']), fn ($q) => $q->where('requester_id', $filters['requester']))
            ->when(!empty($filters['assignee']), fn ($q) => $q->where('assignee_id', $filters['assignee']))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Pull the filtered tickets as a plain Collection, ready to hand
     * to the Excel export class. Chunking isn't needed here since
     * a helpdesk's ticket volume is small enough to load in one go —
     * if this ever needs to scale to 100k+ rows, swap this for
     * FromQuery + chunking inside TicketsExport instead.
     */
    public function fetch(array $filters, User $requester): Collection
    {
        return $this->filteredQuery($filters, $requester)->get();
    }

    /**
     * Build a clean, human-readable filename that reflects the
     * applied date range, e.g.:
     *   tickets_2026-06-01_to_2026-06-30.xlsx
     *   tickets_all-time.xlsx
     */
    public function buildFilename(array $filters): string
    {
        $from = $filters['date_from'] ?? null;
        $to   = $filters['date_to']   ?? null;

        if ($from && $to) {
            return "tickets_{$from}_to_{$to}.xlsx";
        }

        if ($from) {
            return "tickets_from_{$from}.xlsx";
        }

        if ($to) {
            return "tickets_until_{$to}.xlsx";
        }

        return 'tickets_all-time.xlsx';
    }
}