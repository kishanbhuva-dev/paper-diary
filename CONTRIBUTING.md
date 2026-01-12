# Contributing Guidelines

Welcome to the Paper Diary project! This document outlines the **mandatory** development practices all contributors must follow.

## ⚠️ MANDATORY SETUP (All Platforms)

**Works on: Windows, macOS, Ubuntu/Linux**

```bash
# 1. Clone the repository
git clone <repository-url>
cd paper-diary

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies (auto-installs git hooks)
npm install

# 4. Verify hooks are installed
ls .husky/  # Should show: pre-commit, pre-push, commit-msg
```

> **❌ No exceptions.** Pull requests from developers who bypass these checks will be rejected.

---

## 🔒 Branch Protection

### Protected Branches
- `main` - Production code
- `master` - Production code (legacy)
- `develop` - Development branch

### What's Blocked

| Action | Blocked? |
|--------|----------|
| Commit directly to `main` | ❌ **Yes** |
| Push directly to `main` | ❌ **Yes** |
| Commit to feature branch | ✅ Allowed (after lint passes) |
| Push to feature branch | ✅ Allowed |

### Required Workflow

```bash
# 1. Create a feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feat/your-feature-name

# 2. Make changes and commit
git add .
git commit -m "feat(scope): description"

# 3. Push your feature branch
git push origin feat/your-feature-name

# 4. Create Pull Request on GitHub/GitLab
#    - Target: develop (for features) or main (for releases)
#    - Require at least 1 code review
#    - CI must pass

# 5. Merge via web interface (not command line)
```

---

## 🛡️ Enforced Quality Standards

### Pre-Commit Checks (Automatic)

Every commit attempt runs these checks on **ALL files**:

| Check | Tool | Rule |
|-------|------|------|
| Branch protection | Node script | Cannot commit to main/master/develop |
| JavaScript/Vue linting | ESLint | Zero errors, zero warnings |
| Code formatting | Prettier | Must match config |

### Pre-Push Checks (Automatic)

| Check | Rule |
|-------|------|
| Branch protection | Cannot push to main/master |

### Commit Message (Automatic)

All commits must follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>
```

---

## 📝 Commit Message Format

### Allowed Types

| Type | When to Use |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Formatting, no logic change |
| `refactor` | Code restructuring |
| `perf` | Performance improvement |
| `test` | Adding/updating tests |
| `build` | Build system changes |
| `ci` | CI/CD changes |
| `chore` | Maintenance |

### Examples

```bash
# ✅ GOOD
git commit -m "feat(auth): add password reset"
git commit -m "fix(booking): correct date validation"
git commit -m "docs: update API documentation"

# ❌ BAD - Will be rejected
git commit -m "fixed stuff"
git commit -m "WIP"
git commit -m "updates"

# 💡 Use interactive commit (recommended)
npm run commit
```

---

## 🚫 What Will Block Your Work

### Commit Blocked

1. **On protected branch** (main, master, develop)
2. **Any ESLint errors** - Zero tolerance
3. **Any ESLint warnings** - Zero tolerance
4. **Prettier formatting issues**
5. **Invalid commit message format**

### Push Blocked

1. **Pushing to main or master** - Use Pull Requests

### PR Blocked (CI)

1. **Lint failures**
2. **Format check failures**
3. **PHP style violations**
4. **PHPStan errors**
5. **Test failures**

---

## 🛠️ Quick Fix Commands

```bash
# Fix JavaScript/Vue issues
npm run lint:fix

# Format all files
npm run format

# Check everything before committing
npm run quality

# Fix PHP code style
composer lint:fix

# Interactive commit (ensures correct format)
npm run commit
```

---

## 🔄 Development Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                         main                                 │
│  (protected - no direct commits/pushes)                     │
└──────────────────────────▲──────────────────────────────────┘
                           │ Pull Request (reviewed)
┌──────────────────────────┴──────────────────────────────────┐
│                        develop                               │
│  (protected - no direct commits)                            │
└──────────────────────────▲──────────────────────────────────┘
                           │ Pull Request (reviewed)
┌──────────────────────────┴──────────────────────────────────┐
│                   feat/your-feature                          │
│  (you work here)                                            │
└─────────────────────────────────────────────────────────────┘
```

---

## 💻 Cross-Platform Notes

### Windows
- Git hooks work via Node.js (cross-platform)
- Use PowerShell or Git Bash
- Line endings are auto-normalized (see `.gitattributes`)

### macOS / Linux
- Git hooks work natively
- Use Terminal or any shell

### Common Issues

**"Permission denied" on Mac/Linux:**
```bash
chmod +x .husky/*
chmod +x scripts/*.js
```

**Hooks not running:**
```bash
# Reinstall Husky
rm -rf .husky/_
npm run prepare
```

---

## ❓ FAQ

### "Can I bypass the hooks with `--no-verify`?"

**No.** While Git allows this flag, CI will catch any violations. Your PR will be rejected.

### "I need to commit to main for an emergency fix!"

Contact the team lead. Emergency fixes should still go through a fast-tracked PR with at least one reviewer.

### "The linter is too strict!"

It's strict by design. Run `npm run lint:fix` to auto-fix most issues.

### "I have legacy code with errors"

Fix them. The team decided to enforce quality standards. Use `npm run lint:fix` to auto-fix ~450 errors automatically.

---

## 📞 Getting Help

1. Check error messages - they're descriptive
2. Run `npm run lint` to see all issues
3. Ask in the team Slack channel
4. Check this guide's examples
