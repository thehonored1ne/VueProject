# Feature: Hardware Maintenance & Expiration Alerts (`feat/maintenance-alerts`)

## 1. Overview
The Maintenance & Expiration Alerts feature provides proactive IT fleet maintenance and contract monitoring by tracking equipment repairs, servicing costs, and automated alerts for expiring hardware warranties and software licenses.

---

## 2. Business Requirements

### Equipment Maintenance & Repair Logs
- **Log Service & Repair**:
  - Record maintenance tickets for assets: issue summary, technician / service vendor, cost, started at, completed at, and repair notes.
  - When an asset enters maintenance, its status automatically switches to `maintenance`.
  - When a maintenance ticket is completed/resolved, the asset status transitions back to `available`.
- **Maintenance Audit History**:
  - Full repair log visible on the asset detail view with total repair costs accumulated.

### Warranty & License Expiration Alerts
- **Proactive Expiration Thresholds**:
  - `Critical`: Expired already.
  - `Warning`: Expiring within 30 days.
  - `Upcoming`: Expiring within 60 days.
- **Dedicated Alerts & Maintenance Center**:
  - Centralized `/maintenance` view displaying:
    - Active / In-progress repair jobs.
    - Assets with expiring warranties.
    - Software licenses with expiring contracts.
  - Quick action: "Schedule Repair", "Resolve Ticket", and "Review License Renewal".

---

## 3. Architecture & Affected Components

### Backend Layer (Laravel 12)
- **Domain Enums**:
  - `MaintenanceStatus`: `scheduled`, `in_progress`, `completed`, `cancelled`.
- **Persistence Layer**:
  - Table: `asset_maintenances` (`asset_id`, `user_id`, `title`, `provider`, `cost`, `status`, `scheduled_for`, `started_at`, `completed_at`, `notes`).
  - Model: `AssetMaintenance`.
- **Single-Action Classes**:
  - `CreateMaintenanceAction`: Creates ticket and transitions asset to `maintenance`.
  - `CompleteMaintenanceAction`: Resolves ticket, records actual cost/completion date, and transitions asset back to `available`.
- **HTTP Layer**:
  - `MaintenanceController`: Index, create, store, and complete repair tickets.
  - Alerts query aggregation for dashboard & maintenance hub.

### Frontend Layer (Vue 3 + Inertia + TypeScript)
- **Components**:
  - `MaintenanceTicketModal.vue`: Modal to schedule or log repairs for an asset.
  - `CompleteMaintenanceModal.vue`: Modal to resolve repair with final notes and cost.
  - `ExpirationAlertBanner.vue`: High-density banner highlighting expired or soon-to-expire contracts.
- **Pages**:
  - `resources/js/pages/Maintenance/Index.vue`: Tabbed hub for Active Repairs, Expiring Warranties, and Expiring Licenses.
  - Navigation: Add `Maintenance` link to `AppSidebar.vue`.

---

## 4. Verification Gate
- Pest PHP feature tests covering repair ticket lifecycles, state transitions, and alert query scopes.
- Pint style check, ESLint, Prettier, TypeScript, and Vite production build.
