// SMART BICYCLE BRAKE LIGHT (SBBL)
// PHASE 1: BRAKE DETECTION

const int brakePin = 2;

const int ledPin1 = 9;
const int ledPin2 = 10;
const int ledPin3 = 11;

void setup()
{
  // Initialising braking as input
  pinMode(brakePin, INPUT_PULLUP);
  // Note: 'INPUT_PULLUP' enables the Arduino's internal pull-up resistor
  
  // Intialising the LEDs as output
  pinMode(ledPin1, OUTPUT);
  pinMode(ledPin2, OUTPUT);
  pinMode(ledPin3, OUTPUT);
}

void loop()
{
  // Reading the brake input
  int brakeState = digitalRead(brakePin);
  // Note: Since we used 'INPUT_PULLUP:
  // 'LOW' means a pressed button, or in our case braking
  // 'HIGH' means both buttons are released, so no braking
  
  // Conditional statements detect brake use and its response
  if (brakeState == LOW){
    digitalWrite(ledPin1, HIGH);
    digitalWrite(ledPin2, HIGH);
    digitalWrite(ledPin3, HIGH);
  }
  else {
    digitalWrite(ledPin1, LOW);
    digitalWrite(ledPin2, LOW);
    digitalWrite(ledPin3, LOW);
  } 
}