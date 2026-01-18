#include <WiFiS3.h>
#include "arduino_secrets.h" //file in cui vanno le credenziali per la rete wifi

const char* WIFI_SSID = SECRET_SSID;
const char* WIFI_PASS = SECRET_PASS;

// Server (meglio host senza http://)
const char* SERVER_HOST = "";   // qui metti il tuo server pippo.altervista.com oppure IP "192.168.1.50"
const int   SERVER_PORT = 80;

// Path dell’endpoint PHP
const char* SERVER_PATH = ""; //qui metti il percorso della 'agina php es. mia_pag.php

// (Consigliato) chiave semplice per evitare chiamate casuali
const char* API_KEY = ""; //qui metti una chiave per un minimo di sicurezza..

const int LDR_PIN = A0;

// Intervallo invio (ms)
const unsigned long INTERVAL_MS = 10000;

unsigned long lastSend = 0;

WiFiClient client;

void connectWiFi() {
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  while (WiFi.status() != WL_CONNECTED) {
    delay(300);
  }
}

int readLdrAveraged(int samples = 10) {
  long sum = 0;
  for (int i = 0; i < samples; i++) {
    sum += analogRead(LDR_PIN);
    delay(5);
  }
  return (int)(sum / samples);
}

void setup() {

  Serial.begin(115200);
  Serial.println("Serial Init ok");
  
  delay(1000);

  connectWiFi();
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  unsigned long now = millis();
  if (now - lastSend >= INTERVAL_MS) {
    lastSend = now;

    int raw = readLdrAveraged(10);      // 0..1023
    float voltage = raw * (5.0 / 1023.0); // stima tensione (V)

    // Costruisci querystring
    String url = String(SERVER_PATH) +
                 "?key=" + API_KEY +
                 "&value=" + String(raw) +
                 "&v=" + String(voltage, 3) +
                 "&uptime=" + String(now);

    Serial.print("GET http://");
    Serial.print(SERVER_HOST);
    Serial.println(url);

    if (client.connect(SERVER_HOST, SERVER_PORT)) {
      // Richiesta HTTP 1.1
      client.print(String("GET ") + url + " HTTP/1.1\r\n");
      client.print(String("Host: ") + SERVER_HOST + "\r\n");
      client.print("Connection: close\r\n\r\n");

      // Leggi risposta (minimale)
      unsigned long t0 = millis();
      while (client.connected() && millis() - t0 < 5000) {
        while (client.available()) {
          String line = client.readStringUntil('\n');
          // Stampa solo prime righe o tutto se preferisci
          Serial.println(line);
          t0 = millis();
        }
      }
      client.stop();
    } else {
      Serial.println("Connessione al server fallita");
    }
  }
}
