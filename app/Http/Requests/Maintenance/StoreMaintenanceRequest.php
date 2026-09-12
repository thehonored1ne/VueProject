<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use App\DTOs\Maintenance\MaintenanceData;
use App\Enums\MaintenanceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMaintenanceRequest extends FormRequest
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
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'title' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['nullable', Rule::enum(MaintenanceStatus::class)],
            'scheduled_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Convert validated input into strongly-typed DTO.
     */
    public function toDto(): MaintenanceData
    {
        return new MaintenanceData(
            assetId: (int) $this->validated('asset_id'),
            title: (string) $this->validated('title'),
            provider: (string) $this->validated('provider'),
            cost: $this->filled('cost') ? (float) $this->validated('cost') : null,
            status: $this->validated('status') ? MaintenanceStatus::from((string) $this->validated('status')) : MaintenanceStatus::InProgress,
            scheduledAt: $this->validated('scheduled_at') ? (string) $this->validated('scheduled_at') : null,
            startedAt: $this->validated('started_at') ? (string) $this->validated('started_at') : null,
            notes: $this->validated('notes') ? (string) $this->validated('notes') : null,
        );
    }
}
