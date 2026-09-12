<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use App\DTOs\Assets\CheckInAssetData;
use App\Enums\AssetStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInAssetRequest extends FormRequest
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
            'target_status' => ['required', Rule::in([AssetStatus::Available->value, AssetStatus::Maintenance->value])],
            'condition_on_return' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDto(): CheckInAssetData
    {
        return CheckInAssetData::fromArray($this->validated());
    }
}
