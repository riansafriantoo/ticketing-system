<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SLA Response Hours per Priority
    |--------------------------------------------------------------------------
    */
    'sla' => [
        'low'      => (int) env('SLA_LOW_HOURS',      72),
        'medium'   => (int) env('SLA_MEDIUM_HOURS',   24),
        'high'     => (int) env('SLA_HIGH_HOURS',      8),
        'critical' => (int) env('SLA_CRITICAL_HOURS',  4),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'tickets_per_page' => (int) env('TICKETS_PER_PAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_files'      => 5,
        'max_size_kb'    => 10240, // 10 MB per file
        'allowed_mimes'  => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'],
    ],
];