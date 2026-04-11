# Day 40 - First GitHub Actions Workflow

## Workflow File
(paste hello.yml)

## What I Learned
- CI/CD pipelines run automatically on push
- GitHub provides free runners
- YAML defines pipeline structure

## Concepts
on:
👉 Defines trigger (when pipeline runs)
jobs:
👉 Collection of tasks
runs-on:
👉 OS where job runs (Ubuntu runner)
steps:
👉 Sequence of commands
uses:
👉 Use prebuilt GitHub Actions
run:
👉 Execute shell commands
name:
👉 Label for step (visible in UI)
## Failure Testing
- Added "exit 1" → pipeline failed ❌
- Checked logs → identified error
- Fixed → pipeline passed ✅

## Output
(Screenshots added)