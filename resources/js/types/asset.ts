export type AssetStatus = 'available' | 'assigned' | 'maintenance' | 'retired';

export type AssetType = 'laptop' | 'desktop' | 'monitor' | 'mobile' | 'server' | 'peripheral';

export interface UserSummary {
    id: number;
    name: string;
    email: string;
}

export interface AssetAssignment {
    id: number;
    asset_id: number;
    user_id: number;
    assigned_by?: number | null;
    assigned_at: string;
    expected_return_at?: string | null;
    returned_at?: string | null;
    condition_on_assignment?: string | null;
    condition_on_return?: string | null;
    notes?: string | null;
    user?: UserSummary;
    assigned_by_user?: UserSummary;
    created_at: string;
    updated_at: string;
}

export interface Asset {
    id: number;
    asset_tag: string;
    name: string;
    type: AssetType;
    status: AssetStatus;
    serial_number?: string | null;
    model_number?: string | null;
    cost?: string | number | null;
    purchased_at?: string | null;
    warranty_expires_at?: string | null;
    notes?: string | null;
    current_assignment?: AssetAssignment | null;
    assignments?: AssetAssignment[];
    created_at: string;
    updated_at: string;
}

export interface AssetMetrics {
    total: number;
    assigned: number;
    available: number;
    maintenance: number;
    utilization_rate: number;
}

export interface StatusOption {
    value: AssetStatus;
    label: string;
    color: string;
}

export interface TypeOption {
    value: AssetType;
    label: string;
}
