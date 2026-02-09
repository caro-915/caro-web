# 🔄 Git Workflow & Organisation

**Audience:** All developers  
**Criticité:** 🟡 HAUTE (code organization, deployment safety)

---

## 🌳 GIT Branching Strategy

### Branch Structure

```
main (production) ← only deploy-ready code
  ├── caro_bedro (development) ← default branch for ALL work
  │   ├── feature/search-improvements
  │   ├── feature/mobile-app-v2
  │   └── fix/image-watermark
  └── hotfix/urgent-payment-bug (emergency fixes only)
```

### Rules (STRICT)

| Rule | Enforcement |
|------|------------|
| `caro_bedro` is DEFAULT branch | All new PRs target caro_bedro |
| Work ALWAYS in feature branches | No direct commits to caro_bedro |
| `main` = PRODUCTION ONLY | Only merge from caro_bedro after test |
| No force-push | Use `git push` only, never `git push -f` |
| Code review before merge | At least 1 approval required |
| All tests MUST pass | CI/CD gates the merge |

---

## 📋 Commit Guidelines

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Type (First Word)

| Type | Use Case | Example |
|------|----------|---------|
| `✨ feat` | New feature | `✨ feat(pro): Add boost analytics` |
| `🐛 fix` | Bug fix | `🐛 fix(auth): Google OAuth redirect` |
| `🔧 chore` | Build, config, deps | `🔧 chore: Update Laravel to 12.5` |
| `📚 docs` | Documentation | `📚 docs: Add deployment guide` |
| `♻️ refactor` | Code cleanup | `♻️ refactor(search): Extract filter logic` |
| `⚡ perf` | Performance | `⚡ perf: Add index on annonces.prix` |
| `🧪 test` | Tests | `🧪 test: Add ProSubscriptionTest` |
| `🔒 security` | Security fix | `🔒 security: Sanitize image uploads` |
| `🚀 deploy` | Deployment | `🚀 deploy: Release v2.1.0` |

### Scope (Module)

```
pro, auth, search, messages, admin, image, payment, etc.
```

### Subject

- Imperative mood: "add" not "added"
- No period at the end
- < 50 characters
- English only (or French if team agrees)

### Body (Optional)

```
Explain WHAT and WHY, not HOW

Context: Users on free plan couldn't search by brand
Solution: Refactored filter logic to support all tiers
Impact: 0.5s slower on searches (trade-off for universality)
Tested: 10+ browser combos, 500k annonces dataset
```

### Footer

```
Closes #123          # Link to GitHub issue
BREAKING CHANGE: ...  # If API changed
Refs #456, #789       # Related issues
```

### Examples

**Good:**

```
✨ feat(pro): Add annonce boost feature

- Create boosts table (duration, multiplier)
- Add admin boost management UI
- Update search to rank boosted ads higher
- Extend test suite with 8 boost scenarios

Closes #234
BREAKING CHANGE: /api/annonces response now includes is_boosted flag
```

**Bad:**

```
update stuff
Fixed bugs
WIP: trying this
```

---

## 🔀 Workflow: Adding a Feature

### 1. Setup

```bash
# Make sure you're on latest caro_bedro
git checkout caro_bedro
git pull origin caro_bedro

# Create feature branch
git checkout -b feature/awesome-feature
```

### 2. Develop

```bash
# Work normally, small commits
git add app/Http/Controllers/FeatureController.php
git commit -m "✨ feat(feature): Add controller logic"

git add resources/views/feature/
git commit -m "✨ feat(feature): Add views"

git add tests/Feature/FeatureTest.php
git commit -m "🧪 test(feature): Add test cases"
```

### 3. Push & PR

```bash
# Push to remote
git push origin feature/awesome-feature

# Create Pull Request on GitHub
# Title: ✨ feat(feature): Add awesome feature
# Description:
#   - Closes #123
#   - Tests added: 12 scenarios
#   - Breaking: /api/feature response changed
```

### 4. Code Review

- At least 1 approver required
- Fix requested changes:
  ```bash
  git add app/fix.php
  git commit -m "☑️ review: Address feedback from @reviewer"
  git push origin feature/awesome-feature  # Auto-updates PR
  ```

### 5. Merge to caro_bedro

```bash
# GitHub UI: Click "Squash and merge" OR
git checkout caro_bedro
git pull origin caro_bedro
git merge feature/awesome-feature
git push origin caro_bedro

# Delete feature branch
git branch -d feature/awesome-feature
git push origin --delete feature/awesome-feature
```

