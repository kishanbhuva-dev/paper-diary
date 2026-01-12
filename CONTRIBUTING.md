# Contributing Guidelines

Welcome to the Paper Diary project! This document outlines the **mandatory** development practices
all contributors must follow.

## ⚠️ MANDATORY SETUP

**Before you write ANY code, you MUST complete these steps:**

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node.js dependencies (this auto-installs git hooks)
npm install

# 3. Verify hooks are installed
ls .husky/  # Should show: pre-commit, commit-msg
```

> **❌ No exceptions.** Pull requests from developers who bypass these checks will be rejected.

---

## 🔒 Enforced Quality Standards

### Pre-Commit Hooks (Automatic)

Every commit is automatically checked for:

| Check                  | Tool         | Strictness                |
| ---------------------- | ------------ | ------------------------- |
| JavaScript/Vue linting | ESLint       | Zero warnings allowed     |
| Code formatting        | Prettier     | Must pass                 |
| PHP code style         | Laravel Pint | Must pass                 |
| Commit message format  | Commitlint   | Conventional commits only |

**These hooks CANNOT be disabled.** If your code doesn't pass, you cannot commit.

### Continuous Integration (Backup Enforcement)

Even if someone attempts to bypass local hooks, CI will catch it:

- ❌ PRs with lint errors are **auto-blocked**
- ❌ PRs with formatting issues are **auto-blocked**
- ❌ PRs that fail PHPStan are **auto-blocked**
- ❌ PRs with failing tests are **auto-blocked**

---

## 📝 Commit Message Format

All commits **MUST** follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Allowed Types

| Type       | When to Use                 |
| ---------- | --------------------------- |
| `feat`     | New feature                 |
| `fix`      | Bug fix                     |
| `docs`     | Documentation only          |
| `style`    | Formatting, no logic change |
| `refactor` | Code restructuring          |
| `perf`     | Performance improvement     |
| `test`     | Adding/updating tests       |
| `build`    | Build system changes        |
| `ci`       | CI/CD changes               |
| `chore`    | Maintenance                 |

### Examples

```bash
# ✅ GOOD
git commit -m "feat(booking): add date validation"
git commit -m "fix(auth): correct token refresh logic"
git commit -m "docs: update API documentation"

# ❌ BAD - Will be rejected
git commit -m "fixed stuff"
git commit -m "WIP"
git commit -m "updates"
```

### Interactive Commits (Recommended)

Use the interactive tool to ensure correct format:

```bash
npm run commit
```

---

## 🚫 What Will Get Your PR Rejected

1. **Any ESLint errors or warnings** - Zero tolerance
2. **Unformatted code** - Must pass Prettier
3. **PHP code style violations** - Must pass Pint
4. **Non-conventional commit messages**
5. **console.log() statements** - Remove before committing
6. **debugger statements**
7. **Unused variables or imports**

---

## 🛠️ Quick Fix Commands

If your commit is blocked, use these commands:

```bash
# Fix JavaScript/Vue issues automatically
npm run lint:fix

# Format all files
npm run format

# Fix PHP code style
composer lint:fix

# Check everything before committing
npm run quality && composer quality
```

---

## 🔄 Development Workflow

1. **Create a feature branch**

   ```bash
   git checkout -b feat/your-feature-name
   ```

2. **Make your changes**

3. **Verify quality locally**

   ```bash
   npm run quality
   composer quality
   ```

4. **Commit with conventional message**

   ```bash
   npm run commit
   # OR
   git commit -m "feat(scope): description"
   ```

5. **Push and create PR**

   ```bash
   git push origin feat/your-feature-name
   ```

6. **Wait for CI to pass** - PRs cannot be merged until all checks pass

---

## ❓ FAQ

### "Can I bypass the hooks with `--no-verify`?"

**No.** While Git allows this flag, CI will catch any violations. Your PR will be rejected, and
you'll have to fix everything anyway. Save yourself time—fix issues before committing.

### "The hooks are too strict!"

They're strict by design. Clean, consistent code is a non-negotiable requirement for this project.
The few extra minutes spent fixing lint errors prevents hours of debugging later.

### "I have a legitimate console.log for debugging"

Use the proper logging utilities or browser dev tools. If you absolutely need console output
temporarily, use this pattern (but remove before committing):

```javascript
// eslint-disable-next-line no-console
console.log('Temporary debug - DO NOT COMMIT');
```

### "PHPStan is complaining about something that works fine"

Add type hints to your code. PHPStan level 6 requires proper typing. This catches bugs before they
reach production.

---

## 📞 Getting Help

If you're stuck:

1. Check the error messages carefully—they're usually descriptive
2. Run `npm run lint` or `composer analyse` to see all issues
3. Ask in the team Slack channel
4. Check existing code for examples of correct patterns
