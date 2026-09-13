export interface DashboardMetrics {
    total_asset_count: number;
    total_hardware_value: number;
    annual_software_spend: number;
    total_fleet_valuation: number;
    asset_status_counts: {
        available: number;
        assigned: number;
        maintenance: number;
        retired: number;
    };
    hardware_utilization_rate: number;
    total_licenses: number;
    total_software_seats: number;
    allocated_software_seats: number;
    available_software_seats: number;
    seat_utilization_rate: number;
    active_repairs_count: number;
    total_repair_spend: number;
    overdue_returns_count: number;
    expiring_warranties_count: number;
    expiring_licenses_count: number;
    urgent_total_count: number;
}

export interface DashboardCategoryStat {
    type: string;
    label: string;
    total: number;
    deployed: number;
    percentage: number;
    deployment_rate: number;
}

export interface DashboardUrgentItem {
    id: string;
    type: 'overdue_return' | 'warranty_expiring' | 'license_expiring' | 'active_repair';
    title: string;
    subtitle: string;
    date_label: string;
    date: string;
    urgency: 'critical' | 'warning' | 'info';
    action_url: string;
    action_label: string;
}

export interface DashboardActivity {
    id: string;
    type: 'asset_checkout' | 'asset_checkin' | 'repair_logged' | 'repair_completed' | 'license_allocated';
    title: string;
    description: string;
    actor: string;
    target: string;
    timestamp: string;
    time_ago: string;
    url: string;
}
