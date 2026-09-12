<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Models\SoftwareLicense;
use App\Models\User;
use Illuminate\Database\Seeder;

class SoftwareLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = User::whereIn('email', [
            'sarah.chen@example.com',
            'alex.rivera@example.com',
            'jordan.taylor@example.com',
            'maya.lin@example.com',
        ])->get();

        if ($team->isEmpty()) {
            $team = User::factory()->count(4)->create();
        }

        $subscriptions = [
            [
                'name' => 'GitHub Copilot Enterprise',
                'vendor' => 'GitHub / Microsoft',
                'license_key' => 'GH-COPILOT-ENT-88912',
                'seats_total' => 20,
                'cost_per_seat' => 39.00,
                'billing_cycle' => BillingCycle::Monthly,
                'expires_at' => now()->addDays(20)->format('Y-m-d'), // Expiring soon for UI warning
                'notes' => 'Central developer AI license pool. Configured with corporate IP indemnification.',
                'assignees' => $team,
            ],
            [
                'name' => 'JetBrains All Products Pack',
                'vendor' => 'JetBrains s.r.o.',
                'license_key' => 'JB-APP-2024-99124-KL',
                'seats_total' => 8,
                'cost_per_seat' => 779.00,
                'billing_cycle' => BillingCycle::Yearly,
                'expires_at' => now()->addMonths(9)->format('Y-m-d'),
                'notes' => 'Covers IntelliJ IDEA Ultimate, PhpStorm, WebStorm, and DataGrip.',
                'assignees' => $team->take(3),
            ],
            [
                'name' => 'Figma Organization Tier',
                'vendor' => 'Figma, Inc.',
                'license_key' => 'FIG-ORG-4412-SEAT',
                'seats_total' => 12,
                'cost_per_seat' => 45.00,
                'billing_cycle' => BillingCycle::Monthly,
                'expires_at' => now()->addMonths(5)->format('Y-m-d'),
                'notes' => 'Primary UI design system and component spec workspace.',
                'assignees' => $team->take(2),
            ],
            [
                'name' => '1Password Business Vault',
                'vendor' => 'AgileBits, Inc.',
                'license_key' => '1PW-CORP-91284',
                'seats_total' => 25,
                'cost_per_seat' => 7.99,
                'billing_cycle' => BillingCycle::Monthly,
                'expires_at' => now()->addMonths(11)->format('Y-m-d'),
                'notes' => 'Enforced for all engineering and product personnel.',
                'assignees' => $team,
            ],
            [
                'name' => 'Postman Enterprise API Studio',
                'vendor' => 'Postman, Inc.',
                'license_key' => 'PST-ENT-77123',
                'seats_total' => 5,
                'cost_per_seat' => 19.00,
                'billing_cycle' => BillingCycle::Monthly,
                'expires_at' => now()->subDays(5)->format('Y-m-d'), // Expired license for UI warning badge
                'notes' => 'API workspace collaboration, collection runner monitors, and mock servers.',
                'assignees' => $team->take(2),
            ],
        ];

        foreach ($subscriptions as $data) {
            $assignees = $data['assignees'];
            unset($data['assignees']);

            $license = SoftwareLicense::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            foreach ($assignees as $user) {
                if (! $license->assignments()->where('user_id', $user->id)->exists()) {
                    $license->assignments()->create([
                        'user_id' => $user->id,
                        'assigned_at' => now()->subMonths(rand(1, 4)),
                        'notes' => 'Standard onboarding license allocation.',
                    ]);
                }
            }
        }
    }
}
