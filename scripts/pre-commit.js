#!/usr/bin/env node

/**
 * Pre-commit hook - Cross-platform (Windows, Mac, Linux)
 * Blocks commits to main/master and runs lint checks (Frontend + Backend)
 */

import { execSync } from 'child_process';
import { existsSync } from 'fs';
import { join } from 'path';

const RED = '\x1b[31m';
const GREEN = '\x1b[32m';
const YELLOW = '\x1b[33m';
const CYAN = '\x1b[36m';
const RESET = '\x1b[0m';

function run(command, errorMessage, cwd = process.cwd()) {
  try {
    execSync(command, { stdio: 'inherit', cwd });
    return true;
  } catch {
    console.error(`\n${RED}${errorMessage}${RESET}\n`);
    return false;
  }
}

function getCurrentBranch() {
  try {
    return execSync('git rev-parse --abbrev-ref HEAD', { encoding: 'utf8' }).trim();
  } catch {
    return '';
  }
}

function checkCommandExists(command) {
  try {
    execSync(`which ${command}`, { stdio: 'ignore' });
    return true;
  } catch {
    // On Windows, try 'where'
    try {
      execSync(`where ${command}`, { stdio: 'ignore' });
      return true;
    } catch {
      return false;
    }
  }
}

// Check branch
const branch = getCurrentBranch();
const protectedBranches = ['main', 'master', 'develop'];

if (protectedBranches.includes(branch)) {
  console.log(`
${RED}════════════════════════════════════════════════════════════${RESET}
${RED}  COMMIT BLOCKED: Cannot commit directly to '${branch}'${RESET}
${RED}════════════════════════════════════════════════════════════${RESET}

${YELLOW}Create a feature branch instead:${RESET}
  git checkout -b feat/your-feature-name
  git checkout -b fix/bug-description

${YELLOW}Then commit your changes:${RESET}
  git add .
  git commit -m "feat: your message"
`);
  process.exit(1);
}

console.log(`\n${GREEN}Branch: ${branch}${RESET}\n`);

// ═══════════════════════════════════════════════════════════════
// FRONTEND CHECKS
// ═══════════════════════════════════════════════════════════════

console.log(`${CYAN}════════════════════════════════════════════════════════════${RESET}`);
console.log(`${CYAN}  Frontend Checks${RESET}`);
console.log(`${CYAN}════════════════════════════════════════════════════════════${RESET}\n`);

// Run ESLint
console.log(`${YELLOW}→ Running ESLint (zero warnings allowed)...${RESET}`);
if (!run('npm run lint:strict', 'ESLint failed! Run: npm run lint:fix')) {
  process.exit(1);
}

// Run Prettier check
console.log(`\n${YELLOW}→ Checking Prettier formatting...${RESET}`);
if (!run('npm run format:check', 'Formatting check failed! Run: npm run format')) {
  process.exit(1);
}

// ═══════════════════════════════════════════════════════════════
// BACKEND CHECKS
// ═══════════════════════════════════════════════════════════════

console.log(`\n${CYAN}════════════════════════════════════════════════════════════${RESET}`);
console.log(`${CYAN}  Backend Checks${RESET}`);
console.log(`${CYAN}════════════════════════════════════════════════════════════${RESET}\n`);

// Check if vendor/bin/pint exists
const pintPath = join(process.cwd(), 'vendor', 'bin', 'pint');
if (!existsSync(pintPath)) {
  console.log(`${YELLOW}⚠ Laravel Pint not found. Run: composer install${RESET}\n`);
} else {
  // Run Laravel Pint (code style check)
  console.log(`${YELLOW}→ Running Laravel Pint (code style check)...${RESET}`);
  if (!run('composer lint', 'Laravel Pint failed! Run: composer lint:fix')) {
    process.exit(1);
  }
}

// Check if vendor/bin/phpstan exists
const phpstanPath = join(process.cwd(), 'vendor', 'bin', 'phpstan');
if (!existsSync(phpstanPath)) {
  console.log(`${YELLOW}⚠ PHPStan not found. Run: composer install${RESET}\n`);
} else {
  // Run PHPStan (static analysis)
  console.log(`${YELLOW}→ Running PHPStan (static analysis - level 6)...${RESET}`);
  console.log(`${YELLOW}  This may take a moment...${RESET}\n`);
  if (!run('composer analyse', 'PHPStan failed! Fix the errors above.')) {
    process.exit(1);
  }
}

console.log(`\n${GREEN}════════════════════════════════════════════════════════════${RESET}`);
console.log(`${GREEN}  ✓ All pre-commit checks passed!${RESET}`);
console.log(`${GREEN}════════════════════════════════════════════════════════════${RESET}\n`);
