# Day 09 – Linux User & Group Management Challenge

## Users & Groups Created

### Users:
- tokyo
- berlin
- professor
- nairobi

### Groups:
- developers
- admins
- project-team

---

## Group Assignments

tokyo → developers, project-team  
berlin → developers, admins  
professor → admins  
nairobi → project-team  

---

## Directories Created

/opt/dev-project  
Group: developers  
Permissions: 775  

/opt/team-workspace  
Group: project-team  
Permissions: 775  

---

## Commands Used

sudo useradd -m tokyo  
sudo passwd tokyo  

sudo groupadd developers  
sudo groupadd admins  

sudo usermod -aG developers tokyo  
sudo usermod -aG admins professor  

sudo mkdir -p /opt/dev-project  
sudo chgrp developers /opt/dev-project  
sudo chmod 775 /opt/dev-project  

sudo mkdir -p /opt/team-workspace  
sudo chgrp project-team /opt/team-workspace  
sudo chmod 775 /opt/team-workspace  

---

## What I Learned

• How to create and manage Linux users  
• How to assign users to groups  
• How to manage shared directory permissions for teams  

---

## Outcome

Successfully created users, groups, and shared directories with proper permissions. Tested access using multiple users to simulate real DevOps team collaboration.
