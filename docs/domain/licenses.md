# Domain Model: Software Licenses & Seat Allocations

This document defines the core business rules, entity models, and relationships for the **Software Seat & License Management** domain.

---

## 1. Entities & Relationships

### `SoftwareLicense`
Represents an organization's subscription, software contract, or perpetual license key.

- **Attributes**:
  - `id`: Auto-incrementing primary key.
  - `name`: Human-readable name of software or subscription tier (e.g. `GitHub Copilot Business`).
  - `vendor`: Publisher or vendor (e.g. `GitHub / Microsoft`, `JetBrains`, `Figma`).
  - `license_key`: Optional activation key or subscription identifier.
  - `seats_total`: Total seats purchased / permitted.
  - `cost_per_seat`: Decimal(10,2) cost per seat.
  - `billing_cycle`: Enum (`BillingCycle`: `monthly`, `yearly`, `perpetual`).
  - `expires_at`: Renewal / expiration date (nullable).
  - `notes`: Markdown operational notes.
  - `timestamps`.

- **Relationships**:
  - `assignments()`: `HasMany` $\rightarrow$ `LicenseAssignment` (Ordered by `assigned_at` descending).
  - `users()`: `BelongsToMany` $\rightarrow$ `User` via `LicenseAssignment`.

---

### `LicenseAssignment`
Represents the allocation of a single seat to a specific employee.

- **Attributes**:
  - `id`: Auto-incrementing primary key.
  - `software_license_id`: Foreign key $\rightarrow$ `software_licenses.id` (`cascadeOnDelete`).
  - `user_id`: Foreign key $\rightarrow$ `users.id` (`cascadeOnDelete`).
  - `assigned_at`: Datetime when seat was allocated.
  - `notes`: Optional justification notes.
  - `timestamps`.

- **Invariants**:
  - A user **cannot** be assigned more than one seat for the same software license (enforced by a database unique constraint `[software_license_id, user_id]`).
  - New assignments **cannot** be created if the license's allocated seats equal or exceed `seats_total`.
  - Revoking a seat immediately deletes the `LicenseAssignment` record and frees the seat.
