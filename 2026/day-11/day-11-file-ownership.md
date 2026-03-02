# Day 11 – File Ownership Challenge (chown & chgrp)

## Files & Directories Created

Files:
- devops-file.txt
- team-notes.txt
- project-config.yaml
- heist-project/vault/gold.txt
- heist-project/plans/strategy.conf
- bank-heist/access-codes.txt
- bank-heist/blueprints.pdf
- bank-heist/escape-plan.txt

Directories:
- app-logs/
- heist-project/
- bank-heist/

Groups Created:
- heist-team
- planners
- vault-team
- tech-team

Users Used:
- tokyo
- berlin
- professor
- nairobi

---

## Ownership Changes

### Basic chown
- devops-file.txt: user:user → tokyo:user → berlin:user

### chgrp operation
- team-notes.txt: user:user → user:heist-team

### Combined owner & group change
- project-config.yaml → professor:heist-team
- app-logs/ → berlin:heist-team

### Recursive ownership
- heist-project/ (including vault/ & plans/)
  Changed to → professor:planners

### Practice challenge ownership
- access-codes.txt → tokyo:vault-team
- blueprints.pdf → berlin:tech-team
- escape-plan.txt → nairobi:vault-team

---

## Commands Used

# View ownership
ls -l filename

# Change owner
sudo chown tokyo devops-file.txt

# Change group
sudo chgrp heist-team team-notes.txt

# Change owner and group
sudo chown professor:heist-team project-config.yaml

# Recursive change
sudo chown -R professor:planners heist-project/

# Change only group using chown
sudo chown :vault-team access-codes.txt

---

## What I Learned

- File ownership controls which user manages a file.
- Groups allow team-based access control.
- Recursive ownership (-R) is critical when managing application directories.
- chown can modify both owner and group in one command.
- Proper ownership prevents permission conflicts in deployments.

---

## Why This Matters in DevOps

File ownership is essential for:

- Application deployments
- Log file access control
- CI/CD pipeline artifact handling
- Container volume permissions
- Shared team environments

---

## Summary

Today I practiced managing file ownership using chown and chgrp. 
Understanding ownership is critical to maintaining secure and stable Linux systems in real DevOps environments.
