# Day 17 – Shell Scripting: Loops, Arguments & Error Handling

## Overview
Today I improved my shell scripting skills by learning **loops, command-line arguments, package automation, and basic error handling**.

---

# Task 1 – For Loop

## Script:1 for_loop.sh
#!/bin/bash

for fruits in Apple Watermelon Kiwi Orange Mango
do
	echo "$fruits"
done

---
## Script:2 count.sh

#!/bin/bash

for fruits in Apple Watermelon Kiwi Orange Mango
do
	echo "$fruits"
done

# Task 2 While Loop

#!/bin/bash

read -p "Enter the number: " count
echo "Countdown Starts!!"
while [ "$count" -gt 0 ]
do
	echo "$count"
	count=$((count - 1))
done
echo "BOOM"

# Task 3 Command Line Arguments
## Script:1 greet.sh

!/bin/bash

if [ -z "$1" ]; then
	echo "Usage:./greet.sh <argument pass>"
else
	echo "Hello $1"
fi

echo "Total number of arguments: $#"
echo "Show all arguments: $@"
echo "Name of the script: $0"

# Task 4 Install Packages via Script

#!/bin/bash

### Define the list of packages
PACKAGES=("nginx" "curl" "docker")

echo "Starting package installation check..."

for PKG in "${PACKAGES[@]}"; do
    if dpkg -s "$PKG" >/dev/null 2>&1; then
        echo "[SKIP] $PKG is already installed."
        exit 1

	fi

        # Update and install (requires sudo)
        sudo apt-get update -y && sudo apt-get install -y "$PKG"
        
        # Verify result
        if [ $? -eq 0 ]; then
            echo "[SUCCESS] $PKG has been installed."
        fi
done
# Task 5 Error Handling

#!/bin/bash
set -e

mkdir /tmp/devops-test || echo "Directory already exists or could not be created"


cd /tmp/devops-test ||  echo "Failed to change directory" 


touch test_file.txt || echo "Failed to create file"

echo "Script completed successfully."

## What I Learned

Loops (for & while) help automate repetitive tasks in scripts.
Command-line arguments make scripts dynamic and reusable.
Error handling and root checks are important for writing safe production scripts.

