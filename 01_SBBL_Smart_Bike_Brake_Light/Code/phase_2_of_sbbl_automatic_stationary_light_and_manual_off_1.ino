// SMART BICYCLE BRAKE LIGHT (SBBL)
// PHASE 1: BRAKE DETECTION
// REVISED PHASE 2: AUTOMATIC STATIONARY LIGHT AND MANUAL OFF OVERRIDE BUTTON

const int brakePin = 2;
const int motionPin = 3;
const int offButtonPin = 4;

const int ledPin1 = 9;
const int ledPin2 = 10;
const int ledPin3 = 11;

bool brakingEventSeen = false;
// Records whether a braking event has occurred
// Initially, its value is 'false', meaning that the system has not detected a braking event

bool holdActive = false;
// Records whether the stationary hold is active

bool manualOff = false;
// Records whether the rider has manually switched off

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
  
}

void loop()
{
  // Reading the inputs
  bool braking = (digitalRead(brakePin) == LOW);
  // Note: Since we used 'INPUT_PULLUP' for the brakePin:
  // 'LOW' means a pressed button, or in our case braking
  // 'HIGH' means a brake button is released, so no braking
  
  int motionState = digitalRead(motionPin);
  bool stopped = (motionState == LOW);
  
  int offButtonState = digitalRead(offButtonPin);
  
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

  // This section of code combines the braking and movement information above to determines whether the LEDs should be ON or OFF
  bool lightOn = !manualOff && (braking || (stopped && holdActive));
  digitalWrite(ledPin1, lightOn ? HIGH : LOW);
  digitalWrite(ledPin2, lightOn ? HIGH : LOW);
  digitalWrite(ledPin3, lightOn ? HIGH : LOW);
  // The 'lightOn ? HIGH : LOW' is a shorter way of saying if 'lightOn' is true use 'HIGH' and if it is false use 'LOW'
  // The '?' is what starts the conditional expression
  
  previousMotionState = motionState;
  // Note: This updates the previous motion state as the current motion state for the next loop
  
  
  previousOffButtonState = offButtonState;
  // Note: This updates the previous manual OFF button state as the current state for the next loop
}