## App Chosen
Library Management System (PHP + MySQL)

## Why
Real-world CRUD + file uploads + DB integration

## Dockerfile Explanation
- Base image: php:8.2-apache
- Installed extensions: pdo_mysql
- Enabled Apache rewrite
- Copied app files
- Set permissions

## Challenges
- Image not loading due to path mismatch
- Fixed DB + filesystem inconsistency
- Understood volume vs image behavior

## Final Image Size
~450MB

## Docker Hub
https://hub.docker.com/r/rahulthakkar555/smart-library-app

# LMS Docker Project

## Run
docker-compose up --build

## Access
http://localhost:8009

## Tech Stack
- PHP
- MySQL
- Docker