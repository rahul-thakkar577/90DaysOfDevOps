# Day 19 – Shell Scripting Project: Log Rotation, Backup & Crontab

## Overview
Today I applied everything learned in Days 16–18 to build real-world automation scripts.

Projects implemented:
- Log rotation automation
- Server backup script
- Cron scheduling
- Maintenance automation script

---

# Task 1 – Log Rotation Script

## Script: log_rotate.sh
#!/bin/bash

log_dir=$1

if [ ! -d "$log_dir" ]; then
	echo "Error! directory doesn't exists"
	exit 1
fi

function rotation_log () {
	echo "-------Starting Log Rotation--------"				
	compressed=$(find "$log_dir" -type f -name "*.log" -mtime +7 -exec gzip {} \; -print | wc -l)
	delete=$(find "$log_dir" -type f -name "*.gz" -mtime +30 -delete -print |wc -l)

	echo "Total Logs compressed $compressed"
	echo "Old Logs Deleted $delete"	
}

# Task 2  Server Backup Script
## Script: backup.sh

#!/bin/bash

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 source_directory backup_destination"
    exit 1
fi


if [ ! -d "$1" ]; then
    echo "Error: Source directory '$1' not found"
    exit 1
fi


SOURCE="$1"
DEST="$2"
DATE=$(date +%Y-%m-%d-%s)
BACKUP_FILE="backup-$DATE.tar.gz"

mkdir -p "$DEST"

tar -czf "$DEST/$BACKUP_FILE" "$SOURCE" 2>/dev/null

if [ -f "$DEST/$BACKUP_FILE" ]; then
    SIZE=$(du -h "$DEST/$BACKUP_FILE" | cut -f1)
    echo "✓ Backup created: $BACKUP_FILE ($SIZE)"
else
    echo "✗ Backup failed"
    exit 1
fi

find "$DEST" -name "backup-*.tar.gz" -mtime +14 -delete 2>/dev/null
echo "✓ Cleaned up old backups"

exit 0
rotation_log $log_dir

## Task 3 – Crontab
Current Scheduled Jobs
crontab -l
Cron Syntax
* * * * * command
│ │ │ │ │
│ │ │ │ └── Day of week
│ │ │ └──── Month
│ │ └────── Day of month
│ └──────── Hour
└────────── Minute
Cron Entries

Run log rotation every day at 2 AM

0 2 * * * /path/log_rotate.sh /var/log/myapp

Run backup every Sunday at 3 AM

0 3 * * 0 /path/backup.sh /home/rahul /backup

Run health check every 5 minutes

*/5 * * * * /path/health_check.sh

# Task 4 Maintenance Script
## Script: maintenance.sh

#!/bin/bash

LOGFILE="/var/log/maintenance.log"

log_message() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') : $1" >> $LOGFILE
}

log_rotate() {
  find /var/log -name "*.log" -mtime +7 -exec gzip {} \;
}

backup_home() {
  tar -czf /backup/home-backup-$(date +%F).tar.gz /home
}

log_message "Maintenance started"

log_rotate
log_message "Log rotation completed"

backup_home
log_message "Backup completed"

log_message "Maintenance finished"
