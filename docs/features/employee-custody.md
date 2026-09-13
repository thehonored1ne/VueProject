# Feature Specification: Employee Directory & 360° Custody Portal

This document defines the requirements, architecture, data structures, and workflows for the Employee Directory and 360° Hardware & Software Custody Portal with automated 1-click offboarding.

---

## 1. Feature Overview & Goals

IT Operations and People Operations teams require a consolidated, high-density employee portal to track all company assets entrusted to team members. When team members change roles or depart the organization, IT staff need a single-action offboarding workflow that safely and atomically returns physical hardware and revokes software licenses without manual multi-step reconciliation.

### Key Objectives
- Maintain a searchable employee directory with real-time custody counts (active hardware devices and active software license seats).
- Provide a dedicated 360° Employee Custody detail page showing:
  - Active hardware currently checked out (serial number, asset tag, specs, checkout date, expected return).
  - Active software license seats assigned (vendor, license key, seat assigned date).
  - Historical custody audit log (all past checkouts with check-in timestamps, return condition, and notes).
- Provide an **Atomic 1-Click Offboarding Workflow**:
  - Automatically marks all active hardware assignments as returned.
  - Updates asset operational statuses back to `available`.
  - Records offboarding audit notes.
  - Revokes all software license seat assignments in a single transaction.

---

## 2. Entities & Data Relationships

### Existing Core Models Involved
- `User`: Team member holding custody of assets and software seats.
  - `assetAssignments()`: `HasMany` $\rightarrow$ `AssetAssignment`.
  - `activeAssetAssignments()`: `HasMany` $\rightarrow$ `AssetAssignment` (where `returned_at is null`).
  - `licenseAssignments()`: `HasMany` $\rightarrow$ `LicenseAssignment`.
  - `softwareLicenses()`: `BelongsToMany` $\rightarrow$ `SoftwareLicense` via `license_assignments`.
- `AssetAssignment`: Physical hardware checkout record linking `User` and `Asset`.
- `SoftwareLicense` & `LicenseAssignment`: Software seats allocated to `User`.

---

## 3. Endpoints & Routes

| Method | URI | Controller Action | Description |
|---|---|---|---|
| `GET` | `/employees` | `EmployeeController@index` | Searchable employee directory with custody counts |
| `GET` | `/employees/create` | `EmployeeController@create` | Form to add new employee |
| `POST` | `/employees` | `EmployeeController@store` | Store newly registered employee |
| `GET` | `/employees/{user}` | `EmployeeController@show` | 360° custody portal (active hardware, licenses, history) |
| `GET` | `/employees/{user}/edit` | `EmployeeController@edit` | Form to edit employee profile |
| `PUT` | `/employees/{user}` | `EmployeeController@update` | Update employee profile |
| `DELETE` | `/employees/{user}` | `EmployeeController@destroy` | Delete employee (enforces zero-custody guardrail) |
| `POST` | `/employees/{user}/offboard` | `EmployeeController@offboard` | Atomic 1-click offboarding action |

---

## 4. Deletion & Safety Guardrails
- **Self-Deletion Guard**: Administrators cannot delete their own account.
- **Custody Lock Guard**: Employees currently holding active hardware (`activeAssetAssignments`) or active software licenses (`licenseAssignments`) cannot be deleted directly; the system requires completing the atomic offboarding workflow first to maintain inventory and audit trail integrity.

---

## 5. Atomic Offboarding Workflow

Executed by `App\Actions\Employees\OffboardEmployeeAction` wrapped inside `DB::transaction()`:
1. Find all active `AssetAssignment` records for the user (`whereNull('returned_at')`).
2. For each active assignment:
   - Set `returned_at = now()`.
   - Update `condition_on_return` to 'Returned on employee offboarding'.
   - Append offboarding notes to `notes`.
   - Set associated `Asset` status to `AssetStatus::Available`.
3. Find all `LicenseAssignment` records for the user and delete them, releasing seats back to their respective software licenses.
4. Return an execution summary (`assets_returned`, `licenses_revoked`).

---

## 6. Testing & Quality Assurance
- Guest authorization protection on all `/employees*` routes.
- Directory listing search by name and email.
- Show view rendering with typed props and active relations eager-loaded.
- Atomic offboarding transaction test: confirms assets returned, status updated to `available`, licenses revoked, and transaction integrity.
