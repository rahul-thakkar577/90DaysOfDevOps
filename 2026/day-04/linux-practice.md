# Day 04 – Linux Practice: Processes and Services

Today I practiced real Linux fundamentals by running process, service, and log commands on my Ubuntu system.

---

## 🧠 Process Checks

### 1. ps aux | head
Command used:
ps aux | head

Observation:
- systemd running as PID 1
- Multiple background services active
- Snapshot view of CPU and memory usage

---

### 2. top
Command used:
top

Observation:
- Live CPU usage: 23.6%
- Memory usage: 9.6% used
- Real-time process monitoring

---

## 🛠 Service Checks

### 3. systemctl status docker
Command used:
systemctl status docker

Observation:
- Docker service is active (running)
- Loaded from systemd unit file
- No errors reported

---

### 4. systemctl list-units --type=service
Command used:
systemctl list-units --type=service

Observation:
- Multiple services active (cron, networking, systemd-logind, etc.)
- Shows currently loaded system services


##  Log Checks

### 5. journalctl -u ssh --no-pager | tail -n 10
Command used:
journalctl -u ssh --no-pager | tail -n 10

Observation:
- SSH started successfully
- No recent authentication errors


### 6. tail -n 20 /var/log/syslog
Command used:
tail -n 20 /var/log/syslog

Observation:
- System background activity
- No critical errors found



## 🔍 Mini Troubleshooting Flow

1. Checked running processes using `ps`
2. Monitored system load using `top`
3. Verified docker service status
4. Reviewed service logs using `journalctl`
5. Confirmed no critical issues in system logs


## 🎯 Key Learning

Understanding processes + services + logs together builds real troubleshooting confidence.
