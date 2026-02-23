## Day 05 – Linux Troubleshooting Drill: CPU, Memory, and Logs

## Task

## 1️⃣ Environment Basics

### uname -a
Command: uname -a

Observation:
Shows kernel version and system architecture.

### lsb_release -a
Command: lsb_release -a
Observation:
Confirms OS version (Ubuntu 22.04).

---
## 2️⃣ CPU & Memory Snapshot

### top
Command: top

Observation:
Docker using low CPU. No abnormal spikes.

### free -h
Command:
free -h

Observation:
Memory usage normal. No swap usage.

---

## 3️⃣ Disk & IO

### df -h
Command: df -h
Observation:
Root partition 45% used. Enough free space.

### du -sh /var/log
Command: du -sh /var/log

Observation:
Log size moderate. No disk pressure.

---

## 4️⃣ Network Snapshot

### ss -tulpn
Command: ss -tulpn | grep docker

Observation: Docker listening on expected port.

---

## 5️⃣ Logs Reviewed

### journalctl -u docker -n 50
Command:
journalctl -u docker -n 50

Observation:
No recent errors found.

---

## Quick Findings

- Service running normally
- No CPU/memory spike
- No disk issue
- No log errors
---
## If This Worsens

1. Restart service → sudo systemctl restart docker
2. Check container logs → docker logs <container-id>
3. Enable debug logs for deeper investigation
