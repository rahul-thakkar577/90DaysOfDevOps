# Day 24 – Advanced Git: Merge, Rebase, Stash & Cherry Pick

## 1. Fast-forward merge

A fast-forward merge happens when the target branch has not moved ahead and Git can simply move the pointer forward without creating a new commit.

---

## 2. When does Git create a merge commit?

Git creates a merge commit when both branches have new commits. It combines histories and creates a new commit to preserve both changes.

---

## 3. What is a merge conflict?

A merge conflict occurs when Git cannot automatically merge changes because the same line in a file was modified in different branches.

---

## 4. What does rebase do?

Rebase moves or reapplies commits from one branch onto another. It rewrites commit history by placing your changes on top of the latest commits.

---

## 5. Merge vs Rebase history

Merge:
Keeps the complete history and shows branch structure with merge commits.

Rebase:
Creates a linear history by removing unnecessary merge commits.

---

## 6. Why should you not rebase shared commits?

Rebasing changes commit history. If commits are already pushed and shared, it can cause conflicts and confusion for other developers.

---

## 7. When to use rebase vs merge

Rebase:
- To maintain clean, linear history
- Before merging feature branches locally

Merge:
- When working in teams
- To preserve full history of changes

---

## 8. Squash merge

Squash merge combines multiple commits into a single commit before merging into the main branch.

---

## 9. Squash vs regular merge

Squash merge:
- Creates a single commit
- Keeps history clean

Regular merge:
- Keeps all commits
- Preserves detailed history

Trade-off:
Squashing removes detailed commit history.

---

## 10. Git stash

Git stash temporarily saves uncommitted changes so you can switch branches without committing.

---

## 11. stash pop vs stash apply

git stash pop:
Applies the stash and removes it from stash list.

git stash apply:
Applies the stash but keeps it in the list.

---

## 12. When to use stash

- When switching tasks quickly
- When you don’t want to commit incomplete work

---

## 13. Cherry-pick

Cherry-pick applies a specific commit from one branch to another.

---

## 14. When to use cherry-pick

- Applying hotfixes to main branch
- Selecting specific changes without merging full branch

---

## 15. Risks of cherry-pick

- Duplicate commits
- Conflicts
- Messy history if overused