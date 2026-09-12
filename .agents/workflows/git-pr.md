---
description: Automated PR workflow to run full verification, push upstream, assemble PR template with test proof, and open PR via GitHub CLI.
---

# Automated Pull Request Workflow (`/git-pr`)

This workflow executes full verification checks, pushes the current branch to GitHub, populates `.github/pull_request_template.md`, and opens a pull request via GitHub CLI (`gh`).

---

## Steps

### Step 1: Pre-Flight Verification Gate
Run the full verification suite before attempting to push or open a PR:
1. Pest PHP tests: `php artisan test` (0 failures required).
2. Pint style check: `./vendor/bin/pint --test`.
3. ESLint check: `npm run lint`.
4. Prettier check: `npm run format:check`.
5. TypeScript check: `npx vue-tsc --noEmit`.

### Step 2: Push Current Branch to GitHub
Push the branch to `origin` with upstream tracking:
```bash
git push -u origin HEAD
```

### Step 3: Gather Commit History & PR Context
Inspect the commit log between `main` and the current branch:
```bash
git log main..HEAD --oneline
```
Extract key changes, affected files, and context from `docs/features/`.

### Step 4: Assemble PR Description
Format the PR description using `.github/pull_request_template.md`:
- **Title**: Matches conventional commit format (`feat(orders): add CSV export action`).
- **What Was Built / Changed**: Concise bullet points.
- **Verification Scorecard**: Clean summary table showing test counts and durations.

### Step 5: Open Pull Request
Create the PR using the GitHub CLI:
```bash
gh pr create --title "<title>" --body "<body>"
```
If `gh` CLI is not installed or unauthenticated, output the formatted title and body for manual creation on GitHub.
