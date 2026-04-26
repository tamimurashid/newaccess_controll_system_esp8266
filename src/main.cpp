#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <WiFiManager.h>
#include <ArduinoJson.h>
#include <ESP8266HTTPClient.h>
#include <Servo.h>

/* --- Configuration & Pin Definitions --- */
#define RST_PIN D1      // RFID Reset
#define SS_PIN  D2      // RFID Slave Select
#define PIN_LED_GREEN D0 // Access Granted LED
#define PIN_LED_RED   10 // Access Denied LED
#define PIN_LED_CARD  D3 // Blue LED: WiFi/System Status LED (Internet Connection Test)
#define PIN_LED_WAIT  9  // Processing LED
#define PIN_ALARM     D4 // Buzzer/Alarm
#define PIN_SERVO     D8 // Servo Motor

// Server Configuration
const char* serverUrlBase = "http://192.168.0.77:8888/access_control_rfid-main/web/api/process_card.php";
const char* serverUrlMode = "http://192.168.0.77:8888/access_control_rfid-main/web/api/get_mode.php";

// Timing Constants (milliseconds)
const unsigned long GRANTED_HOLD_TIME = 5000;
const unsigned long DENIED_HOLD_TIME  = 2000;
const unsigned long MODE_CHECK_PERIOD = 5000; // Check mode every 5 seconds
const unsigned long HTTP_TIMEOUT      = 5000;  // 5 second timeout for requests

/* --- Global Objects & State --- */
MFRC522 rfid(SS_PIN, RST_PIN);
Servo myServo;
WiFiClient wifiClient;
WiFiManager wifiManager;

enum SystemState { IDLE, PROCESSING, GRANTED, DENIED };
SystemState currentState = IDLE;
unsigned long stateStartTime = 0;
unsigned long lastModeCheckTime = 0;
String currentMode = "auth_mod"; // Default: Authentication Mode

/* --- Helper Functions --- */

void setSystemState(SystemState newState) {
    currentState = newState;
    stateStartTime = millis();
    
    switch (newState) {
        case IDLE:
            Serial.println("State: IDLE");
            digitalWrite(PIN_LED_GREEN, LOW);
            digitalWrite(PIN_LED_RED, LOW);
            digitalWrite(PIN_LED_WAIT, LOW);
            myServo.write(0);
            break;
            
        case PROCESSING:
            Serial.println("State: PROCESSING");
            digitalWrite(PIN_LED_WAIT, HIGH);
            break;
            
        case GRANTED:
            Serial.println("State: ACCESS GRANTED");
            digitalWrite(PIN_LED_GREEN, HIGH);
            digitalWrite(PIN_LED_WAIT, LOW);
            tone(PIN_ALARM, 1000, 300); // Quick success beep
            myServo.write(45);          // Unlock
            break;
            
        case DENIED:
            Serial.println("State: ACCESS DENIED");
            digitalWrite(PIN_LED_RED, HIGH);
            digitalWrite(PIN_LED_WAIT, LOW);
            tone(PIN_ALARM, 500, 1000); // Longer warning beep
            break;
    }
}

void updateSystemState() {
    unsigned long elapsed = millis() - stateStartTime;
    
    if (currentState == GRANTED && elapsed > GRANTED_HOLD_TIME) {
        setSystemState(IDLE);
    } else if (currentState == DENIED && elapsed > DENIED_HOLD_TIME) {
        setSystemState(IDLE);
    }
}

void checkWiFiConnection() {
    if (WiFi.status() != WL_CONNECTED) {
        if (digitalRead(PIN_LED_CARD) == HIGH) {
            digitalWrite(PIN_LED_CARD, LOW);
            Serial.println("\n[!] WiFi Connection Lost!");
        }
        Serial.print(".");
    } else {
        if (digitalRead(PIN_LED_CARD) == LOW) {
            digitalWrite(PIN_LED_CARD, HIGH);
            Serial.println("\n[+] WiFi Connected!");
            Serial.print("IP Address: ");
            Serial.println(WiFi.localIP());
        }
    }
}
void fetchMode() {
    if (WiFi.status() != WL_CONNECTED) return;
    
    Serial.print("Checking system mode from: ");
    Serial.println(serverUrlMode);
    
    String deviceID = String(ESP.getChipId());
    String payload = "{\"code\": \"check_mode\", \"deviceID\": \"" + deviceID + "\"}";
    Serial.print("Payload: ");
    Serial.println(payload);

    HTTPClient http;
    http.begin(wifiClient, serverUrlMode);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(HTTP_TIMEOUT);

    int httpCode = http.POST(payload);
    Serial.print("HTTP Result Code: ");
    Serial.println(httpCode);

    if (httpCode == HTTP_CODE_OK) {
        String response = http.getString();
        Serial.print("Server Response: ");
        Serial.println(response);
        
        JsonDocument doc;
        DeserializationError error = deserializeJson(doc, response);

        if (!error) {
            String newMode = doc["status"].as<String>();
            Serial.print("Mode from Server: ");
            Serial.println(newMode);
            
            if (newMode == "reg_mod" || newMode == "auth_mod") {
                if (currentMode != newMode) {
                    currentMode = newMode;
                    Serial.print("System Mode Updated: ");
                    Serial.println(currentMode);
                }
            }
        } else {
            Serial.print("JSON Parse Error: ");
            Serial.println(error.f_str());
        }
    } else {
        Serial.print("HTTP Request Failed: ");
        Serial.println(http.errorToString(httpCode).c_str());
    }
    http.end();
}

