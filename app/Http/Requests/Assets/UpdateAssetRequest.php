<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use App\DTOs\Assets\AssetData;
use App\Enums\AssetType;
use App\Models\Asset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
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
        /** @var Asset $asset */
        $asset = $this->route('asset');

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AssetType::class)],
            'asset_tag' => ['nullable', 'string', 'max:50', Rule::unique('assets', 'asset_tag')->ignore($asset->id)],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('assets', 'serial_number')->ignore($asset->id)],
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
