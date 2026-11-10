# Smart Bicycle Brake Light (SBBL)

## Project Overview
The Smart Bicycle Brake Light (SBBL) is an intelligent, Arduino-based electronic system designed and simulated in Tinkercad. It was developed as a solution to the communication gap between cyclists and other road users, and it provides an automatic, highly responsive brake light system that signals a cyclist's braking activity and stationary state, as well as automatically adjusting its brightness based on the ambient light.

## The Problem
Even though conventional bicycle rear lights improve visibility, they operate in continuous or flashing modes that often don't explicitly communicate when a cyclist is braking. Moreover, existing bicycle brake light solutions that rely on accelerometers do not accurately determine a braking event; they suffer from delayed reactions and output false positives when the bicycle moves over potholes or rough gravel instead of actually braking.

## The Solution
The SBBL system addresses these challenges by integrating direct mechanical brake detection with four state-machine modes, including CYCLING, BRAKING, STATIONARY, and DEACTIVATED: each state is independent, and transitions are strictly governed by input conditions to ensure predictable and reliable behaviour. When braking occurs, the system instantly illuminates the LEDs. If the bicycle comes to a stop, the system transitions to the STATIONARY mode, keeping the brake light illuminated to warn approaching vehicles until the cyclist either manually deactivates the light or resumes cycling. Furthermore, to counteract the issue with unadaptive brightness, an ambient-light sensor (LDR) and Pulse-Width Modulation (PWM) control automatically adjust the LEDs' intensity based on surrounding light levels: ensuring maximum visibility in daylight, dusk, and nighttime.

## My Contribution
This project was independently envisioned, designed, coded, and tested from scratch. My work included designing the circuit, writing the C++ code, conducting modular tests for all the 5 phases, and documenting the process, as well as outlining the transition from a simulation to a physical prototype to a commercial product.

## Key Features 
*   **Phase 1: Core Brake Detection: Dual pushbutton switches, that represent the brake levers, instantly trigger the brake light when pressed.
*   **Phase 2: Motion State Recognition and Manual Override: A slide switch represents the moving and stationary states, and when the bike stops the LED illumination remains on indefinitely until the Manual OFF button is pressed or movement commences.
*   **Phase 3: Ambient-light Adaptation: An LDR determines the surrounding light levels and the PWM control adjusts the LEDs’ brightness, however, LDR doesn’t activate the LEDs, it just determines how intense their light is.
*   **Phase 4: Power Management: AVR sleep mode is implemented to save power, and Interrupt Service Routines (ISRs) wake-up the microcontroller when brakes are activated or a motion state change occurs.
*   **Phase 5: LDR Fault Detection: LDR readings are monitored, and if an abnormal one is detected for more than 2000 milliseconds, the LEDs will illuminate at the maximum light level when needed.

## Technical Stack
*   **Microcontroller: Arduino Uno
*   **Simulation Environment: Tinkercad Circuits
*   **Programming Language: C++

## Repository Guide
This directory contains all the evidence and documentation of the SBBL system.
*   **Code: This contains all the C++ code used in the development of the system, including the individual phase code before integration.
*   **Documentation: The full engineering report covering the background, methodology, testing results, and real-world application.
*   **System Design & Components: This contains the components of the system, the circuit schematics, and the circuit configuration.
*   **Videos: This contains screen recordings of all the phases and the final SBBL system.