void processCard(String cardID) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("Error: WiFi not connected");
        setSystemState(DENIED);
        return;
    }

    setSystemState(PROCESSING);

    Serial.print("Processing Card: ");
    Serial.println(cardID);
    Serial.print("Target URL: ");
    Serial.println(serverUrlBase);

    HTTPClient http;
    http.begin(wifiClient, serverUrlBase);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(HTTP_TIMEOUT);

    String deviceID = String(ESP.getChipId());
    String payload = "{\"cardID\": \"" + cardID + "\", \"mode\": \"" + currentMode + "\", \"deviceID\": \"" + deviceID + "\"}";
    Serial.print("Sending Payload: ");
    Serial.println(payload);
    
    int httpCode = http.POST(payload);
    Serial.print("HTTP Result Code: ");
    Serial.println(httpCode);

    if (httpCode == HTTP_CODE_OK) {
        String response = http.getString();
        Serial.print("Server Response: ");
        Serial.println(response);
        
        JsonDocument doc;
        DeserializationError error = deserializeJson(doc, response);

        if (!error) {
            String code = doc["code"].as<String>();
            String newMode = doc["mode"].as<String>();
            Serial.print("Server Code: ");
            Serial.println(code);
            Serial.print("Server Mode: ");
            Serial.println(newMode);
            
            if (newMode == "reg_mod" || newMode == "auth_mod") {
                if (currentMode != newMode) {
                    currentMode = newMode;
                    Serial.print("System Mode Updated via API: ");
                    Serial.println(currentMode);
                }
            }

            if (code == "001") {
                Serial.println("Action: ACCESS GRANTED");
                setSystemState(GRANTED);
            } else {
                Serial.println("Action: ACCESS DENIED");
                setSystemState(DENIED);
            }
        } else {
            Serial.print("JSON Parse Error: ");
            Serial.println(error.f_str());
            setSystemState(DENIED);
        }
    } else {
        Serial.print("HTTP Request Failed: ");
        Serial.println(http.errorToString(httpCode).c_str());
        setSystemState(DENIED);
    }
    http.end();
}

/* --- Arduino Core Functions --- */

void setup() {
    Serial.begin(9600);
    Serial.println("\nRFID Access Control System Initializing...");

    // Pin Setup
    pinMode(PIN_LED_GREEN, OUTPUT);
    pinMode(PIN_LED_RED, OUTPUT);
    pinMode(PIN_LED_CARD, OUTPUT);
    pinMode(PIN_LED_WAIT, OUTPUT);
    pinMode(PIN_ALARM, OUTPUT);
    
    // Initial State
    digitalWrite(PIN_LED_GREEN, LOW);
    digitalWrite(PIN_LED_RED, LOW);
    digitalWrite(PIN_LED_CARD, LOW);
    digitalWrite(PIN_LED_WAIT, LOW);

    // Servo Setup
    myServo.attach(PIN_SERVO);
    myServo.write(0); // Initial locked position

    // RFID Setup
    SPI.begin();
    rfid.PCD_Init();
    Serial.println("RFID Reader Ready.");

    // WiFi Setup
    wifiManager.autoConnect("AccessControlAP");
    Serial.println("Connected to WiFi.");
    Serial.print("Local IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("Mode URL: ");
    Serial.println(serverUrlMode);
    Serial.print("Process URL: ");
    Serial.println(serverUrlBase);
    digitalWrite(PIN_LED_CARD, HIGH);

    setSystemState(IDLE);
}

void loop() {
    // 1. Maintain WiFi and Check Status
    checkWiFiConnection();

    // 2. Periodic Mode Update (only if Idle)
    if (currentState == IDLE && (millis() - lastModeCheckTime > MODE_CHECK_PERIOD)) {
        fetchMode();
        lastModeCheckTime = millis();
    }

    // 3. Update State Transitions (Non-blocking)
    updateSystemState();

    // 4. RFID Card Detection (only if Idle)
    if (currentState == IDLE) {
        if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
            String cardUID = "";
            for (byte i = 0; i < rfid.uid.size; i++) {
                if (rfid.uid.uidByte[i] < 0x10) cardUID += "0";
                cardUID += String(rfid.uid.uidByte[i], HEX);
            }
            Serial.print("Card Scanned: ");
            Serial.println(cardUID);
            
            processCard(cardUID);
            
            rfid.PICC_HaltA();
            rfid.PCD_StopCrypto1();
        }
    }

    // Small delay to prevent tight loop, though non-blocking logic is preferred
    delay(10);
}

