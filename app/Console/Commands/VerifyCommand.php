<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

final class VerifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verify
                            {--fix : Automatically repair styling violations with Pint and Prettier}
                            {--skip-frontend : Skip frontend ESLint and Prettier checks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the full-stack quality verification gate (Pint, Pest, ESLint, Prettier)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $skipFrontend = (bool) $this->option('skip-frontend');

        $this->info('Starting Full-Stack Verification Suite...');
        $this->newLine();

        $results = [];
        $hasFailures = false;

        // 1. Laravel Pint (PHP Code Style)
        $pintArgs = $fix ? [] : ['--test'];
        $pintResult = $this->runCheck(
            name: 'Laravel Pint (PHP Style)',
            command: [PHP_BINARY, base_path('vendor/bin/pint'), ...$pintArgs],
        );
        $results[] = $pintResult;
        if (! $pintResult['passed']) {
            $hasFailures = true;
        }

        // 2. Pest PHP (Test Suite)
        $pestResult = $this->runCheck(
            name: 'Pest PHP (Test Suite)',
            command: [PHP_BINARY, 'artisan', 'test'],
            env: ['APP_ENV' => 'testing'],
        );
        $results[] = $pestResult;
        if (! $pestResult['passed']) {
            $hasFailures = true;
        }

        // 3. Frontend Checks (ESLint & Prettier)
        if (! $skipFrontend) {
            // ESLint
            $npmCommand = windows_os() ? 'npm.cmd' : 'npm';

            $eslintResult = $this->runCheck(
                name: 'ESLint (Frontend Lint)',
                command: [$npmCommand, 'run', 'lint'],
            );
            $results[] = $eslintResult;
            if (! $eslintResult['passed']) {
                $hasFailures = true;
            }

            // Prettier
            $prettierScript = $fix ? 'format' : 'format:check';
            $prettierResult = $this->runCheck(
                name: 'Prettier (Formatting)',
                command: [$npmCommand, 'run', $prettierScript],
            );
            $results[] = $prettierResult;
            if (! $prettierResult['passed']) {
                $hasFailures = true;
            }
        }

        // Render Summary Table
        $this->newLine();
        $this->table(
            ['Check', 'Status', 'Duration'],
            array_map(fn (array $res) => [
                $res['name'],
                $res['passed'] ? '<info>✓ PASSED</info>' : '<error>✗ FAILED</error>',
                $res['duration'],
            ], $results)
        );

        if ($hasFailures) {
            $this->newLine();
            $this->error('Verification suite encountered failures. Please resolve the errors above.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('All verification checks passed cleanly!');

        return self::SUCCESS;
    }

    /**
     * Execute a check process, stream relevant output, and record metrics.
     *
     * @param  list<string>  $command
     * @param  array<string, string>  $env
     * @return array{name: string, passed: bool, duration: string}
     */
    private function runCheck(string $name, array $command, array $env = []): array
    {
        $this->output->write("  Running {$name}... ");
        $startTime = microtime(true);

        $process = Process::path(base_path())
            ->timeout(180)
            ->env($env)
            ->run($command);

        $duration = round(microtime(true) - $startTime, 2).'s';

        if ($process->successful()) {
            $this->output->writeln("<info>PASSED</info> ({$duration})");

            return [
                'name' => $name,
                'passed' => true,
                'duration' => $duration,
            ];
        }

        $this->output->writeln("<error>FAILED</error> ({$duration})");

        // Display failure output indented
        $output = trim($process->output() ?: $process->errorOutput());
        if (! empty($output)) {
            $this->line('');
            $this->line('    <fg=gray>'.str_replace("\n", "\n    ", $output).'</>');
            $this->line('');
        }

        return [
            'name' => $name,
            'passed' => false,
            'duration' => $duration,
        ];
    }
}