### 6. Merge to main (Production)

```bash
# After testing on staging/caro_bedro:
git checkout main
git pull origin main
git merge caro_bedro    # Fast-forward merge
git push origin main

# Confirm deployment to Laravel Cloud
curl -I https://caro.laravel.cloud  # Should be 200
```

---

## 🚨 Hotfix: Urgent Production Bugs

### When to Use

- Payment processing broken in production
- Security vulnerability active
- Database corruption
- Auth completely down

### Process

```bash
# 1. Create hotfix branch from main
git checkout main
git pull origin main
git checkout -b hotfix/critical-bug-name

# 2. Fix quickly (no refactoring!)
git add app/fix.php
git commit -m "🔒 security: Fix XSS vulnerability"

# 3. Push & PR to main
git push origin hotfix/critical-bug-name

# 4. Get 2 approvals (not 1)
# Code review on GitHub

# 5. Merge to main
git checkout main
git merge hotfix/critical-bug-name
git push origin main

# 6. ALSO merge to caro_bedro to keep in sync
git checkout caro_bedro
git pull origin caro_bedro
git merge hotfix/critical-bug-name
git push origin caro_bedro

# 7. Delete hotfix branch
git branch -d hotfix/critical-bug-name
git push origin --delete hotfix/critical-bug-name
```

---

## 💡 Daily Workflow Example

```bash
# Morning: Get latest code
git checkout caro_bedro
git pull origin caro_bedro

# Start feature
git checkout -b feature/search-by-color
code app/Models/Annonce.php           # Add default select colors

# Commit often
git add app/Models/
git commit -m "✨ feat(search): Add color filter support"

git add app/Http/Controllers/SearchController.php
git commit -m "✨ feat(search): Implement color filter logic"

git add tests/
git commit -m "🧪 test(search): Add 5 color filter scenarios"

# End of day: Push work in progress
git push origin feature/search-by-color

# Team review next morning, fix feedback
git add app/fix.php
git commit -m "☑️ review: Simplify color query as suggested"
git push origin feature/search-by-color

# Friday: Merge to caro_bedro
git checkout caro_bedro
git pull origin caro_bedro
git merge feature/search-by-color
git push origin caro_bedro
git branch -d feature/search-by-color

# After 1 week of testing on caro_bedro: Deploy to prod
git checkout main
git merge caro_bedro
git push origin main
# 🎉 Live!
```

---

## 🔍 Conflict Resolution

### Prevention

```bash
# Before working, ensure your base is up-to-date
git pull origin caro_bedro

# If working on old branch
git rebase origin/caro_bedro
```

### If Conflicts Happen

```bash
# During merge:
git merge feature/other-feature
# CONFLICT! in app/Services/Search.php

# Option 1: Keep your version
git checkout --ours app/Services/Search.php

# Option 2: Keep their version
git checkout --theirs app/Services/Search.php

# Option 3: Manual merge in editor
# Edit file, remove <<<<, ====, >>>>

# Complete merge
git add app/Services/Search.php
git commit -m "✨ Merge feature/other-feature"
git push origin caro_bedro
```

---

## 🏷️ Versioning & Tags

### Semantic Versioning

```
v1.2.3
│ │ └─ Patch (bug fixes)
│ └─── Minor (new features, backwards compatible)
└───── Major (breaking changes)
```

### Create Release Tag

```bash
# After merging to main
git checkout main
git pull origin main

# Create annotated tag
git tag -a v2.1.0 -m "Release: Annonce boost feature"

# Push tag to remote
git push origin v2.1.0

# View all tags
git tag -l
```

### Changelog

```markdown
# v2.1.0 - 2026-02-09

## ✨ Features
- Annonce boost feature (appear higher in search)
- Boost analytics for sellers
- Admin boost management UI

## 🐛 Fixes
- Fix Google OAuth redirect for localhost
- Fix image watermark opacity (was 50%, now 45%)
- Fix message pagination (was loading all, now 20/page)

## 🔒 Security
- Added XSS sanitization on message text

## ⚡ Performance
- Added index on annonces.prix (search 2x faster)
- Cached popular brands/models

## 💥 Breaking Changes
- /api/annonces now includes is_boosted, boost_multiplier fields
```

---

## 📊 Useful Commands

### View Git Log

```bash
# Pretty commit history
git log --oneline --graph --decorate

# Last 10 commits
git log --oneline -10

# Commits by author
git log --author=ahmed --oneline

# Commits in last 7 days
git log --since="7 days ago"

# Commits mentioning "payment"
git log --grep="payment"
```

