# 🐳 Docker Cheat Sheet

## 📦 Container Commands
```bash
docker run -it ubuntu bash      # Run container interactively
docker run -d nginx             # Run container in detached mode
docker ps                       # List running containers
docker ps -a                    # List all containers
docker stop <id>                # Stop container
docker rm <id>                  # Remove container
docker exec -it <id> bash       # Enter running container
docker logs <id>                # View logs

---
🖼️Image commands
docker pull nginx               # Download image
docker build -t app:v1 .        # Build image
docker images                   # List images
docker rmi <id>                 # Remove image
docker tag app:v1 user/app:v1   # Tag image
docker push user/app:v1         # Push to Docker Hub

---
💾 Volume Commands
docker volume create myvol      # Create volume
docker volume ls                # List volumes
docker volume inspect myvol     # Inspect volume
docker volume rm myvol          # Remove volume

---
🌐 Network Commands
docker network create mynet     # Create network
docker network ls               # List networks
docker network inspect mynet    # Inspect network
docker network connect mynet <container>

---
⚙️ Docker Compose
docker compose up -d            # Start services
docker compose down             # Stop & remove services
docker compose ps               # List services
docker compose logs -f          # View logs
docker compose build            # Build services

---
🧹 Cleanup Commands
docker system prune -a          # Remove unused data
docker system df               # Check disk usage

---
📝 Dockerfile Instructions
FROM        # Base image
RUN         # Execute commands
COPY        # Copy files
WORKDIR     # Set working directory
EXPOSE      # Expose port
CMD         # Default command
ENTRYPOINT  # Fixed command
