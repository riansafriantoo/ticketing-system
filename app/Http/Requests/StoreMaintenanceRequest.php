<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'               => ['required', 'in:repair,service,upgrade,inspection'],
            'description'        => ['required', 'string', 'max:5000'],
            'cost'               => ['nullable', 'numeric', 'min:0'],
            'vendor'             => ['nullable', 'string', 'max:150'],
            'performed_at'       => ['required', 'date', 'before_or_equal:today'],
            'next_maintenance_at'=> ['nullable', 'date', 'after:performed_at'],
        ];
    }
}