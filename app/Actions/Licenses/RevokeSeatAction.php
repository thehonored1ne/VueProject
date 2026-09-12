<?php

declare(strict_types=1);

namespace App\Actions\Licenses;

use App\Models\SoftwareLicense;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RevokeSeatAction
{
    public function execute(SoftwareLicense $license, User $user): bool
    {
        $assignment = $license->assignments()->where('user_id', $user->id)->first();

        if (! $assignment) {
            throw ValidationException::withMessages([
                'seat' => 'This employee does not hold a seat on this software license.',
            ]);
        }

        return (bool) $assignment->delete();
    }
}
