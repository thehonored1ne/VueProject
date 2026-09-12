import type { Asset, UserSummary } from './asset';

export type MaintenanceStatus = 'scheduled' | 'in_progress' | 'completed' | 'cancelled';

export interface AssetMaintenance {
    id: number;
    asset_id: number;
    user_id?: number | null;
    title: string;
    provider: string;
    cost?: string | number | null;
    status: MaintenanceStatus;
    scheduled_at?: string | null;
    started_at?: string | null;
    completed_at?: string | null;
    notes?: string | null;
    asset?: Asset;
    technician?: UserSummary | null;
    created_at: string;
    updated_at: string;
}

export interface WarrantyAlertItem {
    id: number;
    asset_tag: string;
    name: string;
    type: string;
    status: string;
    model_number?: string | null;
    serial_number?: string | null;
    warranty_expires_at?: string | null;
    days_until_expiration: number | null;
    is_expired: boolean;
    assignee?: string | null;
}

export interface LicenseAlertItem {
    id: number;
    name: string;
    vendor: string;
    seats_total: number;
    seats_assigned: number;
    billing_cycle: string;
    cost_per_seat?: string | number | null;
    expires_at?: string | null;
    days_until_expiration: number | null;
    is_expired: boolean;
}

export interface MaintenanceMetrics {
    active_repairs: number;
    expiring_warranties: number;
    expiring_licenses: number;
    total_repair_costs: number;
}
