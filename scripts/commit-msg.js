#!/usr/bin/env node

/**
 * Commit message hook - Cross-platform (Windows, Mac, Linux)
 * Validates commit messages follow Conventional Commits
 */

import { readFileSync } from 'fs';
import { execSync } from 'child_process';

const RED = '\x1b[31m';
const GREEN = '\x1b[32m';
const YELLOW = '\x1b[33m';
const CYAN = '\x1b[36m';
const RESET = '\x1b[0m';

// Get commit message file path from args
const commitMsgFile = process.argv[2];

if (!commitMsgFile) {
  console.error(`${RED}No commit message file provided${RESET}`);
  process.exit(1);
}

// Read commit message
let commitMsg;
try {
  commitMsg = readFileSync(commitMsgFile, 'utf8').trim();
} catch {
  console.error(`${RED}Could not read commit message file${RESET}`);
  process.exit(1);
}

// Skip merge commits
if (commitMsg.startsWith('Merge')) {
  process.exit(0);
}

// Conventional commit pattern
const conventionalPattern = /^(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)(\(.+\))?: .{1,100}/;

if (!conventionalPattern.test(commitMsg)) {
  console.log(`
${RED}════════════════════════════════════════════════════════════${RESET}
${RED}  INVALID COMMIT MESSAGE${RESET}
${RED}════════════════════════════════════════════════════════════${RESET}

${YELLOW}Your message:${RESET} "${commitMsg}"

${YELLOW}Expected format:${RESET} <type>(<scope>): <description>

${CYAN}Valid types:${RESET}
  feat      New feature
  fix       Bug fix
  docs      Documentation changes
  style     Formatting (no code change)
  refactor  Code restructuring
  perf      Performance improvement
  test      Adding/updating tests
  build     Build system changes
  ci        CI/CD changes
  chore     Maintenance tasks
  revert    Reverting changes

${CYAN}Examples:${RESET}
  feat(auth): add password reset
  fix(booking): correct date validation
  docs: update API documentation
  chore: update dependencies

${YELLOW}Use interactive commit:${RESET} npm run commit
`);
  process.exit(1);
}

console.log(`${GREEN}✓ Commit message is valid${RESET}`);
