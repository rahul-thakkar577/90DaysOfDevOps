# Day 13 – Linux Volume Management (LVM)

## Overview
Today I learned how to manage storage using LVM (Logical Volume Manager).
I practiced creating Physical Volumes, Volume Groups, Logical Volumes, mounting them, and extending storage dynamically.

---

## Task 1: Check Current Storage

Commands used:

lsblk  
pvs  
vgs  
lvs  
df -h  

---

## Task 2: Create Physical Volume

pvcreate /dev/sdb
pvs

---

## Task 3: Create Volume Group

vgcreate devops-vg /dev/sdb
vgs

---

## Task 4: Create Logical Volume

lvcreate -L 500M -n app-data devops-vg
lvs


---

## Task 5: Format and Mount

mkfs.ext4 /dev/devops-vg/app-data
mkdir -p /mnt/app-data
mount /dev/devops-vg/app-data /mnt/app-data
df -h /mnt/app-data

---

## Task 6: Extend the Volume

lvextend -L +200M /dev/devops-vg/app-data
resize2fs /dev/devops-vg/app-data
df -h /mnt/app-data


## What I Learned

1. LVM allows flexible storage management without downtime.
2. Logical Volumes can be extended dynamically.
3. LVM structure hierarchy:
   Physical Volume (PV) → Volume Group (VG) → Logical Volume (LV)
