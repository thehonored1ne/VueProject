<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use App\DTOs\Assets\AssetData;
use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AssetType::class)],
            'asset_tag' => ['nullable', 'string', 'max:50', 'unique:assets,asset_tag'],
            'status' => ['nullable', Rule::enum(AssetStatus::class)],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'purchased_at' => ['nullable', 'date'],
            'warranty_expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): AssetData
    {
        return AssetData::fromArray($this->validated());
    }
}
