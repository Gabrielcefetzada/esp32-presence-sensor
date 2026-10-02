#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include "config.h"

WiFiClientSecure wifiClient;
PubSubClient pubSubClient(wifiClient);
void connectToWifi();
void reconnectToMqttBroker();

void setup() {
  Serial.begin(115200);
  delay(100);

  connectToWifi();

  pubSubClient.setBufferSize(2048);
  pubSubClient.setServer(MQTT_HOST, MQTT_PORT);
}

void loop() {
  if (!pubSubClient.connected()) {
    reconnectToMqttBroker();
  }
  pubSubClient.loop();

  static unsigned long lastMsg = 0;
  if (millis() - lastMsg > 3000) {
    lastMsg = millis();
    pubSubClient.publish("sensores/teste", "{\"status\": \"online\"}");
    Serial.println("Mensagem publicada via TLS!");
  }
}

void connectToWifi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWi-Fi Conectado!");
  wifiClient.setInsecure();
}

void reconnectToMqttBroker() {
  while (!pubSubClient.connected()) {
    String clientId = "ESP32_PIR_" + String(random(0xffff), HEX);
    
    if (pubSubClient.connect(clientId.c_str(), MQTT_USER, MQTT_PASS)) {
      Serial.println("CONECTADO COM SUCESSO!");
    } else {
      Serial.print(pubSubClient.state());
      delay(3000);
    }
  }
}