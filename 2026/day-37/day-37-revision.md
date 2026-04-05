# Day 37 – Docker Revision

## ✅ Self Assessment

✔ Can do confidently:
- Run containers (interactive & detached)
- Manage containers & images
- Write Dockerfile
- Build & tag images
- Use volumes & networks
- Write docker-compose.yml
- Use env variables
- Push to Docker Hub

⚠️ Need improvement:
- CMD vs ENTRYPOINT (edge cases)
- Multi-stage optimization strategies

❌ Haven’t done:
- None

---

## ⚡ Quick-Fire Answers

**1. Image vs Container**
Image = blueprint, Container = running instance.

**2. Data after container removal**
Lost unless stored in volume/bind mount.

**3. Container communication**
Using container name via Docker DNS on same network.

**4. docker compose down -v**
Removes containers + volumes (data deleted).

**5. Multi-stage builds**
Reduce image size by keeping only final artifacts.

**6. COPY vs ADD**
COPY = simple file copy  
ADD = copy + extract + URL support

**7. -p 8080:80**
Host port 8080 → Container port 80

**8. Disk usage**
docker system df

---

## 🔁 Revisited Topics

### 1. CMD vs ENTRYPOINT
- CMD can be overridden
- ENTRYPOINT is fixed

### 2. Multi-stage builds
- Used builder + final image
- Reduced size significantly

---

## 🧠 Key Learnings

- Containers are ephemeral → use volumes
- Compose simplifies multi-container setups
- Image layers improve caching & build speed
- Multi-stage builds are critical for production
