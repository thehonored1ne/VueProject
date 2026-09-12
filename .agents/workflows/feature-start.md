---
description: Interactive workflow to sync main, create a standardized feature/bugfix branch, and initialize a spec note in docs/features/.
---

# Feature Branch Creator (`/feature-start`)

This workflow prepares the local repository, creates a cleanly named branch from the latest `main`, and initializes context in the `docs/` Obsidian vault.

---

## Steps

### Step 1: Verify Working Tree Cleanliness
Ensure there are no unstaged or dirty changes:
```bash
git status -s
```
If dirty changes exist, ask the user to commit or stash them before proceeding.

### Step 2: Switch to and Sync `dev`
Ensure you are branching from the freshest development state:
```bash
git checkout dev
git pull origin dev
```

### Step 3: Branch Naming
Determine branch type and kebab-case descriptor:
- `feat/<feature-slug>` for new functionality (e.g. `feat/order-export-csv`)
- `fix/<fix-slug>` for bug and deprecation fixes (e.g. `fix/session-expiry-modal`)
- `refactor/<refactor-slug>` for code restructuring
- `perf/<perf-slug>` for query and performance tuning

### Step 4: Create and Checkout Branch
Create and switch to the new branch:
```bash
git checkout -b <branch-name>
```

### Step 5: Initialize Context in `docs/features/`
Create a starter note in `docs/features/<feature-slug>.md` documenting:
- Feature goal and user requirements
- Affected components, actions, and models
- Testing strategy
