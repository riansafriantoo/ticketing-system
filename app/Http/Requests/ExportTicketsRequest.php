<?php

namespace App\Http\Requests;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;

class ExportTicketsRequest extends FormRequest
{
    /**
     * Any authenticated user may export — the underlying query is already
     * scoped to "my tickets only" for non-agents inside TicketExportService,
     * so a regular user can never export tickets that don't belong to them.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Date range is the core filter this feature was built for.
            // Both are optional — omitting both exports everything the
            // user is allowed to see.
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],

            // Secondary filters — same options already used on the index page,
            // so the export always matches whatever the user is looking at.
            'status'    => ['nullable', 'in:' . implode(',', TicketStatus::values())],
            'priority'  => ['nullable', 'in:' . implode(',', TicketPriority::values())],
            'category'  => ['nullable', 'in:' . implode(',', TicketCategory::values())],
            'assignee'  => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}