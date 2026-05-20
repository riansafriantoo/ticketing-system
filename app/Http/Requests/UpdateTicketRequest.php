<?php

namespace App\Http\Requests;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAgent();
    }

    public function rules(): array
    {
        return [
            'subject'     => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:20000',
            'priority'    => ['sometimes', 'required', new Enum(TicketPriority::class)],
            'category'    => ['sometimes', 'required', new Enum(TicketCategory::class)],
        ];
    }
}