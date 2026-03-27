# Day 31 – Dockerfile: Build Your Own Images

## 1. First Dockerfile (Ubuntu + Curl)

### Dockerfile
FROM ubuntu

RUN apt-get update && apt-get install curl -y

CMD ["echo", "Hello from my custom image!"]
---

## 2. Dockerfile Instructions
### Dockerfile:

FROM ubuntu

WORKDIR

COPY

RUN apt-get update && apt-get install vim -y

EXPOSE 8080

CMD ["echo","Dockerfile Instructions !"]
---

## 3. CMD vs ENTRYPOINT

### CMD
FROM ubuntu
CMD ["echo","Hello Rahul"]

Run:
docker run ubuntu

👉 Output:
Hello Rahul

### ENTRYPOINT

FROM ubuntu
ENTRYPOINT ["echo"]

Run:

docker run image-name hello
docker run image-name Rahul

👉 Output:
hello
Rahul

Difference
CMD → Default, can be overridden
ENTRYPOINT → Fixed command, arguments appended
---

## 4. Build a Simple Web App Image
### Dockerfile:

FROM nginx:alpine

WORKDIR /app

COPY index.html /usr/share/nginx/html

EXPOSE 80
---

## 5. .dockerignore

node_modules
.git
*.md
.env

purpose:

- Reduces image size
- Improves build speed
- Avoids unnecessary files
---

## 6. Buid Optimization

Observation:
- Docker caches layers
- If a layer doesn't change → reused

Best Practice
- Put frequently changing code at the bottom
- Keep dependencies installation at top