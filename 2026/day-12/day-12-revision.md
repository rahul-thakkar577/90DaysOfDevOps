# Day 12 – Breather & Revision (Days 01–11)

## Revision Notes

### Mindset & Plan
Revisited my Day 01 learning plan.
Goals are aligned with becoming a strong DevOps Engineer.
Focus remains on mastering Linux fundamentals before moving deeper into cloud & CI/CD.

---

### Processes & Services Check

Commands rerun:
ps aux | head -5  
systemctl status docker  
journalctl -u docker -n 20  

Observation:
Docker service is active and logs show recent activity.

---

### File Skills Practice

Practiced:
echo "revision test" >> notes.txt  
chmod 755 script.sh  
sudo chown tokyo:vault-team access-codes.txt  

---

### Cheat Sheet – 5 Go-To Commands

1. ls -l  
2. systemctl status  
3. journalctl -u service  
4. chmod  
5. chown  

---

### User/Group Sanity Check

Created a test file and changed ownership:
sudo chown professor:planners test.txt

Verified using:
ls -l test.txt

---

## Mini Self-Check

### 1. Which 3 commands save you most time?
- ls -l → Quick permission check
- systemctl status → Service health
- journalctl -u → Log troubleshooting

### 2. How to check if a service is healthy?
- systemctl status docker
- ps aux | grep docker
- journalctl -u docker -n 20

### 3. How to safely change ownership?
Example:
sudo chown -R user:group directory/

### 4. Focus for next 3 days
- Stronger shell scripting practice
- More Docker hands-on
- Networking command mastery

---

## Key Takeaway

Consistency is building confidence.
Linux fundamentals are becoming more natural each day.
