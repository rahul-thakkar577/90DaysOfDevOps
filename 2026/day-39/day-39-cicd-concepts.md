# Day 39 – CI/CD Concepts

## 🚨 Task 1: The Problem

### What can go wrong?
- Code conflicts between developers
- Bugs introduced into production
- Manual deployment errors
- Downtime due to incorrect builds
- No proper testing before release

### "It works on my machine"
This means the code runs fine on a developer’s local system but fails in other environments due to differences in dependencies, configurations, or OS.

👉 This is a real problem because production environments differ from local setups.

### Manual deployment frequency
- Safely: 1–2 times per day (at most)
- More than that = high risk of errors and instability

---

## 🔄 Task 2: CI vs CD

### Continuous Integration (CI)
Developers frequently merge code into a shared repository. Automated builds and tests run on every commit to detect issues early.

✅ Example:
A developer pushes code → tests run automatically → build fails if tests fail.

---

### Continuous Delivery (CD)
Code is automatically tested and prepared for release, but deployment to production requires manual approval.

✅ Example:
Code passes all tests → ready to deploy → team clicks "Deploy"

---

### Continuous Deployment (CD)
Every change that passes tests is automatically deployed to production without human intervention.

✅ Example:
Code passes tests → automatically deployed to live users

---

## ⚙️ Task 3: Pipeline Anatomy

- **Trigger** → Starts pipeline (e.g., git push)
- **Stage** → Major phase (build, test, deploy)
- **Job** → Task within a stage
- **Step** → Single command (e.g., npm install)
- **Runner** → Machine executing the job
- **Artifact** → Output (e.g., Docker image, build files)

---

## 🧩 Task 4: CI/CD Pipeline Diagram
Developer Push (GitHub)
│
▼
🔹 Stage 1: Build
- Install dependencies
- Build application
│
▼
🔹 Stage 2: Test
- Run unit tests
- Run integration tests
│
▼
🔹 Stage 3: Dockerize
- Build Docker image
- Push to Docker Hub
│
▼
🔹 Stage 4: Deploy (Staging)
- Pull Docker image
- Run container on server


---

## 🌍 Task 5: Explore in the Wild

Repository: Kubernetes (example)

### Trigger
- On push / pull request

### Jobs
- Multiple jobs (build, test, validation)

### What it does
- Runs tests
- Validates code
- Ensures stability before merging

---

## 🧠 Key Takeaways

- CI/CD automates software delivery
- Reduces human errors
- Makes deployments faster and safer
- Pipeline failures help catch issues early

---

# 🚀 Conclusion
CI/CD is not just a tool but a practice that improves development speed, quality, and reliability.
