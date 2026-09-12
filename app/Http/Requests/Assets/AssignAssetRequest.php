<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use App\DTOs\Assets\AssignAssetData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignAssetRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'expected_return_at' => ['nullable', 'date', 'after:today'],
            'condition_on_assignment' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDto(): AssignAssetData
    {
        return AssignAssetData::fromArray($this->validated());
    }
}
