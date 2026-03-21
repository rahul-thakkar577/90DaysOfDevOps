# Day 26 – GitHub CLI (gh)

## 1. Authentication Methods in gh

GitHub CLI supports:
- Browser-based login (OAuth)
- Personal Access Token (PAT)
- SSH authentication

---

## 2. Working with Repositories

gh allows managing repositories directly from terminal:
- Create repo
- Clone repo
- View repo details
- List repositories
- Open repo in browser
- Delete repo

---

## 3. Issues with gh

Using gh issue:
- Create issues with title, body, labels
- List all issues
- View specific issue
- Close issues

### Automation Use Case

gh issue can be used in scripts to:
- Automatically create issues for failed jobs
- Track bugs in CI/CD pipelines
- Manage tasks programmatically

---

## 4. Pull Requests with gh

Using gh pr:
- Create PR from terminal
- List PRs
- View PR details
- Merge PR

### Merge Methods supported

- merge (default)
- squash
- rebase

---

## 5. Reviewing PRs using gh

You can:
- View PR details
- Checkout PR locally
- Add comments
- Approve or request changes

---

## 6. GitHub Actions (Preview)

gh workflow and gh run help to:
- List workflow runs
- Check status of CI/CD pipelines
- View logs

### CI/CD Use Case

Useful for:
- Monitoring pipeline status
- Debugging failures
- Automating deployment checks

---

## 7. Useful gh Features

gh api:
Used to interact with GitHub API directly

gh gist:
Create and manage code snippets

gh release:
Manage releases

gh alias:
Create shortcuts for commands

gh search repos:
Search repositories from terminal