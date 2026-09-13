<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\DTOs\Employees\EmployeeData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateEmployeeAction
{
    public function execute(User $user, EmployeeData $data): User
    {
        $payload = [
            'name' => $data->name,
            'email' => $data->email,
        ];

        if ($data->password) {
            $payload['password'] = Hash::make($data->password);
        }

        $user->update($payload);

        return $user->fresh();
    }
}
