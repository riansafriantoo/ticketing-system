<?php

namespace App\Http\Requests;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create
    }

    public function rules(): array
    {
        return [
            'subject'       => 'required|string|max:255',
            'description'   => 'required|string|max:20000',
            'priority'      => ['required', new Enum(TicketPriority::class)],
            'category'      => ['required', new Enum(TicketCategory::class)],
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ];
    }
}