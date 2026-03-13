# Shell Scripting Cheat Sheet

**Personal quick reference for Bash / POSIX shell scripting**  
**Last updated:** March 2026  
**#90DaysOfDevOps #TrainWithShubham**

## Quick Reference Table

| Topic              | Key Syntax                              | Common Example                                      |
|--------------------|-----------------------------------------|-----------------------------------------------------|
| Shebang            | `#!/usr/bin/env bash`                   | `#!/usr/bin/env bash`                               |
| Variable           | `VAR="value"`                           | `APP="nginx"`                                       |
| Use (quoted)       | `"$VAR"`                                | `echo "App is $APP"`                                |
| Positional param   | `$1`, `$2`, `$#`, `$@`, `$?`            | `./script.sh file.txt` → `$1 = file.txt`            |
| If condition       | `if [ cond ]; then ... fi`              | `if [ -f /tmp/run ]; then ...`                      |
| For loop           | `for i in list; do ... done`            | `for f in *.log; do ...`                            |
| While read         | `while read line; do ... done`          | `cat file \| while read line; do ...`               |
| Function           | `name() { ... }`                        | `log() { echo "[$(date)] $1"; }`                    |
| Grep               | `grep -Eir "pattern" dir`               | `grep -i error /var/log/*.log`                      |
| Awk                | `awk -F: '{print $1}'`                  | `awk '{print $1,$NF}' access.log`                   |
| Sed replace        | `sed -i 's/old/new/g' file`             | `sed -i 's/debug=true/debug=false/g' config`       |
| Exit on error      | `set -euo pipefail`                     | Top of script (very recommended)                    |

---

## 1. Basics

- Shebang
#!/usr/bin/env bash     # Best practice – portable across systems
#!/bin/bash             # Common but less flexible
- Comments: Use # for single line or inline comments.

- Variables: * VAR="Value" (Assign; no spaces)
    $VAR or "${VAR}" (Access value; use quotes to handle spaces)
    '$VAR' (Literal string; won't expand variable)

- User Input: read -p "Enter Name: " NAME

- Special Variables:
    $0: Script name | $1-$9: Arguments | $#: Number of args | $@: All args | $?: Exit status of last command.

## 2  Operators & Conditionals
Comparisons
- Strings: = (equal), != (not equal), -z (empty), -n (not empty).
- Integers: -eq, -ne, -lt, -gt, -le, -ge.

File Tests:
    -f: Is a regular file | -d: Is a directory | -e: Exists | -r/-w/-x: Permissions

## 3 Loops
- List-based For: for item in apple banana; do echo $item; done
- C-style For: for ((i=0; i<5; i++)); do echo $i; done
- While Loop: Runs as long as condition is true.
- Until Loop: Runs until condition becomes true.
- Loop Control: break (exit loop), continue (skip to next iteration).
- File Globbing: for file in *.log; do cp $file /backup/; done
- Reading Output: ls | while read line; do echo "File: $line"; done

## 4 Functions

# Definition
deploy_app() {
  local ENV=$1  # Local variable restricted to function
  echo "Deploying to $ENV..."
  return 0      # Numeric status code (0-255)
}

## 5 ext Processing
- grep: grep -ri "error" /var/log (Recursive, case-insensitive). -c (count), -v (invert match).

- awk: awk -F',' '{print $1, $3}' file.csv (Print specific columns by delimiter).

- sed: sed -i 's/localhost/127.0.0.1/g' config.php (In-place find and replace).

- cut: cut -d':' -f1 /etc/passwd (Extract 1st column using : delimiter).

- sort/uniq: sort file.txt | uniq -c (Sort and count unique occurrences).

- tr: echo "hello" | tr 'a-z' 'A-Z' (Translate to uppercase).

- wc: wc -l file.txt (Count lines).

- head/tail: tail -f app.log (Follow log updates in real-time).

## 6 Useful One-Liners
1. Cleanup: find /tmp -type f -mtime +7 -delete (Delete files older than 7 days).

2. Health Check: pgrep nginx > /dev/null || systemctl start nginx (Start service if not running).

3. Log Monitoring: tail -f access.log | grep --line-buffered "404" (Real-time error watch).

4. Bulk Rename: for f in *.txt; do mv "$f" "${f%.txt}.bak"; done (Change extensions).

5. Disk Alert: df -h | awk '$5 > 80 {print $0}' (List partitions over 80% full)

## 7 Error Handling & Debugging
- set -e: Exit immediately if a command fails.

- set -u: Exit if an undefined variable is used.

- set -o pipefail: Prevents errors in a pipeline from being masked.

- set -x: Print each command before executing (Debug mode).

- trap: Execute a cleanup function on script exit or interrupt.