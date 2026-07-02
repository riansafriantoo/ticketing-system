<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TicketsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths
{
    public function __construct(private readonly Collection $tickets) {}

    /**
     * The actual rows handed to PhpSpreadsheet. We pass the already-fetched
     * collection straight through — filtering happened upstream in
     * TicketExportService so this class stays focused on presentation only.
     */
    public function collection(): Collection
    {
        return $this->tickets;
    }

    /**
     * Column headers, in the exact order they'll appear in the sheet.
     */
    public function headings(): array
    {
        return [
            'Ticket #',
            'Subject',
            'Status',
            'Priority',
            'Requester',
            'Requester Email',
            'Assignee',
            'Asset Tag',
            'Created At',
            'SLA Due At',
            'Resolved At',
            'Closed At',
        ];
    }

    /**
     * Transforms a single Ticket model into a flat array matching the
     * column order in headings(). This runs once per row.
     */
    public function map($ticket): array
    {
        return [
            $ticket->ticketNumber(),
            $ticket->subject,
            $ticket->status->label(),
            $ticket->priority->label(),
            $ticket->requester?->name ?? '—',
            $ticket->requester?->email ?? '—',
            $ticket->assignee?->name ?? 'Unassigned',
            $ticket->asset?->asset_tag ?? '—',
            $ticket->created_at?->format('Y-m-d H:i'),
            $ticket->sla_due_at?->format('Y-m-d H:i') ?? '—',
            $ticket->resolved_at?->format('Y-m-d H:i') ?? '—',
            $ticket->closed_at?->format('Y-m-d H:i')   ?? '—',
        ];
    }

    /**
     * Bold header row with a light fill, and center-align a few
     * columns that read better centered than left-aligned.
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B55E8'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J:O')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Freeze the header row so it stays visible while scrolling
        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Fixed column widths tuned to the typical content length of each
     * field — avoids the "everything crammed into 8 characters" look
     * that Excel defaults to.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Ticket #
            'B' => 38,  // Subject
            'C' => 13,  // Status
            'D' => 11,  // Priority
            'E' => 13,  // Category
            'F' => 20,  // Requester
            'G' => 26,  // Requester Email
            'H' => 20,  // Assignee
            'I' => 13,  // Asset Tag
            'J' => 17,  // Created At
            'K' => 17,  // SLA Due At
            'L' => 17,  // Resolved At
        ];
    }

    /**
     * Maps the internal duration status key to a clean label for the sheet.
     */
    private function slaResultLabel(?string $status): string
    {
        return match($status) {
            'within_sla'   => 'Within SLA',
            'breached_sla' => 'SLA Breached',
            'on_hold'      => 'On Hold',
            'open'         => 'Still Open',
            default        => '—',
        };
    }
}