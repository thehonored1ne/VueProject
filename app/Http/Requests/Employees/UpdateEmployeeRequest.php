<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use App\DTOs\Employees\EmployeeData;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
        /** @var User $targetUser */
        $targetUser = $this->route('user');
        $targetId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($targetId),
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    public function toDto(): EmployeeData
    {
        return EmployeeData::fromArray($this->validated());
    }
}
