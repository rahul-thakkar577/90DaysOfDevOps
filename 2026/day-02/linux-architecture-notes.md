# Day 02 – Linux Architecture, Processes & systemd
#90DaysOfDevOps

## 1 Core Components of Linux

###  Kernel
- Core of the operating system.
- Manages CPU, memory, disk, and devices.
- Handles communication between hardware and applications using system calls.

###  User Space
- Where users and applications run.
- Includes shell (bash), system utilities, and installed programs.
- Sends requests to the kernel.

### 🔹 init / systemd (PID 1)
- First process started after the kernel boots.
- Initializes the system.
- Starts and manages background services.

---

## 2 How Processes Are Created & Managed

- A **process** = running instance of a program.
- Each process has a unique **PID (Process ID)**.
- Created using:
  - `fork()` → creates a child process
  - `exec()` → loads a new program into that process
- Linux scheduler decides which process gets CPU time.

---

## 3 Process States

- **Running (R)** → Currently executing.
- **Sleeping (S)** → Waiting for input or resource.
- **Stopped (T)** → Suspended manually.
- **Zombie (Z)** → Finished execution but parent hasn’t collected status.
- **Uninterruptible Sleep (D)** → Waiting for I/O operation.

Understanding these helps troubleshoot high CPU or stuck services.

---

## 4  Daily Linux Commands

1. `ps aux` → View running processes
2. `top` → Monitor CPU & memory usage
3. `kill <PID>` → Stop a process
4. `systemctl status <service>` → Check service status
5. `systemctl restart <service>` → Restart a service

---

## 5 What systemd Does & Why It Matters

- Manages services and daemons.
- Starts services during boot.
- Handles service dependencies.
- Maintains logs via journal.

Example:
`systemctl status nginx`
