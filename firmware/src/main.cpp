#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include "config.h"

const int PIR_PIN = 25;
const char* MQTT_TOPIC = "sensors/test";
const char* MOTION_PAYLOAD = "{\"message\": \"🚨 Motion Detected 🚨\"}";

int lastPirState = LOW;

WiFiClientSecure wifiClient;
PubSubClient pubSubClient(wifiClient);

void connectToWifi();
void reconnectToMqttBroker();
bool hasMotionStarted();
void publishMotionAlert();

void setup() {
  Serial.begin(115200);
  delay(100);

  pinMode(PIR_PIN, INPUT);

  connectToWifi();

  pubSubClient.setBufferSize(2048);
  pubSubClient.setServer(MQTT_HOST, MQTT_PORT);
}

void loop() {
  if (!pubSubClient.connected()) {
    reconnectToMqttBroker();
  }
  pubSubClient.loop();

  if (hasMotionStarted()) {
    publishMotionAlert();
    delay(1000);
  }
}

bool hasMotionStarted() {
  int currentPirState = digitalRead(PIR_PIN);
  bool started = (currentPirState == HIGH && lastPirState == LOW);
  lastPirState = currentPirState;
  return started;
}

void publishMotionAlert() {
  Serial.println("Motion detected!");

  if (!pubSubClient.publish(MQTT_TOPIC, MOTION_PAYLOAD)) {
    Serial.println("Failed to publish motion alert");
  }
}

void connectToWifi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWi-Fi connected!");
  wifiClient.setInsecure();
}

void reconnectToMqttBroker() {
  while (!pubSubClient.connected()) {
    String clientId = "ESP32_PIR_" + String(random(0xffff), HEX);
    if (pubSubClient.connect(clientId.c_str(), MQTT_USER, MQTT_PASS)) {
      Serial.println("CONNECTED SUCCESSFULLY!");
    } else {
      Serial.print(pubSubClient.state());
      delay(3000);
    }
  }
}
