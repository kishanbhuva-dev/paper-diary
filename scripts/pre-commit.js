#!/usr/bin/env node

/**
 * Pre-commit hook - Cross-platform (Windows, Mac, Linux)
 * Blocks commits to main/master and runs lint checks
 */

import { execSync } from 'child_process';

const RED = '\x1b[31m';
const GREEN = '\x1b[32m';
const YELLOW = '\x1b[33m';
const RESET = '\x1b[0m';

function run(command, errorMessage) {
  try {
    execSync(command, { stdio: 'inherit' });
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

// Run ESLint
console.log(`${YELLOW}Running ESLint...${RESET}`);
if (!run('npm run lint:strict', 'ESLint failed! Run: npm run lint:fix')) {
  process.exit(1);
}

// Run Prettier check
console.log(`\n${YELLOW}Checking Prettier formatting...${RESET}`);
if (!run('npm run format:check', 'Formatting check failed! Run: npm run format')) {
  process.exit(1);
}

console.log(`\n${GREEN}✓ All pre-commit checks passed!${RESET}\n`);
