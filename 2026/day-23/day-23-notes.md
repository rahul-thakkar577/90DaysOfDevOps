# Day 23 – Git Branching & Working with GitHub

## 1. What is a branch in Git?

A branch in Git is an independent line of development. It allows developers to work on new features, bug fixes, or experiments without affecting the main codebase.

---

## 2. Why do we use branches instead of committing everything to main?

Branches help isolate changes. Developers can work on features safely without breaking the main project. After testing, the branch can be merged into the main branch.

---

## 3. What is HEAD in Git?

HEAD is a pointer that refers to the current branch and the latest commit on that branch. It tells Git where you are currently working.

---

## 4. What happens to your files when you switch branches?

When switching branches, Git updates the working directory to match the files and commits of the selected branch. Files may appear, disappear, or change depending on the branch state.

---

## 5. Difference between origin and upstream

origin:
The default name for the remote repository you cloned or pushed to. It usually points to your own repository.

upstream:
The original repository from which a project was forked. It is used to keep your fork updated with the original project.

---

## 6. Difference between git fetch and git pull

git fetch:
Downloads changes from the remote repository but does not merge them into your current branch.

git pull:
Fetches the changes from the remote repository and automatically merges them into the current branch.

---

## 7. Difference between clone and fork

clone:
Creates a copy of a remote repository on your local machine.

fork:
Creates a copy of someone else's repository on your GitHub account.

---

## 8. When would you clone vs fork?

Clone:
When you want to work directly on a repository you have access to.

Fork:
When contributing to an open-source project where you do not have direct write access.

---

## 9. After forking, how do you keep your fork updated?

You add the original repository as an upstream remote and fetch updates from it.

Example commands:

git remote add upstream <original-repo-url>

git fetch upstream

git merge upstream/main