<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\DTOs\Employees\EmployeeData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateEmployeeAction
{
    public function execute(EmployeeData $data): User
    {
        $password = $data->password ?: Str::random(16);

        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($password),
        ]);
    }
}
