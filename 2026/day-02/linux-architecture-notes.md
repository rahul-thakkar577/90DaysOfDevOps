Day 02 – Linux Architecture, Processes & systemd
#90DaysOfDevOps
🧠 1️⃣ Core Components of Linux
 -> Kernel
- Core of the OS.
- Manages CPU, memory, disk, and devices.
- Handles system calls between apps and hardware.

 -> User Space
- Where users and applications run.
- Includes shell (bash), utilities, and programs.
- Interacts with kernel via system calls.

-> init / systemd (PID 1)
- First process started by the kernel.
- Initializes the system.
- Starts and manages background services
