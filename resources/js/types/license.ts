import type { UserSummary } from './asset';

export type BillingCycle = 'monthly' | 'yearly' | 'perpetual';

export interface LicenseAssignment {
    id: number;
    software_license_id: number;
    user_id: number;
    assigned_at: string;
    notes?: string | null;
    user?: UserSummary;
    created_at: string;
    updated_at: string;
}

export interface SoftwareLicense {
    id: number;
    name: string;
    vendor: string;
    license_key?: string | null;
    seats_total: number;
    cost_per_seat?: string | number | null;
    billing_cycle: BillingCycle;
    expires_at?: string | null;
    notes?: string | null;
    assignments_count?: number;
    assignments?: LicenseAssignment[];
    created_at: string;
    updated_at: string;
}

export interface LicenseMetrics {
    total_licenses: number;
    total_seats: number;
    allocated_seats: number;
    available_seats: number;
    utilization_rate: number;
    expiring_count: number;
}

export interface BillingCycleOption {
    value: BillingCycle;
    label: string;
}
