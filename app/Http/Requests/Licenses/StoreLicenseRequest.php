<?php

declare(strict_types=1);

namespace App\Http\Requests\Licenses;

use App\DTOs\Licenses\LicenseData;
use App\Enums\BillingCycle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLicenseRequest extends FormRequest
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
            'vendor' => ['required', 'string', 'max:255'],
            'seats_total' => ['required', 'integer', 'min:1', 'max:100000'],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'license_key' => ['nullable', 'string', 'max:255'],
            'cost_per_seat' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): LicenseData
    {
        return LicenseData::fromArray($this->validated());
    }
}
