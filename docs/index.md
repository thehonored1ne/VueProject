# Project Knowledge Base & Context Hub

Welcome to the project's **Single Source of Truth (SSOT)**. This Obsidian vault documents the domain specifications, architectural decisions, data models, and feature guides for the application.

---

## 🧭 Vault Directory Map

- **[[architecture/index|Architecture]]**: System design, clean layered architecture, technology stack, and Architecture Decision Records (ADRs).
  - **[[architecture/github-workflow|GitHub Developer Workflow]]**: End-to-end GitHub collaboration guide (Branch -> PR -> CI -> Merge).
  - **[[architecture/dev-workflow|Local Dev Workflow]]**: Environment booting, live log streaming, testing, and daily dev loop.
  - **[[architecture/git-workflows|Git & CI/CD Workflows]]**: Branching standards, Antigravity workflows, and GitHub Actions CI.
- **[[domain/index|Domain Models]]**: Core business entities, Eloquent relationships, state machines, and business rules.
  - **[[domain/assets|Assets & Custody Assignments]]**: Hardware registry, lifecycle states, and assignment models.
  - **[[domain/licenses|Software Licenses & Seats]]**: Subscriptions, seat allocations, and renewal lifecycles.
- **[[features/index|Features & Flows]]**: Functional specifications, user stories, and Inertia Vue 3 screen workflows.
  - **[[features/executive-dashboard|Executive Operations & Analytics Dashboard]]**: IT operations command center, fleet valuation, utilization rates, and urgent queues.
  - **[[features/asset-management|AssetFlow Management]]**: Inventory dashboard, check-out/check-in, warranty tracking, and audit trails.
  - **[[features/software-licenses|Software Seat Management]]**: Seat allocations, overallocation prevention, and license renewals.
  - **[[features/asset-qr-export|Asset QR Labeling & Data Export]]**: Printable hardware label badges, thermal stickers, and streamed CSV exports.
  - **[[features/maintenance-alerts|Hardware Maintenance & Expiration Alerts]]**: Equipment repairs, service logging, and warranty/renewal alerts.
- **[[api/index|API & Routing]]**: Route endpoints, form request contracts, DTO shapes, and external integrations.
- **[[changelog|Project Changelog]]**: Release history, milestones, and unreleased feature developments.

---

## 📝 Rules for AI Agents & Developers

1. **Pull Before Modifying**: Review relevant notes here before designing or building features.
2. **Synchronize After Modifying**: Update or create notes whenever features, models, or architectural patterns are added or modified.
3. **Format**: Use standard Markdown with Obsidian Wikilinks (`[[Note Name]]`) or standard Markdown links.
