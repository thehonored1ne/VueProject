<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;

test('app:verify command executes verification suite cleanly', function () {
    Process::fake([
        '*' => Process::result('Everything clean', '', 0),
    ]);

    $this->artisan('app:verify')
        ->expectsOutputToContain('Starting Full-Stack Verification Suite...')
        ->expectsOutputToContain('Laravel Pint (PHP Style)')
        ->expectsOutputToContain('Pest PHP (Test Suite)')
        ->expectsOutputToContain('ESLint (Frontend Lint)')
        ->expectsOutputToContain('Prettier (Formatting)')
        ->expectsOutputToContain('All verification checks passed cleanly!')
        ->assertSuccessful();
});

test('app:verify reports failure when a check fails', function () {
    Process::fake([
        '*vendor*pint*' => Process::result('', 'Style violation detected', 1),
        '*' => Process::result('', '', 0),
    ]);

    $this->artisan('app:verify')
        ->expectsOutputToContain('Verification suite encountered failures.')
        ->assertFailed();
});
