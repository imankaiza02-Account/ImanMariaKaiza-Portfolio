// SMART BICYCLE BRAKE LIGHT (SBBL)
// PHASE 1: BRAKE DETECTION
// PHASE 2: AUTOMATIC STATIONARY LIGHT AND MANUAL OFF OVERRIDE BUTTON
// PHASE 3: INTELLIGENT LIGHTING MANAGEMENT  
// PHASE 4: POWER MANAGEMENT AND LOW POWER OPERATION

// Including the AVR Libraries
#include <avr/sleep.h>
// Provides the functions required to configure and control the microcontrollers sleep mode
#include <avr/interrupt.h>
// Provides the functions required to configure and manage interrupts

const int brakePin = 2;
const int motionPin = 3;
const int offButtonPin = 4;

const int ledPin1 = 9;
const int ledPin2 = 10;
const int ledPin3 = 11;

const int lightSensorPin = A0;
// Identifies the analogue pin connected to the LDR

const int daylightThreshold = 620;
// This is the lowest boundary for the daylight range
/* Since it is the starting boundary for the daylight range,
it distinguishes daylight from the other lighting conditions.
Any reading below this boundary will fall under either dusk
or nighttime, while readings at or above this boundary
will fall under the daylight range */

const int nightThreshold = 270;
// This is the highest boundary for the nighttime range
/* Since it is the upper boundary for the nighttime range,
it distinguishes nighttime from the other lighting conditions.
Any reading above this boundary will fall under either dusk
or daylight, while readings at or below this boundary
will fall under the nighttime range */
// Dusk will fall between these two boundaries: 271 - 619

const int daylightBrightness = 180;
// Sets the LED brightness during daylight
const int duskBrightness = 218;
// Increases the LED brightness during dusk
const int nightBrightness = 255;
// Sets the highest controlled LED brightness during nighttime
/* This section introduce PWM (Pulse Width Modulation), 
where we will use the 'analogWrite()' function to control the LED's brightness.
The three pins, 9, 10, and 11: support PWM on the Arduino */

bool brakingEventSeen = false;
// Records whether a braking event has occurred
// Initially, its value is 'false', meaning that the system has not detected a braking event

bool holdActive = false;
// Records whether the stationary hold is active

bool manualOff = false;
// Records whether the rider has manually switched off

volatile bool brakeWakeEvent = false;
// Records whether the brake button has triggered an interrupt wake-up event
// 'volatile' ensures that the variable is read again because it can be changed by an interruption
volatile bool motionWakeEvent = false;
// Records whether the motion switch has triggered an interrupt wake-up event

int previousMotionState = HIGH;
// Stores the bicycles previous motion state:
// 'HIGH' means it is moving
// 'LOW' means it is is stationary

int previousOffButtonState = HIGH;
// Stores the previous manual OFF button state
// 'HIGH' means it is released
// 'LOW' means it is pressed
// It is this way because of the use of 'INPUT_PULLUP'

void setup()
{
  // Initialising serial communication to monitor and test the sleep mode of the Arduino
  Serial.begin(9600);
  
  // Initialising braking as input
  pinMode(brakePin, INPUT_PULLUP);
  // Note: 'INPUT_PULLUP' enables the Arduino's internal pull-up resistor
  
  // Initialising motion as input
  pinMode(motionPin, INPUT);
  
  // Initialising the manual OFF button as input
  pinMode(offButtonPin, INPUT_PULLUP);
  
  // Intialising the LEDs as output
  pinMode(ledPin1, OUTPUT);
  pinMode(ledPin2, OUTPUT);
  pinMode(ledPin3, OUTPUT);
  
  // Reading the actual intial position of the motion switch
  previousMotionState = digitalRead(motionPin);
  // This records whether the bicycle initially starts in the moving (HIGH) or stationary (LOW) motion state 
  // The stationary hold will only activate after the Arduino detects a HIGH-to-LOW transition
  // Provided a braking event was previously recorded
  
  // Reading the actual intial state of the manual OFF button
  previousOffButtonState = digitalRead(offButtonPin);
  // This records whether the manual OFF button is initially pressed or not
  // It allows the Arduino to detect a new button press by comparing the previous button state with its curerent state
  
  // Configuring the external interrupt for the brake button
  attachInterrupt(digitalPinToInterrupt(brakePin), wakeOnBrake, FALLING);
  // Detects when the brake button changes from HIGH to LOW
  
  // Configuring the external interrupt for the motion switch
  attachInterrupt(digitalPinToInterrupt(motionPin), wakeOnMotion, CHANGE);
  // Detects whenever the motion switch changes between HIGH and LOW
  
}

// INTERRUPT SERVICE ROUTINES (USED TO WAKE THE ARDUINO FROM SLEEP)

// Interrupt service routine for detecting a brake button press
void wakeOnBrake() {
  brakeWakeEvent = true;
  // Records that a brake related wake up event has occurred
}

// Interrupt service routine for detecting a change in the motion switch
void wakeOnMotion() {
  motionWakeEvent = true;
  // Records that a motion related wake up event has occurred
}

// FUNCTION FOR ENTERING POWER-DOWN SLEEP MODE

