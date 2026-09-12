# Feature Specification: Software Seat & License Management

This document defines the requirements, architecture, data structures, and workflows for tracking software licenses and seat allocations within AssetFlow.

---

## 1. Feature Overview & Goals

IT departments and engineering managers need to manage third-party software subscriptions (e.g., GitHub Copilot, Figma, JetBrains, 1Password, Slack), allocate individual seats to team members, prevent seat overallocation, and monitor renewal/expiration dates.

### Key Objectives
- Maintain a registry of purchased software subscriptions, seat caps, costs, and vendors.
- Assign licenses to team members with one-click revocation.
- Enforce seat limits (prevent assigning beyond `seats_total`).
- Provide utilization metrics and expiration warnings for proactive renewals.

---

## 2. Entities & Data Model

### `SoftwareLicense`
- `id`: Auto-incrementing primary key.
- `name`: Name of software (e.g., `GitHub Copilot Business`).
- `vendor`: Vendor or publisher (e.g., `GitHub / Microsoft`).
- `license_key`: Optional activation key or subscription ID.
- `seats_total`: Total seats purchased.
- `cost_per_seat`: Decimal(10,2) cost per seat.
- `billing_cycle`: Enum (`monthly`, `yearly`, `perpetual`).
- `expires_at`: Renewal / expiration date (nullable).
- `notes`: Markdown operational notes.
- **Relationships**:
  - `assignments()`: `HasMany` $\rightarrow$ `LicenseAssignment`.
  - `users()`: `BelongsToMany` $\rightarrow$ `User` via `LicenseAssignment`.

### `LicenseAssignment`
- `id`: Auto-incrementing primary key.
- `software_license_id`: Foreign key $\rightarrow$ `software_licenses.id` (`cascadeOnDelete`).
- `user_id`: Foreign key $\rightarrow$ `users.id` (`cascadeOnDelete`).
- `assigned_at`: Timestamp.
- `notes`: Optional note (e.g., project justification).
- **Constraints**: Unique index on `[software_license_id, user_id]`.

---

## 3. Endpoints & Routes

| Method | URI | Controller Action | Description |
|---|---|---|---|
| `GET` | `/licenses` | `LicenseController@index` | License catalog & seat utilization metrics |
| `GET` | `/licenses/create` | `LicenseController@create` | License creation form |
| `POST` | `/licenses` | `LicenseController@store` | Store new license |
| `GET` | `/licenses/{license}` | `LicenseController@show` | License details & active seat holders |
| `GET` | `/licenses/{license}/edit` | `LicenseController@edit` | Edit license |
| `PUT` | `/licenses/{license}` | `LicenseController@update` | Update license |
| `DELETE` | `/licenses/{license}` | `LicenseController@destroy` | Delete license |
| `POST` | `/licenses/{license}/assign` | `LicenseAssignmentController@store` | Allocate seat to employee |
| `DELETE` | `/licenses/{license}/revoke/{user}` | `LicenseAssignmentController@destroy` | Revoke employee seat |

---

## 4. Testing Strategy
- Guest route protection tests.
- Form validation: required name, valid seats integer ($> 0$), billing cycle enums.
- Seat allocation limit: attempting to assign seat when `assigned_count >= seats_total` rejects with validation error.
- Unique assignee: attempting to assign the same license to the same user twice rejects.
- Seat revocation: successfully removes the seat assignment and frees up the seat.
