# Day 14 – Networking Fundamentals & Hands-on Checks

## Overview
Today I practiced core networking fundamentals and essential troubleshooting commands used in real DevOps environments.

---

# Quick Concepts

## OSI vs TCP/IP

### OSI Model (L1–L7)
1. Physical  
2. Data Link  
3. Network  
4. Transport  
5. Session  
6. Presentation  
7. Application  

### TCP/IP Stack
- Link  
- Internet  
- Transport  
- Application  

### Where Things Sit

- IP → Internet Layer  
- TCP/UDP → Transport Layer  
- HTTP/HTTPS → Application Layer  
- DNS → Application Layer  

### Real Example

`curl https://example.com`

Application (HTTP) → Transport (TCP) → Internet (IP) → Link Layer  

---

# Hands-on Commands & Observations

## 1️⃣ Identity

hostname -I  

Observation: Displays my system’s assigned IP address.

---

## 2️⃣ Reachability

ping google.com  

Observation: Received replies with low latency and 0% packet loss.

---

## 3️⃣ Path Check

traceroute google.com  

Observation: Multiple hops shown; no major timeouts.

---

## 4️⃣ Open Ports

ss -tulpn  

Observation: Found SSH service listening on port 22.

---

## 5️⃣ DNS Resolution

dig google.com  

Observation: Domain successfully resolved to public IP.

---

## 6️⃣ HTTP Check

curl -I https://google.com  

Observation: Received HTTP 200 OK response.

---

## 7️⃣ Connection Snapshot

netstat -an | head  

Observation: Saw LISTEN and ESTABLISHED connections.

---

# Mini Task – Port Probe

Identified SSH running on port 22.

Tested using:

nc -zv localhost 22  

Result: Port reachable.

If not reachable, next checks:
- systemctl status ssh  
- firewall rules (ufw status / iptables -L)

---

# Reflection

### Fastest Signal When Something Breaks?
ping or curl gives the fastest initial signal.

### If DNS Fails?
Inspect Application layer → Check DNS resolution (dig, nslookup).

### If HTTP 500 Error?
Check Application logs and backend service status.

### Two Follow-up Checks in Real Incident
1. systemctl status <service>  
2. journalctl -u <service>  

---

# What I Learned

- Networking troubleshooting follows a layered approach.
- Small commands provide powerful insights.
- Always verify DNS, reachability, and service status step by step.

---
