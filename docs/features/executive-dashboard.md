# Feature: Executive Operations & Fleet Analytics Dashboard (`feat/executive-dashboard`)

## 1. Overview
The Executive Operations & Fleet Analytics Dashboard replaces the placeholder `/dashboard` with an IT operations control center. It delivers real-time visibility into capital equipment valuation, fleet hardware utilization, software seat saturation, immediate critical attention queues, and a unified operational audit stream.

---

## 2. Business Requirements

### 2.1 Executive KPIs & Capital Metrics
- **Total Fleet Valuation**:
  - Total replacement / purchase cost of physical hardware (`SUM(assets.cost)`).
  - Estimated annualized software licensing commitment based on seat cost and billing cycle.
- **Hardware Fleet Utilization**:
  - Breakdown of physical assets by status: `Available`, `Assigned`, `Maintenance`, `Retired`.
  - Live deployment utilization rate (`assigned / total * 100`).
- **Software Seat Saturation**:
  - Aggregated seat metrics: Total purchased seats, actively allocated seats, and available seats.
  - License allocation efficiency rate (`allocated / total * 100`).
- **Active Operational Incidents & Alerts**:
  - Count of hardware currently under active maintenance/repair.
  - Count of equipment warranties expiring within 30 days or overdue.
  - Count of software contracts expiring within 30 days.

### 2.2 Urgent Action Center (Immediate Attention Queue)
A prioritized triage board for IT administrators:
- **Overdue Hardware Returns**: Custody assignments where `expected_return_at < today` and `returned_at is null`.
- **Expiring Warranties**: Hardware assets with warranty expiring within 30 days or already expired.
- **Expiring Software Licenses**: Licenses with contract expiration within 30 days.
- **In-Progress Repairs**: Ongoing equipment maintenance jobs requiring follow-up.
- Direct 1-click links to resolve or inspect each item.

### 2.3 Hardware Category Distribution
- Visual distribution of inventory by hardware type (`Laptop`, `Desktop`, `Monitor`, `Mobile Device`, `Peripheral`, `Server`, `Other`).
- Total units and deployed units per category with proportional progress bars.

### 2.4 Unified Activity Audit Stream
- Chronological timeline combining:
  - Hardware checkouts and check-ins (`AssetAssignment`).
  - Repair job submissions and resolutions (`AssetMaintenance`).
  - Software seat allocations and revocations (`LicenseAssignment`).
- Displays timestamps, acting technician, recipient employee, and related entity tags.

### 2.5 Quick Launch Actions
- Fast navigation to key workflows:
  - "Check Out Hardware" -> Jump to `/assets`
  - "Register Asset" -> `/assets/create`
  - "Add Software License" -> `/licenses/create`
  - "Log Repair Ticket" -> `/maintenance`
  - "Export Inventory CSV" -> Stream download from `/assets/export`

---

## 3. Architecture & Components

### Backend Layer (Laravel 12)
- **Domain Layer**:
  - `app/Actions/Dashboard/GetDashboardMetricsAction.php`: Single-action query class that aggregates database metrics using SARGable, non-blocking indexed queries and eager loading.
- **HTTP Layer**:
  - `app/Http/Controllers/DashboardController.php`: Invokable thin controller delegating to `GetDashboardMetricsAction` and returning `Inertia::render('Dashboard', ...)`.
- **Routes**:
  - `routes/web.php`: Route `dashboard` bound to `DashboardController`.

### Frontend Layer (Vue 3 + Inertia + TypeScript)
- **Types**:
  - `resources/js/types/index.d.ts`: Interfaces for `DashboardMetrics`, `DashboardCategoryStat`, `DashboardUrgentItem`, and `DashboardActivity`.
- **Views**:
  - `resources/js/pages/Dashboard.vue`: Anti-Slop UI layout with high-density KPI cards, interactive urgent queue, category distribution bars, and activity timeline.

---

## 4. Verification Gate
- **Pest PHP Tests** (`tests/Feature/DashboardTest.php`):
  - Guest authentication protection.
  - Valuation and metric calculation correctness.
  - Urgent queue detection (overdue assignments, warranty expiration).
  - Activity stream ordering.
- **Static Quality**:
  - Laravel Pint (`./vendor/bin/pint --test`)
  - ESLint (`npm run lint`)
  - Prettier (`npm run format:check`)
  - TypeScript (`npx vue-tsc --noEmit`)
