# Feature: Asset QR Code Labeling & Inventory Export (`feat/asset-qr-export`)

## 1. Overview
The Asset QR Code Labeling & Inventory Export feature provides physical inventory management capabilities by generating printable, scannable QR code label badges for hardware assets and enabling structured CSV data export for audits and compliance.

---

## 2. Business Requirements

### QR Code & Label Generation
- **Individual Asset Label**:
  - Each asset generates an SVG/canvas QR code pointing directly to its asset detail URL (`/assets/{asset}`).
  - Printable badge layout displays: Company name/logo, Asset Tag (`AST-XXXXXX`), Model Name, Serial Number, and QR code.
  - Dedicated print-ready modal with responsive print CSS (hides web UI, optimizes for thermal sticker labels and paper printing).
- **Batch Print Sheet**:
  - Bulk printable sheet for selected assets or all filtered assets formatted into standard grid sticker sheets (e.g., Avery 2x4 labels).

### Data Export (CSV)
- **Filtered Export**:
  - Export filtered inventory list directly into CSV format.
  - CSV columns: Asset Tag, Name, Type, Model, Serial Number, Status, Cost, Assigned User, Purchase Date, Warranty Expiration.
  - Streamed response to prevent memory exhaustion on large datasets.

---

## 3. Architecture & Affected Components

### Backend Layer (Laravel 12)
- **Single-Action Classes**:
  - `app/Actions/Assets/ExportAssetsCsvAction.php`: Streams SARGable query results as a CSV download.
- **HTTP Layer**:
  - `app/Http/Controllers/AssetExportController.php`: Handles CSV generation requests with active query filters.
- **Routes**:
  - `GET /assets/export`: CSV download with search/status/type filters.

### Frontend Layer (Vue 3 + Inertia + TypeScript)
- **Components**:
  - `resources/js/components/assets/AssetQrModal.vue`: Modal displaying scannable QR badge with 1-click browser print trigger.
  - `resources/js/components/assets/BatchPrintSheetModal.vue`: Grid sheet preview with print styles.
- **Pages**:
  - `resources/js/pages/Assets/Index.vue`: Add "Export CSV" and "Print Labels" action buttons to the inventory toolbar.
  - `resources/js/pages/Assets/Show.vue`: Add "Print QR Label" quick action button next to asset tag.

---

## 4. Testing & Verification
- `tests/Feature/AssetExportTest.php`:
  - Guests redirected to login.
  - Authenticated users receive valid CSV headers (`text/csv`, `Content-Disposition`).
  - Active search and status filters apply correctly to exported records.
- Verification Gate:
  - `php artisan test`
  - `./vendor/bin/pint --test`
  - `npm run format:check`
  - `npm run lint`
  - `npm run build`
