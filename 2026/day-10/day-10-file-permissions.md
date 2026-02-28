# Day 10 – File Permissions & File Operations Challenge

## Files Created

- devops.txt – empty file created using touch
- notes.txt – created with content using echo
- script.sh – shell script created using vim

---

## Permission Changes

### Before changes
-rw-rw-r-- devops.txt  
-rw-rw-r-- notes.txt  
-rw-rw-r-- script.sh  

### After changes

script.sh made executable:
-rwxrwxr-x script.sh  

devops.txt set to read-only:
-r--r--r-- devops.txt  

notes.txt permission set to 640:
-rw-r----- notes.txt  

project directory created with permission 755:
drwxr-xr-x project/

---

## Commands Used

### File creation
touch devops.txt  
echo "This is my DevOps notes file" > notes.txt  
vim script.sh  

### Reading files
cat notes.txt  
vim -R script.sh  
head -n 5 /etc/passwd  
tail -n 5 /etc/passwd  

### Permission changes
chmod +x script.sh  
chmod a-w devops.txt  
chmod 640 notes.txt  

### Directory creation
mkdir project  
chmod 755 project  

### Testing permissions
echo "test" >> devops.txt  
./script.sh  

---

## Test Results

Attempt to write to read-only file resulted in:
Permission denied

Attempt to execute without execute permission resulted in:
Permission denied

---

## What I Learned

- Linux file permissions control access for owner, group, and others
- chmod command is used to modify file and directory permissions
- Execute permission is required to run shell scripts
- Proper permission management is critical for system security
- Permission errors help prevent unauthorized access or modification

---
