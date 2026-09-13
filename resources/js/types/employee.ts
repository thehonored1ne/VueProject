import type { Asset, AssetAssignment } from './asset';
import type { SoftwareLicense } from './license';

export interface Employee {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    created_at: string;
    updated_at: string;
    active_assets_count?: number;
    active_licenses_count?: number;
}

export interface EmployeeAssetAssignment extends AssetAssignment {
    asset: Asset;
}

export interface EmployeeLicenseAssignment {
    id: number;
    software_license_id: number;
    user_id: number;
    assigned_at: string;
    notes?: string | null;
    license: SoftwareLicense;
    created_at: string;
    updated_at: string;
}

export interface EmployeeCustodySummary {
    active_hardware_count: number;
    software_seats_count: number;
    lifetime_hardware_count: number;
}

export interface EmployeeMetrics {
    total_employees: number;
    active_custody_employees: number;
    total_assigned_assets: number;
    total_assigned_licenses: number;
}

export interface EmployeeDetail extends Employee {
    active_asset_assignments?: AssetAssignment[];
    license_assignments?: EmployeeLicenseAssignment[];
    asset_assignments?: AssetAssignment[];
}
