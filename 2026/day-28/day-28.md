# Day 28 – Revision Day

## 1. Self-Assessment Summary

### Linux
- File system navigation → Can do confidently
- Process management → Can do confidently
- systemd → Need to revisit
- File editing → Can do confidently
- Troubleshooting (top, df, du) → Can do confidently
- File system hierarchy → Need to revisit
- Users & groups → Can do confidently
- Permissions → Can do confidently
- Ownership → Can do confidently
- LVM → Need to revisit
- Networking → Need to revisit

---

### Shell Scripting
- Variables & arguments → Can do confidently
- Conditions → Can do confidently
- Loops → Can do confidently
- Functions → Can do confidently
- Text processing → Need to revisit
- Error handling → Need to revisit
- Crontab → Can do confidently

---

### Git & GitHub
- Basic workflow → Can do confidently
- Branching → Can do confidently
- Push/Pull → Can do confidently
- Clone vs Fork → Can do confidently
- Merge/Rebase → Need to revisit
- Stash → Can do confidently
- Cherry-pick → Need to revisit
- Reset/Revert → Can do confidently
- Branching strategies → Need to revisit
- GitHub CLI → Can do confidently

---

## 2. Revisited Topics

### Topic 1: systemd
Re-learned how to:
- Start/stop services using systemctl
- Enable services at boot
- Check service status

---

### Topic 2: LVM
Re-learned:
- PV → VG → LV structure
- Flexible storage management
- Resizing volumes without downtime

---

### Topic 3: Merge vs Rebase
Re-learned:
- Merge keeps history with commit tree
- Rebase creates clean linear history
- Avoid rebasing shared branches

---

## 3. Quick-Fire Answers

### chmod 755 script.sh
Gives read, write, execute to owner and read, execute to others.

---

### Process vs Service
Process:
A running program

Service:
A background process managed by system

---

### Find process using port 8080
lsof -i :8080

---

### set -euo pipefail
- -e → exit on error
- -u → error on undefined variable
- pipefail → fail if any command fails

---

### git reset --hard vs git revert
reset --hard:
Deletes history and changes

revert:
Creates new commit to undo changes

---

### Branching strategy (team of 5)
GitHub Flow

---

### git stash
Temporarily saves uncommitted changes

---

### Run script daily at 3 AM
0 3 * * * /path/to/script.sh

---

### git fetch vs git pull
fetch:
Download only

pull:
Download + merge

---

### LVM
Logical Volume Management allows flexible disk resizing and management.

---

## 4. Teach It Back

### Git Branching (Simple Explanation)

Git branching allows you to create separate versions of your code to work on new features without affecting the main project. Each branch is independent, and once the work is complete, it can be merged back into the main branch. This helps teams work in parallel without conflicts.
