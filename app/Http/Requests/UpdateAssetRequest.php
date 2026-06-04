<?php

namespace App\Http\Requests\Asset;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $assetId = $this->route('asset')?->id;
        return [
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:5000'],
            'category'        => ['required', new Enum(AssetCategory::class)],
            'status'          => ['required', new Enum(AssetStatus::class)],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'serial_number'   => ['nullable', 'string', 'max:100', "unique:assets,serial_number,{$assetId}"],
            'purchase_date'   => ['nullable', 'date'],
            'purchase_cost'   => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'warranty_expiry' => ['nullable', 'date'],
            'location'        => ['nullable', 'string', 'max:150'],
            'notes'           => ['nullable', 'string', 'max:5000'],
            'image'           => ['nullable', 'image', 'max:2048'],
        ];
    }
}