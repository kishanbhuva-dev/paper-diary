#!/usr/bin/env node

/**
 * Pre-push hook - Cross-platform (Windows, Mac, Linux)
 * Blocks pushes to protected branches
 */

import { execSync } from 'child_process';

const RED = '\x1b[31m';
const GREEN = '\x1b[32m';
const YELLOW = '\x1b[33m';
const RESET = '\x1b[0m';

function getCurrentBranch() {
  try {
    return execSync('git rev-parse --abbrev-ref HEAD', { encoding: 'utf8' }).trim();
  } catch {
    return '';
  }
}

const branch = getCurrentBranch();
const protectedBranches = ['main', 'master'];

if (protectedBranches.includes(branch)) {
  console.log(`
${RED}════════════════════════════════════════════════════════════${RESET}
${RED}  PUSH BLOCKED: Cannot push directly to '${branch}'${RESET}
${RED}════════════════════════════════════════════════════════════${RESET}

${YELLOW}All changes to ${branch} must go through Pull Requests.${RESET}

${YELLOW}Steps:${RESET}
  1. Create a feature branch:
     git checkout -b feat/your-feature-name

  2. Push your branch:
     git push origin feat/your-feature-name

  3. Create a Pull Request on GitHub/GitLab

  4. Get code review and approval

  5. Merge via the web interface
`);
  process.exit(1);
}

console.log(`${GREEN}Pushing to branch: ${branch}${RESET}`);
