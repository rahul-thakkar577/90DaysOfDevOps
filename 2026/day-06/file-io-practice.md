# Day 06 – Linux Fundamentals: File Read & Write Practice

## Objective
Practice creating, writing, appending, and reading text files using basic Linux commands.

---

## Step 1: Create File

Command:
touch notes.txt

Purpose:
Creates an empty file named notes.txt

---

## Step 2: Write to File

Command:
echo "Linux is powerful" > notes.txt

Purpose:
Writes text into the file (overwrites existing content)

---

## Step 3: Append to File

Command:
echo "DevOps uses Linux daily" >> notes.txt

Purpose:
Appends new line to existing file

---

## Step 4: Write & Display Using tee

Command:
echo "Practice makes perfect" | tee -a notes.txt

Purpose:
Writes to file and displays output at same time

---

## Step 5: Read Full File

Command:
cat notes.txt

Purpose:
Displays full file content

---

## Step 6: Read First 2 Lines

Command:
head -n 2 notes.txt

Purpose:
Shows first 2 lines of file

---

## Step 7: Read Last 2 Lines

Command:
tail -n 2 notes.txt

Purpose:
Shows last 2 lines of file

---

## What I Learned

- > overwrites content
- >> appends content
- tee writes and displays output
- cat, head, tail help in quick log/file reading

File handling is essential for logs, configs, and automation in DevOps.
