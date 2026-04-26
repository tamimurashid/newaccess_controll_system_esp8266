<?php
// init_db.php (Universal RFID System)
require_once 'config.php';

$queries = [
    "SET FOREIGN_KEY_CHECKS = 0",
    "DROP TABLE IF EXISTS logs",
    "DROP TABLE IF EXISTS users",
    "DROP TABLE IF EXISTS scanned_cards_temp",
    "DROP TABLE IF EXISTS devices",
    "DROP TABLE IF EXISTS sections",
    "DROP TABLE IF EXISTS departments",
    "DROP TABLE IF EXISTS organizations",
    "SET FOREIGN_KEY_CHECKS = 1",

    "CREATE TABLE IF NOT EXISTS organizations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_uid VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        organization_id INT,
        location VARCHAR(100),
        status ENUM('online', 'offline') DEFAULT 'offline',
        last_seen TIMESTAMP NULL,
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL
    )",

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
        photo_path VARCHAR(255),
        organization_id INT,
        department_id INT,
        section_id INT,
        role VARCHAR(100),
        member_id VARCHAR(50),
        card_uid VARCHAR(50) UNIQUE,
        status ENUM('active', 'frozen') DEFAULT 'active',
        failed_attempts INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
    )",

    "CREATE TABLE IF NOT EXISTS scanned_cards_temp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_uid VARCHAR(50) NOT NULL,
        card_uid VARCHAR(50) NOT NULL,
        scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        card_uid VARCHAR(50) NOT NULL,
        device_id INT,
        action VARCHAR(100) NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL
    )",

    "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('system_mode', 'auth_mod')",
    "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('max_access_per_day', '0')",
    "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('max_failed_attempts', '0')",
    "INSERT IGNORE INTO organizations (id, name) VALUES (1, 'Default Organization')"
];

foreach ($queries as $q) {
    if (!mysqli_query($conn, $q)) {
        echo "Error executing: $q <br> Error: " . mysqli_error($conn) . "<br>";
    }
}

mysqli_close($conn);
echo "Database initialized with new Universal Multi-Organization structure.";
?>
