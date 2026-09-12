# Feature Specification: AssetFlow Management

This document defines the user workflows, routes, components, and permissions for the AssetFlow feature.

---

## 1. User Workflows

### 1.1 Asset Registry & Inventory
- IT administrators and engineers can view all hardware assets in a high-density, filterable data table.
- Filter assets by **Status** (`Available`, `Assigned`, `Maintenance`, `Retired`) and **Category** (`Laptop`, `Desktop`, `Monitor`, etc.).
- Search instantly by `name`, `asset_tag`, or `serial_number`.
- Summary metric tiles at the top highlight:
  - Total Assets
  - Active Deployed Count (Utilization %)
  - In Stock (Available for deployment)
  - Under Maintenance

### 1.2 Check Out (Assignment)
- An administrator clicks "Check Out" on any `available` asset.
- A modal opens allowing the selection of:
  - Target Employee (`user_id`)
  - Expected return date (`expected_return_at`)
  - Outgoing hardware condition notes (`condition_on_assignment`)
- On submission, `AssignAssetAction` records the custody assignment and flips the asset status to `assigned`.

### 1.3 Check In (Return)
- On an `assigned` asset, an administrator clicks "Check In".
- A modal opens requesting:
  - Received condition (`condition_on_return`)
  - Return notes
  - Destination status: `Available` (ready for re-issuance) or `Maintenance` (needs diagnostics/cleaning).
- On submission, `CheckInAssetAction` closes the assignment and updates the asset status.

### 1.4 Asset Detail & Custody Timeline
- Clicking an asset navigates to `/assets/{asset}`.
- Displays hardware specs, purchase & warranty metrics (flagging warranties expiring within 30 days), and current custody status.
- Shows a chronological vertical audit trail of every past assignment and condition report.

---

## 2. Route Endpoints

| HTTP Method | URI | Controller Action | Description |
|---|---|---|---|
| `GET` | `/assets` | `AssetController@index` | Asset inventory dashboard & metrics |
| `GET` | `/assets/create` | `AssetController@create` | Asset creation form |
| `POST` | `/assets` | `AssetController@store` | Store new asset |
| `GET` | `/assets/{asset}` | `AssetController@show` | Asset details & custody timeline |
| `GET` | `/assets/{asset}/edit` | `AssetController@edit` | Edit asset details |
| `PUT`/`PATCH` | `/assets/{asset}` | `AssetController@update` | Update asset details |
| `DELETE` | `/assets/{asset}` | `AssetController@destroy` | Delete asset |
| `POST` | `/assets/{asset}/assign` | `AssetAssignmentController@store` | Check out asset to employee |
| `POST` | `/assets/{asset}/check-in` | `AssetAssignmentController@update` | Check in / return asset |
