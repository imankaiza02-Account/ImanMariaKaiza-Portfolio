const int lightSensorPin = A0;

void setup() {
  Serial.begin(9600);
  // This allows the Arduino to send information to the Serial Monitor, where we can see the LDR readings.
}

void loop() {
  int lightLevel = analogRead(lightSensorPin);
  // The Arduino measures the voltage at A0 and stores the reading in lightLevel.

  Serial.println(lightLevel);
  // The Arduino displays that reading in the Serial Monitor.

  delay(500);
  // The Arduino waits half a second before taking another reading.
}
