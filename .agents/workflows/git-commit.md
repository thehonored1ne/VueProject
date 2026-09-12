---
description: Clean Git commit workflow with automated code formatting, secret leak prevention, and conventional commit drafting.
---

# Clean Git Commit Workflow (`/git-commit`)

This workflow prepares, sanitizes, formats, and stages code changes, then crafts a descriptive conventional commit.

---

## Steps

### Step 1: Inspect Working Tree & Status
Inspect the current status of all tracked and untracked files:
```bash
git status -s
```

### Step 2: Secret & Environment Safety Gate (CRITICAL)
Verify that no sensitive or private files are accidentally staged:
- [ ] Ensure `.env`, `.env.local`, `.env.*` are **NEVER** staged.
- [ ] Ensure private keys (`.pem`, `.key`), certificates, or sqlite test databases are not tracked.
- If any secret is accidentally staged, unstage immediately:
  ```bash
  git restore --staged <file>
  ```

### Step 3: Pre-Commit Code Formatting
Auto-format modified files before staging so git history remains clean:
1. Format PHP files:
   ```bash
   ./vendor/bin/pint
   ```
2. Format frontend files:
   ```bash
   npm run format
   ```

### Step 4: Stage Intended Changes
Stage specific modified files rather than a blanket `git add -A`:
```bash
git add <file1> <file2> ...
```

### Step 5: Draft Conventional Commit Message
Analyze `git diff --staged` and draft a concise, conventional commit message following the format:
- `feat(<scope>): <description>` for new capabilities
- `fix(<scope>): <description>` for bug or deprecation fixes
- `refactor(<scope>): <description>` for architectural or code improvements
- `style(<scope>): <description>` for formatting or UI polish
- `test(<scope>): <description>` for adding or updating tests
- `chore(<scope>): <description>` for config, dependency, or agent customization updates

### Step 6: Present Proposal & Commit
Show the staged diff summary and proposed commit message to the user for confirmation before executing `git commit`.
