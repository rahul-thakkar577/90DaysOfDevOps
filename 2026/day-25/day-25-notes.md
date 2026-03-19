# Day 25 – Git Reset vs Revert & Branching Strategies

## 1. Git Reset Types

### git reset --soft
Moves HEAD to previous commit but keeps changes staged.

### git reset --mixed (default)
Moves HEAD and unstages changes, but keeps them in working directory.

### git reset --hard
Moves HEAD and deletes all changes from staging and working directory.

---

## 2. Difference between --soft, --mixed, --hard

--soft:
Keeps changes staged

--mixed:
Keeps changes but unstaged

--hard:
Deletes all changes completely

---

## 3. Which is destructive?

git reset --hard is destructive because it permanently deletes changes from working directory.

---

## 4. When to use each

--soft:
When you want to edit last commit

--mixed:
When you want to unstage changes

--hard:
When you want to completely discard changes

---

## 5. Should you use reset on pushed commits?

No, because it rewrites history and can break collaboration.

---

## 6. Git Revert

git revert creates a new commit that undoes changes of a previous commit.

---

## 7. Reverting a middle commit

When reverting commit Y, Git creates a new commit that reverses Y, but original commit remains in history.

---

## 8. Reset vs Revert

| Feature | git reset | git revert |
|--------|----------|-----------|
| What it does | Moves HEAD backward | Creates new commit to undo |
| Removes history | Yes | No |
| Safe for shared branches | No | Yes |
| Use case | Local changes | Public/shared history |

---

## 9. Why revert is safer

Because it does not delete history and works well in team environments.

---

## 10. When to use revert vs reset

Revert:
- For pushed commits
- In team projects

Reset:
- For local changes
- Before pushing

---

# Branching Strategies

## 11. GitFlow

How it works:
Uses multiple branches: main, develop, feature, release, hotfix

Flow:
main → develop → feature → release → main

Used in:
Large projects with scheduled releases

Pros:
- Structured workflow
- Good for release management

Cons:
- Complex
- Slower development

---

## 12. GitHub Flow

How it works:
Single main branch + feature branches

Flow:
main → feature → pull request → merge

Used in:
Startups and continuous deployment

Pros:
- Simple
- Fast

Cons:
- Less control over releases

---

## 13. Trunk-Based Development

How it works:
Developers commit directly to main or short-lived branches

Flow:
main ← short-lived branches

Used in:
High-speed teams with CI/CD

Pros:
- Fast integration
- Less merge conflict

Cons:
- Requires strong testing

---

## 14. Which strategy to use?

Startup (fast shipping):
GitHub Flow or Trunk-Based Development

Large team (scheduled releases):
GitFlow

---

## 15. Open-source example

Many open-source projects use GitHub Flow because it is simple and supports collaboration via pull requests.