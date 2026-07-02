<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Http\Requests\ExportTicketsRequest;
use App\Services\TicketExportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketExportController extends Controller
{
    public function __construct(private readonly TicketExportService $service) {}

    /**
     * GET /tickets/export
     *
     * Validates the filter inputs, builds the scoped query, and streams
     * an XLSX file back to the browser. No view is rendered — the
     * response IS the file download.
     */
    public function __invoke(ExportTicketsRequest $request): BinaryFileResponse
    {
        $filters = $request->validated();

        $tickets  = $this->service->fetch($filters, $request->user());
        $filename = $this->service->buildFilename($filters);

        return Excel::download(new TicketsExport($tickets), $filename);
    }
}