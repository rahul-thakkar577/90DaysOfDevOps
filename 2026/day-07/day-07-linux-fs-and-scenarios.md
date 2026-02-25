# Day 07 – Linux File System Hierarchy & Scenario Practice

---

# Part 1 – Linux File System Hierarchy

## /

Root directory. Starting point of entire filesystem.
I would use this when navigating the system structure.

---

## /home

Contains user home directories.
I would use this when managing user files and scripts.

---

## /root

Home directory of root user.
Used for administrative tasks.

---

## /etc

Contains configuration files.
Example:
ls -l /etc
Common file: hostname, hosts

I would use this when checking system configuration.

---

## /var/log

Contains log files.
Command:
du -sh /var/log/* 2>/dev/null | sort -h | tail -5

I would use this for troubleshooting services.

---

## /tmp

Temporary files stored here.
Used during installations or temporary operations.

---

## /bin

Essential command binaries like ls, cp, mv.

---

## /usr/bin

User command binaries.

---

## /opt

Optional third-party software installed here.

---

# Part 2 – Scenario-Based Practice

---

## Scenario 1: Service Not Starting (myapp)

Step 1:
systemctl status myapp  
Why: Check if service is running, failed, or inactive.

Step 2:
journalctl -u myapp -n 50  
Why: View recent logs for error messages.

Step 3:
systemctl is-enabled myapp  
Why: Check if service starts on boot.

Step 4:
systemctl start myapp  
Why: Try starting service manually after diagnosis.

---

## Scenario 2: High CPU Usage

Step 1:
top  
Why: View live CPU usage.

Step 2:
ps aux --sort=-%cpu | head -10  
Why: Identify top CPU-consuming processes.

Step 3:
kill <PID>  
Why: Stop problematic process if required.

---

## Scenario 3: Finding Service Logs (docker)

Step 1:
systemctl status docker  
Why: Check service state.

Step 2:
journalctl -u docker -n 50  
Why: View last 50 log lines.

Step 3:
journalctl -u docker -f  
Why: Follow logs in real-time.

---

## Scenario 4: Permission Denied (backup.sh)

Step 1:
ls -l /home/user/backup.sh  
Why: Check current permissions.

Step 2:
chmod +x /home/user/backup.sh  
Why: Add execute permission.

Step 3:
./backup.sh  
Why: Run script after fixing permission.

---

# What I Learned

- Linux filesystem structure is critical for troubleshooting.
- Logs are usually found in /var/log or via journalctl.
- Always check service status before restarting.
- File permissions must include execute (x) to run scripts.

Understanding system structure helps in real production debugging and DevOps interviews.
