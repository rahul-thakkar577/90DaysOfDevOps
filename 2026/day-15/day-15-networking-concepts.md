# Day 15 – Networking Concepts: DNS, IP, Subnets & Ports

## Overview
Today I strengthened my understanding of core networking building blocks that every DevOps engineer must know — DNS, IP addressing, CIDR, subnetting, and ports.

---

# Task 1 – DNS: How Names Become IPs

When I type `google.com` in a browser:

1. The system checks local DNS cache.
2. If not found, it queries the configured DNS resolver.
3. DNS resolver contacts root → TLD → authoritative server.
4. The domain is resolved to an IP address (A/AAAA record).

## DNS Record Types

A → Maps domain to IPv4 address  
AAAA → Maps domain to IPv6 address  
CNAME → Alias of another domain  
MX → Mail server record  
NS → Name server record  

### Command:

dig google.com

### Observation:

- A record returned public IPv4 address.
- TTL shows how long the result is cached.

---

# Task 2 – IP Addressing

## What is IPv4?

An IPv4 address is a 32-bit number written in dotted decimal format.
Example: 192.168.1.10

It has 4 octets (0–255 each).

## Public vs Private IP

Public IP → Accessible over the internet (Example: 8.8.8.8)  
Private IP → Used inside internal networks (Example: 192.168.1.5)

## Private IP Ranges

10.0.0.0 – 10.255.255.255  
172.16.0.0 – 172.31.255.255  
192.168.0.0 – 192.168.255.255  

### Command:

ip addr show

### Observation:

My system IP falls under private IP range.

---

# Task 3 – CIDR & Subnetting

## What does /24 mean?

192.168.1.0/24 means:
24 bits are network bits.
Remaining 8 bits are for hosts.

## Usable Hosts

/24 → 254 usable hosts  
/16 → 65,534 usable hosts  
/28 → 14 usable hosts  

## Why Do We Subnet?

- Better network organization
- Security isolation
- Efficient IP usage
- Reduced broadcast traffic

## CIDR Table

| CIDR | Subnet Mask       | Total IPs | Usable Hosts |
|------|------------------|-----------|--------------|
| /24  | 255.255.255.0    | 256       | 254          |
| /16  | 255.255.0.0      | 65,536    | 65,534       |
| /28  | 255.255.255.240  | 16        | 14           |

---

# Task 4 – Ports: The Doors to Services

## What is a Port?

A port is a logical endpoint that allows multiple services to run on the same IP address.

## Common Ports

22 → SSH  
80 → HTTP  
443 → HTTPS  
53 → DNS  
3306 → MySQL  
6379 → Redis  
27017 → MongoDB  

### Command:

ss -tulpn

### Observation:

- Port 22 → SSH service
- Port 631 → CUPS printing service (example)

---

# Task 5 – Putting It Together

## curl http://myapp.com:8080

- DNS resolves domain to IP.
- TCP connects to port 8080.
- HTTP request sent at application layer.

## App can't reach DB at 10.0.1.50:3306

First checks:
- Is port 3306 open?
- Is MySQL service running?
- Network firewall/security group rules?

---

# What I Learned

1. DNS is the backbone of internet name resolution.
2. CIDR helps efficiently manage IP allocation.
3. Ports allow multiple services to operate on a single machine.

---
