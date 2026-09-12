# Developer GitHub Workflow

This guide details the standard GitHub collaboration workflow for developers contributing to this codebase, from feature inception to production merge.

---

## 🔄 End-to-End Workflow Diagram

```
1. Issue / Spec  ──>  2. Feature Branch  ──>  3. Local Dev & Tests  ──>  4. Conventional Commit
      (docs/)             (feat/...)                 (/verify)                  (/git-commit)
                                                                                       │
8. Linear Main   <──  7. Squash & Merge  <──  6. GitHub Actions CI  <──  5. Pull Request
     (Production)         (Clean History)          (.github/workflows/ci.yml)       (/git-pr)
```

---

## 📋 The 8-Step Developer Lifecycle

### Step 1: Requirements & Obsidian Context Pull
- Read or create the task specification in `docs/features/`.
- Verify existing domain models and architectural constraints in `docs/`.

### Step 2: Branching from `main`
Always cut feature or bugfix branches from the latest updated `main`:
```bash
git checkout main
git pull origin main
git checkout -b feat/your-feature-name
```
*(Or invoke `/feature-start` to automate this step)*.

### Step 3: Local Development & Verification
- Adhere to the **Clean Architecture & Guardrails**:
  - Keep controllers thin; use single-purpose Action classes (`app/Actions`).
  - Strict typing in PHP 8.5 (`declare(strict_types=1);`) and TypeScript (`zero any`).
  - Follow the **Anti-Slop UI** rules for Vue 3 SFCs.
- Run local verification before committing:
  ```bash
  # Slash command:
  /verify

  # Or individual tools:
  ./vendor/bin/pint --test
  php artisan test
  npx vue-tsc --noEmit
  npm run lint
  npm run format:check
  ```

### Step 4: Atomic Conventional Commits
- Make small, bisectable commits.
- Write commit messages following Conventional Commits format:
  ```bash
  git add <files>
  git commit -m "feat(orders): implement CSV export action"
  ```
*(Or invoke `/git-commit` to automatically run formatters, safety checks, and draft the message)*.

### Step 5: Push Branch & Open Pull Request
Push your branch to GitHub:
```bash
git push -u origin feat/your-feature-name
```
Open a PR on GitHub. The PR will automatically load `.github/pull_request_template.md`:
- Provide a summary of what was built and changed.
- Check off the pre-flight verification checklist.
- Include the Pest PHP test scorecard.
*(Or invoke `/git-pr` via Antigravity to automate this)*.

### Step 6: Automated GitHub Actions CI
Upon push or PR opening, GitHub Actions runs `.github/workflows/ci.yml`:
1. **`quality` Job**:
   - Checks Pint PHP styling (`vendor/bin/pint --test`).
   - Checks Prettier formatting (`npm run format:check`).
   - Runs ESLint (`npm run lint`).
   - Runs TypeScript validation (`npx vue-tsc --noEmit`).
2. **`tests` Job**:
   - Executes the full Pest PHP test suite on an isolated SQLite database.

> [!IMPORTANT]
> A pull request **cannot be merged** if any CI check fails.

### Step 7: Code Review & Discussion
- Reviewers inspect the visual diffs, architecture adherence, and test coverage.
- If revisions are requested, make local edits, commit, and push; GitHub Actions will re-run automatically.

### Step 8: Squash and Merge & Cleanup
- Once approved and CI passes green:
  - Use **Squash and Merge** on GitHub to combine feature commits into a single clean commit on `main`.
  - Delete the remote feature branch (`Delete branch` button).
  - Switch back to `main` locally and pull:
    ```bash
    git checkout main
    git pull origin main
    git branch -d feat/your-feature-name
    ```
- Update `docs/` with any architectural decisions or notes resulting from the feature.
