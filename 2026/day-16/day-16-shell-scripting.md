## Overview
Today I started learning the fundamentals of **Shell Scripting** in Linux.  
I practiced writing basic scripts, using variables, taking user input, and applying simple conditional logic.

---

# Task 1: First Script


```bash
#!/bin/bash
echo "Hello, DevOps!"

Commands Used
chmod +x hello.sh
./hello.sh

Output
Hello, DevOps!
Observation

The shebang (#!/bin/bash) tells the system which interpreter should run the script.
If the shebang line is removed, the script may still run in some shells, but it is not guaranteed to execute with the correct interpreter.
---

# Task 2: Variables
!/bin/bash

NAME='Rahul Thakkar'
ROLE='Devops Engineer'

echo "Hello I am $NAME and I am a $ROLE"
---

## Task 3: User Input with read
#!/bin/bash

read -p "Enter your name: " name
read -p "Enter your favourite tool: " tool

echo "Hello $name, your favourite tool is $tool"

---

## Task 4: If-Else Conditions
#!/bin/bash

read -p "Enter your number: " number

if [ "$number" -gt 0 ]; then
	echo "$number is positive"
elif [ "$number" -lt 0  ]; then
	echo "$number is negative"
else
	echo "$number is zero"
	
fi

2
#!/bin/bash

read -p "Enter file name: " file

if [ -f "$file" ]; then
	echo "File exits"
else
	echo "File not exits"
fi

---

## Task 5: Combine Everything
#!/bin/bash

read -p "Enter service name: " service
read -p "Do you want to check the status?(y/n): " user_side

if [ "$user_side" = "y" ]; then
	echo "checking the service"
	if systemctl status $service ; then
		echo "$service is active"
	fi
elif [ "$user_side" = "n" ]; then
	echo "skipped"
fi

---

## What I Learned

The shebang (#!/bin/bash) defines the interpreter used to execute the script.

Variables and user input (read) allow scripts to become interactive.

Conditional statements (if-else) help automate decision-making in scripts.


