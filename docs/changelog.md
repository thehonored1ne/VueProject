# Changelog

All notable changes to **AssetFlow** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] - `feat/maintenance-alerts`

### Added
- **Hardware Maintenance Operations Center**:
  - Centralized dashboard at `/maintenance` with tabs for Active Repairs, Hardware Warranty Alerts, Software Renewals, and Repair History.
  - High-density KPI cards tracking Active Repairs, Expiring Warranties (≤ 60d), Software Renewals (≤ 60d), and Lifetime Repair Spend.
- **Repair Lifecycle Tracking & Automation**:
  - `AssetMaintenance` model with statuses: `scheduled`, `in_progress`, `completed`, and `cancelled`.
  - Single-action classes:
    - `CreateMaintenanceAction`: Dispatches repair tickets inside database transactions and automatically transitions asset status to `maintenance`.
    - `CompleteMaintenanceAction`: Records resolution notes, final cost, completion timestamp, and restores asset status to `available`.
    - `CancelMaintenanceAction`: Cancels repair tickets and restores asset status to `available`.
- **Proactive Expiration & Renewal Alerts**:
  - Warranty expiration detection flagging equipment expiring within 60 days or past warranty with urgency badges (`Expired`, `Urgent`, `Upcoming`).
  - Software license renewal alerts highlighting upcoming subscription dates within 60 days with active seat count allocation stats.
- **UI Integrations**:
  - "Log Repair" quick action button on `Assets/Show.vue` with active repair warning banner.
  - `Maintenance` navigation item with `Wrench` icon added to `AppSidebar.vue`.
  - Modal dialogs for logging repairs (`MaintenanceTicketModal.vue`) and resolving repairs (`CompleteMaintenanceModal.vue`).
- **Feature Documentation**:
  - Comprehensive feature spec in `[[features/maintenance-alerts|Hardware Maintenance & Expiration Alerts]]`.
- **Testing**:
  - 5 Pest PHP feature tests (`tests/Feature/MaintenanceTest.php`) verifying guest access restrictions, dashboard metrics, and automatic asset lifecycle transitions.

---

## [0.3.0] - 2026-09-13 - `feat/asset-qr-export`

### Added
- **Asset Identification QR Code Badges**:
  - Client-side vector QR code rendering using `qrcode` library for zero-latency label generation.
  - Scannable 256px QR codes encoding unique asset detail URLs.
  - Individual hardware badge modal (`AssetQrModal.vue`) featuring company branding, monospace asset tag, model, serial number, and 1-click clipboard URL copying.
  - Dedicated thermal printer and standard paper `@media print` CSS isolating sticker labels.
- **Batch Printable Sticker Sheets**:
  - Multi-label printable sticker sheet (`BatchQrPrintModal.vue`) formatting all filtered assets into a clean print grid with `break-inside: avoid` page breaks.
- **Streamed Inventory CSV Export**:
  - Memory-safe chunked CSV export (`ExportAssetsCsvAction`) utilizing `chunkById(200)` and `response()->streamDownload` to support arbitrary inventory sizes with constant O(1) memory consumption.
  - SARGable query filtering preserving active search keywords, asset status, and hardware category.
  - UTF-8 BOM encoding for seamless Microsoft Excel and Google Sheets compatibility.
- **UI Controls**:
  - "Export CSV" and "Print Labels" action buttons added to the `Assets/Index.vue` toolbar.
  - "QR Label" action button added to `Assets/Show.vue` header.
- **Documentation**:
  - Technical specification in `[[features/asset-qr-export|Asset QR Labeling & Data Export]]`.
- **Testing**:
  - 5 Pest PHP feature tests (`tests/Feature/AssetExportTest.php`) validating HTTP headers, streamed CSV payload integrity, assignee data, and query filters.

---

## [0.2.0] - 2026-09-13 - `feat/software-licenses`

### Added
- **Software Seat & License Management**:
  - `License` and `LicenseAssignment` domain models for tracking enterprise software subscriptions, vendors, license keys, purchase/expiration dates, and per-seat costs.
  - Seat overallocation guard (`AssignSeatAction`) preventing assignments beyond purchased seat limits with strict database-level locks.
  - Assignment revocation workflow (`RevokeSeatAction`) freeing up seats in real time.
- **Licenses Operations Center**:
  - `/licenses` index page featuring high-density KPI metrics (Total Seats, Allocated Seats, Available Seats, and Active Software Subscriptions).
  - Search, vendor filtering, and detailed license inspection (`Licenses/Show.vue`) displaying active assignee seat tables.
  - "Assign Seat" modal (`AssignSeatModal.vue`) and seat revocation with confirmation.
- **Navigation**:
  - Added `Licenses` navigation item with `KeyRound` icon to `AppSidebar.vue`.
- **Documentation**:
  - Feature specification in `[[features/software-licenses|Software Seat Management]]` and domain note in `[[domain/licenses|Software Licenses & Seats]]`.
- **Testing**:
  - 15 Pest PHP feature tests (`LicenseTest.php`, `LicenseAssignmentTest.php`) verifying CRUD, overallocation prevention, duplicate assignment blocking, and cascade deletion guards.

---

## [0.1.1] - 2026-09-12 - Core Navigation & Branding Polish

### Changed
- Replaced default Laravel starter kit branding with **AssetFlow** across sidebar, navigation headers, and metadata.
- Streamlined `AppSidebar.vue` by removing external starter kit links and placeholder items.

### Fixed
- Fixed `NavMain.vue` route highlighting to accurately support subpath matching and exact Inertia navigation URLs.
- Added case-insensitive `page_paths` configuration in `config/inertia.php` to resolve Linux CI path resolution discrepancies.

---

## [0.1.0] - 2026-09-12 - Initial AssetFlow MVP

### Added
- **Core IT Asset Management**:
  - `Asset` and `AssetAssignment` domain models with status lifecycle (`available`, `assigned`, `maintenance`, `retired`).
  - Single-action classes: `CheckOutAssetAction` (assign hardware to employee) and `CheckInAssetAction` (return hardware to available pool).
  - Full custody timeline audit logging preserving checkout timestamps, assignees, and return notes.
- **Frontend Pages & Components**:
  - Asset inventory index (`Assets/Index.vue`) with live search, status filtering, category filtering, and status badges.
  - Detail view (`Assets/Show.vue`) displaying hardware specs, financial purchase records, warranty tracking, and chronological custody history.
  - Create and Edit forms (`Assets/Create.vue`, `Assets/Edit.vue`) with strict form request validation.
  - Check-out (`AssignAssetModal.vue`) and Check-in (`CheckInAssetModal.vue`) modal workflows.
- **Documentation & CI/CD**:
  - Initialized Obsidian context vault (`docs/`) as the project Single Source of Truth (SSOT).
  - Configured GitHub Actions CI pipeline running Pest PHP, Laravel Pint, ESLint, Prettier, TypeScript checks, and Vite production builds.
  - Standardized Antigravity developer workflows (`/dev`, `/verify`, `/git-commit`, `/git-pr`, `/feature-start`).