void enterSleepMode(bool lightIsOn)
{
  if (digitalRead(brakePin) == LOW || lightIsOn){
    return;
  }
  // This conditional statement checks if a brake is pressed or if the LEDs are on
  // If either of these conditions are true, then the 'return' exits the function immediately

  set_sleep_mode(SLEEP_MODE_PWR_DOWN);
  // Configures the microcontroller to enter power down sleep mode, reducing the power consumption significantly

  cli();
  // Disables the global interrupts temporarily, to prevent an interrupt from occurring between the final safety check and the Arduino entering sleep

  if (digitalRead(brakePin) == HIGH && !lightIsOn && !brakeWakeEvent && !motionWakeEvent){
    // Printing this text before the Arduino attempts to 'sleep'
    Serial.println("Arduino attempting to enter sleep mode");
    
    sleep_enable();
    // Enables the sleep function
    sei(); 
    // Re-enables global interrupts
    sleep_cpu();
    // Puts the Arduino into sleep mode
    sleep_disable();
    // Disables the sleep function after the Arduino wakes up
    
    // Printing this text when the sleep function is disabled and the Arduino is 'awake'
    Serial.println("Arduino has woken up");
  }
  // This conditional statement performs a final safety check before sleeping. 
  // If all the conditions are true, then it will proceed to enter sleep mode to save power
  /* Conditions include checking if the brake is released, the LEDs are OFF, 
  no brake wake event has occurred, and no motion related event has occurred */
  
  else {
    sei();
  }
  // If the final safety check fails, the Arduino doesnt sleep, and the interrupts and re-enabled
}

void loop()
{
  // Clearing the interrupt wake-up flags

  cli();
  // Temporarily disables global interrupts
  
  // New variables to be used for the sleep mode simulation tests (using the Serial Monitor)
  bool brakeEvent = brakeWakeEvent;
  bool motionEvent = motionWakeEvent;

  brakeWakeEvent = false;
  motionWakeEvent = false;

  sei();
  // Re-enables global interrupts
  
  if (brakeEvent){
    Serial.println("WAKE UP: BRAKING DETECTED!!!");
  }
  
  if (motionEvent){
    Serial.println("Wake UP: MOTION CHANGE DETECTED!!!");
  }
  
  // Reading the surrounding light level
  int lightLevel = analogRead(lightSensorPin);
  // Measures the voltage at A0 and stores the resulting light reading
  
  // Reading the inputs
  bool braking = (digitalRead(brakePin) == LOW);
  // Note: Since we used 'INPUT_PULLUP' for the brakePin:
  // 'LOW' means a pressed button, or in our case braking
  // 'HIGH' means a brake button is released, so no braking
  
  int motionState = digitalRead(motionPin);
  bool stopped = (motionState == LOW);
  
  int offButtonState = digitalRead(offButtonPin);
  
  // Identifying the surrounding light conditions
  int ledBrightness;
  
  // Setting the LED brightness based off the boundaries for daylight and nighttime
  // If the reading is 620 or above, the Arduino selects daylight brightness
  if (lightLevel >= daylightThreshold){
    ledBrightness = daylightBrightness;
  }
  //  If the reading is 270 or below, the Arduino selects nighttime brightness
  else if (lightLevel <= nightThreshold){
    ledBrightness = nightBrightness;
  }
  // If neither condition is met, the Arduino selects dusk brightness
  // Since it is the remaining values between the nighttime highest boundary and daylight lowest boundary
  else {
    ledBrightness = duskBrightness;
  }
  
  // Detecting the bicycles transition from moving to stopping
  bool justStopped = (previousMotionState == HIGH && motionState == LOW);
  
  // Detecting the bicycles transition from stationary to moving
  bool justStartedMoving = (previousMotionState == LOW && motionState == HIGH);
  
  // Detecting a new press of the manual OFF button
  bool offButtonJustPressed = (previousOffButtonState == HIGH && offButtonState == LOW);
  
  // Resetting the system when movement resumes
  // When the bicycle transitions from stationary (LOW) to moving (HIGH), the Arduino resets the stationary lighting system
  if (justStartedMoving){
    holdActive = false;
    // Cancels the stationary light display
    manualOff = false;
    // Clears the manual OFF override so the system is ready for the next stop
    brakingEventSeen = false;
    // Clears the previous braking event so that a new one can be detected during the continuation of the ride
  }
  
  // Recording a braking event
  if (braking){
    brakingEventSeen = true;
  }
  
  // Starting the stationary hold after braking
  // It is started when the simulated 'bicycle' becomes stationary
  if (justStopped && brakingEventSeen){
    holdActive = true;
  }
  
  // Overiding the manual OFF button
  // When the bike is stationary (stopped) and the OFF button is pressed, the Arduino activates the manual OFF override
  if (stopped && offButtonJustPressed){
    manualOff = true;
  }

  // Determining whether the brake lights should be ON or OFF
  bool lightOn = !manualOff && (braking || (stopped && holdActive));
  // The LEDs illuminate when braking or when the stationary hold is active
  // However, the manual OFF override takes priority and keeps the LEDs switched OFF
  
  // Controlling the LED brightness
  if (lightOn){
    analogWrite(ledPin1, ledBrightness);
    analogWrite(ledPin2, ledBrightness);
    analogWrite(ledPin3, ledBrightness);
  }
  // If the light is on, then the LEDs will use the brightness selected according to the LDR reading from A0
  
  else{
    analogWrite(ledPin1, 0);
    analogWrite(ledPin2, 0);
    analogWrite(ledPin3, 0);
  }
  // If the brake lights should be OFF, all three LEDs receive a brightness value of zero
  /* In this section, 'analogWrite()' is used instead of 'digitalWrite()'.
  'analogWrite()' uses PWM (Pulse Width Modulation) to control the
  LED brightness using values between 0 and 255.
  The selected brightness depends on the surrounding light detected by the LDR.
  
  'DigitalWrite()', on the other hand, simply switches the LEDs ON or OFF
  using 'HIGH' and 'LOW' respectively */
  
  previousMotionState = motionState;
  // Note: This updates the previous motion state as the current motion state for the next loop
  
  previousOffButtonState = offButtonState;
  // Note: This updates the previous manual OFF button state as the current state for the next loop
  
  // Checking whether the Arduino can enter power down sleep mode
  enterSleepMode(lightOn);
}