# Documentation & Context Source of Truth (`docs/`)

The `docs/` directory is an Obsidian vault and serves as the **Single Source of Truth (SSOT)** for project context, domain models, architectural decisions, and functional requirements.

---

## 1. Pulling Context (Consult Before Building)

- **Mandatory Pre-Task Context Pull**:
  - Before designing features, planning architectural changes, or modifying domain logic, the agent **must** search and read relevant documentation inside `docs/`.
  - Ground all implementations in existing domain specifications, data models, and business rules documented in the vault.
  - If a discrepancy exists between assumptions and `docs/`, `docs/` takes precedence unless explicitly instructed otherwise by the user.

---

## 2. Updating Context (Synchronize After Building)

- **Keep Documentation Synchronized**:
  - Whenever a new feature, domain model, action, API route, or architectural decision is introduced or refactored, the agent **must update or create corresponding documentation in `docs/`**.
  - Document:
    - Feature overviews and user flows
    - Model schemas, relationships, and business constraints
    - Architectural decisions (ADRs - Architecture Decision Records)
    - Key configuration changes or external service integrations
- **Preserve Obsidian Vault Integrity**:
  - Format notes using standard GitHub Flavored Markdown and Obsidian-compatible syntax (Wikilinks `[[Note Name]]` or relative markdown links).
  - Do NOT modify or corrupt files in `docs/.obsidian/`.

---

## 3. Recommended Structure for `docs/`

Organize notes cleanly within `docs/`:
- `docs/architecture/`: System design, layered architecture notes, decision records.
- `docs/domain/`: Entity definitions, domain rules, business workflows.
- `docs/features/`: Feature specifications, user stories, implementation guides.
- `docs/api/`: Endpoint definitions, payload contracts, and integration specs.
