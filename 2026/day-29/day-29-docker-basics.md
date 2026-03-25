# Day 29 – Introduction to Docker

## 1. What is a Container?

A container is a lightweight environment that packages an application along with its dependencies so it runs consistently across systems.

### Why containers?
- Solve "it works on my machine" problem
- Lightweight and fast
- Easy to deploy and scale

---

## 2. Containers vs Virtual Machines

| Feature | Containers | Virtual Machines |
|--------|-----------|-----------------|
| OS | Share host OS | Full OS |
| Size | Lightweight | Heavy |
| Startup | Fast | Slow |
| Performance | High | Moderate |

---

## 3. Docker Architecture

Docker consists of:

- Docker Client → CLI (docker commands)
- Docker Daemon → Manages containers
- Images → Blueprint of applications
- Containers → Running instances
- Registry → Stores images (Docker Hub)

Flow:
Client → Daemon → Pull Image → Run Container

---

## 4. Installation & Verification

```bash
docker --version

5. Run First Container
docker run hello-world

This pulls the image from Docker Hub and runs it.

6. Run Nginx Container
docker run -d -p 80:80 nginx

Access in browser:
http://localhost

7. Access Container (Interactive Work)
docker exec -it <container-id> bash

Inside container:

cd /usr/share/nginx/html
ls
8. Modify Website Inside Container
apt-get update
apt-get install vim -y
vim index.html
Edited HTML content and verified changes in browser.


9. List Containers

Running containers: docker ps
All containers: docker ps -a

10. Stop and Remove Container

docker stop <container-id>
docker rm <container-id>


11. Key Learnings
Containers are lightweight and fast
Nginx container can serve web pages easily
We can access and modify container file system
Changes inside container reflect immediately
Docker helps in consistent deployment


12. Real Practice Summary

Ran Nginx container
Accessed container using docker exec
Navigated to /usr/share/nginx/html
Modified index.html
Installed vim inside container
Verified changes on browser
Stopped and removed container successfully