### Undo Changes

```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Undo specific file
git checkout HEAD~1 app/Models/Annonce.php
git diff app/Models/Annonce.php

# Undo commited changes
git revert abc1234      # Creates new commit undoing that commit
```

### Stash Work

```bash
# Save unfinished work
git stash

# List stashes
git stash list

# Restore stashed work
git stash pop

# Discard stash
git stash drop
```

### Cherry-pick

```bash
# Apply specific commit from another branch
git cherry-pick abc1234

# If conflict, fix and:
git add .
git cherry-pick --continue
```

---

## 🚀 Deployment Checklist

Before pushing to production:

```
[ ] Feature tested locally (npm run dev + php artisan serve)
[ ] All tests pass (php artisan test)
[ ] No console errors or warnings (dev tools)
[ ] Database migrations created (if needed)
[ ] Environment variables set on Laravel Cloud
[ ] images/storage not hardcoded in code
[ ] No debug info (dd(), var_dump())
[ ] No hardcoded credentials
[ ] Commit message is clear and descriptive
[ ] PR has >= 1 approval
[ ] All CI checks pass (GitHub Actions)
[ ] Deployed to staging on caro_bedro
[ ] QA tested feature
[ ] Merged to main branch
[ ] Production health check passed
[ ] Monitoring dashboard shows no anomalies
```

---

## 🔐 Security in Git

### Never Commit

```
❌ .env (use .env.example)
❌ database.sqlite (use .gitignore)
❌ node_modules/ (use .gitignore)
❌ storage/ (use .gitignore)
❌ Private keys or certificates
❌ API keys or secrets
❌ Password hashes or plaintext passwords
```

### Check Before Commit

```bash
# View what will be committed
git diff --cached

# See which files will be added
git status

# Use .gitignore to exclude files
cat .gitignore | head -20
```

### If You Accidentally Committed a Secret

```bash
# ⚠️ EMERGENCY! Someone has the secret now!
# 1. Rotate all credentials immediately
# 2. Remove from git history
git filter-branch --index-filter \
  "git rm -z --cached --ignore-unmatch .env" \
  --force -- --all

# 3. Force push (only if git history not public)
git push origin -f caro_bedro
```

---

## 📖 Team Standards

### Code Review Expectations

**Reviewer Should Check:**

```
✅ Code follows Laravel conventions
✅ Tests included for new features
✅ No hardcoded secrets or credentials
✅ Performance not significantly impacted
✅ Database migrations if needed
✅ Error handling included
✅ Security considerations addressed
✅ Comments for complex logic
✅ Accessibility (if UI)
✅ Backwards compatibility
```

**Developer Checklist:**

```
✅ Self-reviewed code before PR
✅ Tests pass locally
✅ No debug code (dd, var_dump)
✅ Descriptive commit messages
✅ Updated documentation
✅ No unnecessary files changed
✅ Followed coding standards
✅ Handled edge cases
```

### Code Style

```php
// PHP: Use PSR-12 (Laravel standard)
class MyClass
{
    public function myMethod()
    {
        //
    }
}

// JavaScript: Use Prettier formatting
const myFunction = () => {
    //
};
```

**Enforce with:**

```bash
# Format PHP code
./vendor/bin/pint

# Format JavaScript
npx prettier --write resources/js/

# Pre-commit hook
git hooks/pre-commit
```

---

## 🆚 Git Aliases for Faster Work

Add to `~/.bashrc` or `~/.zshrc`:

```bash
# Lazy shortcuts
alias g="git"
alias ga="git add"
alias gc="git commit -m"
alias gp="git push"
alias gpl="git pull"
alias gco="git checkout"
alias gb="git branch"
alias gst="git status"
alias gl="git log --oneline -10"
alias gd="git diff"

# Useful compound commands
alias gs="git status"
alias gfp="git fetch && git pull"  # Safe pull
function gcb() { git checkout -b "$1"; }  # Quick branch create
function gcm() { git checkout main; }     # To main
function gcb() { git checkout caro_bedro; }  # To dev
```

---

## ✅ Workflow Checklist

- [ ] caro_bedro is default branch
- [ ] main has only production-ready code
- [ ] All feature branches created from caro_bedro
- [ ] Hotfixes created from main
- [ ] Commit messages follow format
- [ ] Code reviewed before merge
- [ ] Tests pass (CI gates merge)
- [ ] Database migrations tested
- [ ] Deployment checklist complete
- [ ] Tags created for releases

