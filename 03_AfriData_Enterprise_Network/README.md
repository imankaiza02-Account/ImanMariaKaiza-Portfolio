# Enterprise Network Design for AfriData Solutions LTD (DBIT COMPUTER NETWORKS CASE STUDY)

## Project Overview
AfriData Solutions Ltd is a rapidly expanding company providing data analytics and cloud-based business intelligence services. The company has three branches located in Nairobi (Headquarters), Kisumu, and Mombasa, connected via leased lines and the public internet. This project details the design, simulation, and critical evaluation of a new enterprise-level computer network to enhance reliability, security, scalability, and performance.

## The Problem
Due to growth, the company faces the following challenges: Frequent network downtime affects business operations. Security incidents involving unauthorized access attempts. Inefficient bandwidth utilization during peak hours. Poor performance of remote access and cloud synchronization.

## The Solution
The proposed architecture employs a robust hybrid topology, a structured IP addressing scheme, and dynamic routing to establish a reliable, secure, and scalable foundation for the company's operations across the Nairobi, Kisumu, and Mombasa branches. 

The logical and physical topology selected for this design is a hybrid star-mesh configuration. Within each branch location, a star topology is implemented, where all end devices connect centrally to a layer-2 switch, which in turn connects to the local router. For the Wide Area Network (WAN) interconnecting the three branches, a partial mesh topology is utilized. Each branch router is connected to the other two via dedicated serial links, creating redundant pathways. This design provides significant resilience; for instance, if the direct link between Nairobi and Kisumu fails, traffic can automatically reroute through Mombasa, thereby directly addressing the business challenge of frequent network downtime.

## My Contribution
This was a group project, and my specific contributions included:
* Protocol Analysis and OSI Layer Mapping: Determined the communication protocols and models section, explaining how the TCP/IP and OSI models guided the network design and mapping network services to their appropriate OSI layers. 
* Network Design: Assisted in the initial configuration and simulation stages of the Cisco Packet Tracer model, contributing to the setup of the hybrid topology and IP addressing scheme.

## Key Technical Implementations
* IP Addressing & VLSM: A hierarchical IP addressing scheme using Variable Length Subnet Masking (VLSM) to ensure efficient resource allocation. Each branch has been allocated a dedicated /24 network. WAN links use /30 subnets to conserve address space.
* Dynamic Routing (OSPF): The Open Shortest Path First (OSPF) dynamic routing protocol was implemented for its fast convergence and scalability. All routers are configured within a single OSPF area (Area 0), allowing each router to dynamically learn the routes to all subnets across the enterprise.
* Layered Security Architecture: Implemented Next-Generation Firewalls (NGFW), Virtual Private Networks (VPNs) for secure inter-branch and remote access, SSL/TLS encryption for internal web applications, and Intrusion Detection and Prevention Systems (IDS/IPS). 
* Network Administration: Utilized Wireshark for deep packet inspection, identifying anomalies such as ICMP "Destination Unreachable" messages and duplicate ARP requests to diagnose network misconfigurations.

## Technical Stack
* Simulation Environment: Cisco Packet Tracer
* Network Analysis: Wireshark
* Protocols: TCP/IP, OSPF, DHCP, DNS, HTTP/HTTPS, FTP

## Repository Guide
This directory contains all the evidence and documentation of the AfriData network design.
* Documentation: The full group report covering the network architecture, security plan, and critical evaluation.
* Network Diagrams: This contains the final Cisco Packet Tracer network topology diagram designed for AfriData.
* Simulation Model: This contains the Cisco Packet Tracer simulation file.
*(Note: Depending on your version of Packet Tracer, the file may experience rendering glitches; however, all routing configurations are evidenced by the CLI outputs documented in the report).*
