#!/bin/bash

LOGFILE=$1
DATE=$(date +%Y-%m-%d)
REPORT="log_report_$DATE.txt"

# ---- 1-Validation ----
if [ -z "$LOGFILE" ]; then
  echo "Usage: ./log_analyzer.sh <logfile>"
  exit 1
fi

if [ ! -f "$LOGFILE" ]; then
  echo "Error: Log file does not exist"
  exit 1
fi

echo "Analyzing log file: $LOGFILE"

TOTAL_LINES=$(wc -l < "$LOGFILE")

ERROR_COUNT=$(grep -Ei "ERROR|Failed" "$LOGFILE" | wc -l)

CRITICAL_EVENTS=$(grep -n "CRITICAL" "$LOGFILE")

TOP_ERRORS=$(grep "ERROR" "$LOGFILE" | \
awk '{$1=$2=$3=""; print}' | \
sort | uniq -c | sort -rn | head -5)

# ---- Print to Console ----

echo "Total lines: $TOTAL_LINES"
echo "Total errors: $ERROR_COUNT"

echo ""
echo "--- Critical Events ---"
echo "$CRITICAL_EVENTS"

echo ""
echo "--- Top 5 Error Messages ---"
echo "$TOP_ERRORS"

# ---- Generate Report ----

{
echo "=====================Log Analysis Report========================================="
echo "Date: $DATE"
echo "Log File: $LOGFILE"
echo "Total Lines: $TOTAL_LINES"
echo "Total Errors: $ERROR_COUNT"

echo ""
echo "---4  Top 5 Error Messages ---"
echo "$TOP_ERRORS"

echo ""
echo "--- Critical Events ---"
echo "$CRITICAL_EVENTS"

} > "$REPORT"

echo "Report generated: $REPORT"

# ----6 Optional Archive ----

mkdir -p archive
mv "$LOGFILE" archive/

echo "Log file moved to archive/"
