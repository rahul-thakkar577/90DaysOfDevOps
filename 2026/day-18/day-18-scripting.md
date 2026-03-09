# Day 18 – Shell Scripting: Functions & Intermediate Concepts

## Overview
Today I learned how to write cleaner and reusable shell scripts using **functions**, **strict mode**, and **local variables**.  
These concepts help make scripts safer, more maintainable, and closer to real-world DevOps automation practices.

---

# Task 1 – Basic Functions

## Script: functions.sh
#!/bin/bash

greet () {
	echo "Hello $1"
}

add () {
	num1=5
	num2=5
	num=$(( $num1 + $num2 ))
	echo "num = $num"
}

greet $1
add

# Task 2  Functions with Return Values

## Script : disk_check.sh

#!/bin/bash

check_disk () {
	echo "--------Disk usage---------"
	df -h
}
check_memory () {
	echo "------Memory usage----------"
	free -h
}

check_disk
check_memor

# Task 3  Strict Mode — set -euo pipefail

## Script : strict_demo.sh

#!/bin/bash
set -euo pipefail

echo "Testing strict mode"

echo "Undefined variable test:"
echo $UNDEFINED_VAR

# Task 4: Local Variables

## Script : local_demo.sh

#!/bin/bash

demo_local() {
    local message="This is a local variable"
    echo $message
}

demo_global() {
    message="This is a global variable"
}

demo_local
echo $message

demo_global
echo $message

# Task 5: Build a Script — System Info Reporter

## Script : system_info.sh

#!/bin/bash
set -euo pipefail

print_system() {
    echo "=========================== System Information ===================="
    hostname
    uname -a
}

print_uptime() {
    echo "---------------------- Uptime -----------------"
    uptime
}

print_disk() {
    echo "----------------------- Disk Usage ----------------------"
    df -h | sort -rh | head -5
}

print_memory() {
    echo "--------------------- Memory Usage --------------------"
    free -h
}

print_cpu() {
    echo "--------------- Top CPU Processes ---------------------"
    ps -eo pid,ppid,cmd,%cpu --sort=-%cpu | head -6
}

main() {
    print_system
    print_uptime
    print_disk
    print_memory
    print_cpu
}

main

----

What I Learned

Functions help break scripts into reusable and organized blocks.
Strict mode (set -euo pipefail) makes scripts safer and prevents hidden errors.
Local variables keep function data isolated and avoid unexpected behavior.

Summary

Day 18 helped me move toward clean, maintainable, and production-style shell scripts, which are essential for DevOps automation.
