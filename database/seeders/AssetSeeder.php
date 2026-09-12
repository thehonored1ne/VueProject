<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first() ?? User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $team = [
            User::firstOrCreate(['email' => 'sarah.chen@example.com'], [
                'name' => 'Sarah Chen',
                'password' => bcrypt('password'),
            ]),
            User::firstOrCreate(['email' => 'alex.rivera@example.com'], [
                'name' => 'Alex Rivera',
                'password' => bcrypt('password'),
            ]),
            User::firstOrCreate(['email' => 'jordan.taylor@example.com'], [
                'name' => 'Jordan Taylor',
                'password' => bcrypt('password'),
            ]),
            User::firstOrCreate(['email' => 'maya.lin@example.com'], [
                'name' => 'Maya Lin',
                'password' => bcrypt('password'),
            ]),
        ];

        $hardwareList = [
            // Laptops
            [
                'asset_tag' => 'AST-1001',
                'name' => 'MacBook Pro 16" M3 Max (64GB / 2TB)',
                'type' => AssetType::Laptop,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'C02G8790MD6R',
                'model_number' => 'A2991',
                'cost' => 3999.00,
                'purchased_at' => now()->subMonths(8)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(28)->format('Y-m-d'),
                'notes' => 'Assigned to Principal Architect. Configured with FileVault 2 and Jamf MDM.',
                'assigned_to' => $team[0],
            ],
            [
                'asset_tag' => 'AST-1002',
                'name' => 'MacBook Pro 14" M3 Pro (36GB / 1TB)',
                'type' => AssetType::Laptop,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'C02FL981MD4K',
                'model_number' => 'A2992',
                'cost' => 2399.00,
                'purchased_at' => now()->subMonths(6)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(18)->format('Y-m-d'),
                'notes' => 'Primary frontend development workstation.',
                'assigned_to' => $team[1],
            ],
            [
                'asset_tag' => 'AST-1003',
                'name' => 'Lenovo ThinkPad X1 Carbon Gen 11 (32GB / 1TB)',
                'type' => AssetType::Laptop,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'PF-398A71',
                'model_number' => '21HM002EUS',
                'cost' => 2150.00,
                'purchased_at' => now()->subMonths(11)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(13)->format('Y-m-d'),
                'notes' => 'Fedora Workstation dual-boot approved for security audits.',
                'assigned_to' => $team[3],
            ],
            [
                'asset_tag' => 'AST-1004',
                'name' => 'Dell XPS 15 9530 (i9 / 32GB / RTX 4070)',
                'type' => AssetType::Laptop,
                'status' => AssetStatus::Available,
                'serial_number' => '8F7G6H5',
                'model_number' => 'XPS-9530',
                'cost' => 2499.00,
                'purchased_at' => now()->subMonths(3)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(33)->format('Y-m-d'),
                'notes' => 'Clean image reinstalled. Stored in IT Secure Cabinet A-3.',
                'assigned_to' => null,
            ],
            [
                'asset_tag' => 'AST-1005',
                'name' => 'MacBook Air 15" M2 (16GB / 512GB)',
                'type' => AssetType::Laptop,
                'status' => AssetStatus::Available,
                'serial_number' => 'C02E9918MN09',
                'model_number' => 'A2941',
                'cost' => 1499.00,
                'purchased_at' => now()->subMonths(4)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(20)->format('Y-m-d'),
                'notes' => 'Spare pool machine for onboarding or loaner requests.',
                'assigned_to' => null,
            ],
            // Displays & Monitors
            [
                'asset_tag' => 'AST-2001',
                'name' => 'Apple Studio Display 27" 5K (Nano-texture)',
                'type' => AssetType::Monitor,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'F17HK92MN892',
                'model_number' => 'MK0U3LL/A',
                'cost' => 1899.00,
                'purchased_at' => now()->subMonths(10)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(14)->format('Y-m-d'),
                'notes' => 'Desk 4B - Office HQ.',
                'assigned_to' => $team[0],
            ],
            [
                'asset_tag' => 'AST-2002',
                'name' => 'Dell UltraSharp U2723QE 27" 4K USB-C Hub',
                'type' => AssetType::Monitor,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'CN-098KLA-716',
                'model_number' => 'U2723QE',
                'cost' => 620.00,
                'purchased_at' => now()->subMonths(7)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(29)->format('Y-m-d'),
                'notes' => 'Remote home setup shipment.',
                'assigned_to' => $team[1],
            ],
            [
                'asset_tag' => 'AST-2003',
                'name' => 'LG UltraFine 32UN880-B 32" Ergo 4K',
                'type' => AssetType::Monitor,
                'status' => AssetStatus::Available,
                'serial_number' => '204NTXN98124',
                'model_number' => '32UN880',
                'cost' => 599.00,
                'purchased_at' => now()->subMonths(2)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(34)->format('Y-m-d'),
                'notes' => 'Boxed and verified in IT storage room.',
                'assigned_to' => null,
            ],
            // Workstations & Servers
            [
                'asset_tag' => 'AST-3001',
                'name' => 'Mac Studio M2 Ultra (128GB / 4TB)',
                'type' => AssetType::Desktop,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'C02HM018MN78',
                'model_number' => 'A2784',
                'cost' => 4799.00,
                'purchased_at' => now()->subMonths(9)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(27)->format('Y-m-d'),
                'notes' => 'Dedicated local LLM benchmarking & CI simulation node.',
                'assigned_to' => $team[2],
            ],
            [
                'asset_tag' => 'AST-3002',
                'name' => 'Dell PowerEdge R660 1U Rack Server',
                'type' => AssetType::Server,
                'status' => AssetStatus::Maintenance,
                'serial_number' => 'SVC-TAG-8821B',
                'model_number' => 'R660-1U',
                'cost' => 8400.00,
                'purchased_at' => now()->subYears(2)->format('Y-m-d'),
                'warranty_expires_at' => now()->subDays(15)->format('Y-m-d'), // Expired warranty for testing UI badge
                'notes' => 'RAID controller battery fault detected. Replacement unit on order with Dell ProSupport.',
                'assigned_to' => null,
            ],
            // Peripherals & Mobile
            [
                'asset_tag' => 'AST-4001',
                'name' => 'CalDigit TS4 Thunderbolt 4 Dock',
                'type' => AssetType::Peripheral,
                'status' => AssetStatus::Assigned,
                'serial_number' => 'TS4-US-99128',
                'model_number' => 'TS4-08',
                'cost' => 399.00,
                'purchased_at' => now()->subMonths(5)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(19)->format('Y-m-d'),
                'notes' => 'Issued with primary developer kit.',
                'assigned_to' => $team[1],
            ],
            [
                'asset_tag' => 'AST-4002',
                'name' => 'Google Pixel 8 Pro 256GB (Bay)',
                'type' => AssetType::Mobile,
                'status' => AssetStatus::Assigned,
                'serial_number' => '3A191FDH3000K',
                'model_number' => 'GC3VE',
                'cost' => 999.00,
                'purchased_at' => now()->subMonths(6)->format('Y-m-d'),
                'warranty_expires_at' => now()->addMonths(18)->format('Y-m-d'),
                'notes' => 'Mobile QA test bench device.',
                'assigned_to' => $team[2],
            ],
            [
                'asset_tag' => 'AST-5001',
                'name' => 'MacBook Pro 15" Intel Core i7 (2018 Legacy)',
                'type' => AssetType::Laptop,
                'status' => AssetStatus::Retired,
                'serial_number' => 'C02W9919JH89',
                'model_number' => 'A1990',
                'cost' => 2799.00,
                'purchased_at' => now()->subYears(6)->format('Y-m-d'),
                'warranty_expires_at' => now()->subYears(3)->format('Y-m-d'),
                'notes' => 'Decommissioned following end-of-life battery cycle limit. SSD securely sanitized.',
                'assigned_to' => null,
            ],
        ];

        foreach ($hardwareList as $item) {
            $assignedUser = $item['assigned_to'];
            unset($item['assigned_to']);

            $asset = Asset::firstOrCreate(
                ['asset_tag' => $item['asset_tag']],
                $item
            );

            if ($assignedUser) {
                // If there's an assigned user and no active assignment exists, create one
                if (! $asset->currentAssignment()->exists()) {
                    $asset->assignments()->create([
                        'user_id' => $assignedUser->id,
                        'assigned_by' => $admin->id,
                        'assigned_at' => now()->subMonths(rand(1, 6)),
                        'expected_return_at' => now()->addMonths(rand(3, 12))->format('Y-m-d'),
                        'condition_on_assignment' => 'Pristine, newly deployed.',
                        'notes' => 'Standard equipment issue for role.',
                    ]);
                }
            }

            // For AST-1001, create a past returned assignment to show custody history
            if ($item['asset_tag'] === 'AST-1001' && $asset->assignments()->count() === 1) {
                $asset->assignments()->create([
                    'user_id' => $team[2]->id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => now()->subMonths(14),
                    'expected_return_at' => now()->subMonths(9)->format('Y-m-d'),
                    'returned_at' => now()->subMonths(9),
                    'condition_on_assignment' => 'Brand new in box.',
                    'condition_on_return' => 'Minor scuff on lower chassis lid, fully functional.',
                    'notes' => 'Returned when employee upgraded to Mac Studio desktop node.',
                ]);
            }
        }
    }
}
