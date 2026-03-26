# Day 30 – Docker Images & Container Lifecycle

## 1. Docker Images
### Pull Images
docker pull nginx
docker pull ubuntu
docker pull alpine

docker images
Observation
ubuntu → larger size (full OS)
alpine → very small (8MB)
nginx → moderate size

Why Alpine is smaller?

Alpine is a minimal Linux distribution with fewer packages, making it lightweight and faster.

Inspect Image
docker inspect nginx

Shows:
- Image ID
- Layers
- Environment variables
- Metadata

Remove Image
docker rmi alpine


## 2. Image Layers
docker image history nginx

Observation
- Each line represents a layer
- Some layers have size (actual data)
- Some layers show 0B (metadata/config)

What are layers?
==> Layers are read-only changes stacked on top of each other to build an image.

Why Docker uses layers?
- Faster builds using caching
- Reusability across images
- Efficient storage

## 3. Container Lifecycle
Create container (without starting)
docker create nginx

Start container
docker start <container-id>
Pause container
docker pause <container-id>

Unpause container
docker unpause <container-id>

Stop container
docker stop <container-id>

Restart container
docker restart <container-id>

Kill container
docker kill <container-id>

Remove container
docker rm <container-id>

Observed States
- created
- running
- paused
- exited
## 4. Working with Running Containers
Run Nginx in detached mode
docker run -d -p 80:80 --name my-nginx nginx
List Running Containers
docker ps

Observation (from practice):
- Multiple containers running:
-nginx
- ubuntu
- alpine

View Logs
docker logs my-nginx

Follow Logs (real-time)
docker logs  my-nginx

Exec into container
docker exec -it my-nginx bash

Inspect container
docker inspect my-nginx

Find:
- IP address
- Port mappings (0.0.0.0:80 → 80)
- Container configuration

## 5. Cleanup Commands
Stop all running containers
docker stop $(docker ps -q)

Remove all containers
docker rm $(docker ps -aq)

Remove unused images

docker image prune -a
Check Docker disk usage
docker system df

--- 
## 6. Real Practice Summary
Pulled nginx, ubuntu, and alpine images
Compared image sizes (alpine is smallest)
Explored image layers using docker image history
Ran multiple containers simultaneously
Verified running containers using docker ps
Worked with logs and container inspection
Understood container lifecycle states
Cleaned up containers and images

## 7. Key Learnings
Images are blueprints; containers are running instances
Layers make Docker efficient and fast
Alpine is preferred for lightweight containers
docker image history helps understand image build process
Managing container lifecycle is essential in DevOps