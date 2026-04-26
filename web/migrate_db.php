<?php
// migrate_db.php - Safe migration that adds missing tables/columns without dropping data
require_once 'config.php';

echo "<h3>Running safe migration...</h3>";

// 1. Ensure settings table exists
$res = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if (mysqli_num_rows($res) == 0) {
    mysqli_query($conn, "CREATE TABLE settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value VARCHAR(100) NOT NULL,
        INDEX idx_setting_key (setting_key)
    )");
    echo "Created 'settings' table.<br>";
} else {
    echo "'settings' table already exists.<br>";
}

// 2. Ensure default settings exist
mysqli_query($conn, "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('system_mode', 'auth_mod')");
mysqli_query($conn, "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('max_access_per_day', '0')");
mysqli_query($conn, "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('max_failed_attempts', '0')");
echo "Default settings ensured.<br>";

// 3. Ensure scanned_cards_temp table exists
$res = mysqli_query($conn, "SHOW TABLES LIKE 'scanned_cards_temp'");
if (mysqli_num_rows($res) == 0) {
    mysqli_query($conn, "CREATE TABLE scanned_cards_temp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_uid VARCHAR(50) NOT NULL,
        card_uid VARCHAR(50) NOT NULL,
        scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created 'scanned_cards_temp' table.<br>";
} else {
    echo "'scanned_cards_temp' table already exists.<br>";
}

// 4. Add missing columns to users table
$cols_to_add = [
    'max_access_per_day' => 'INT DEFAULT 0',
    'max_failed_attempts' => 'INT DEFAULT 0'
];
foreach ($cols_to_add as $col => $type) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$col'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN $col $type");
        echo "Added column '$col' to users table.<br>";
    } else {
        echo "Column '$col' already exists in users table.<br>";
    }
}

// 5. Verify the current system mode
$mode_res = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'system_mode'");
$mode_row = mysqli_fetch_assoc($mode_res);
echo "<br><strong>Current system mode: " . ($mode_row ? $mode_row['setting_value'] : 'NOT SET') . "</strong><br>";

// 6. Show registered users
$users_res = mysqli_query($conn, "SELECT id, full_name, card_uid, status FROM users ORDER BY id");
echo "<br><strong>Registered users:</strong><br>";
if ($users_res && mysqli_num_rows($users_res) > 0) {
    while ($u = mysqli_fetch_assoc($users_res)) {
        echo "  ID={$u['id']}, Name={$u['full_name']}, Card={$u['card_uid']}, Status={$u['status']}<br>";
    }
} else {
    echo "  No users registered.<br>";
}

mysqli_close($conn);
echo "<br><strong>Migration complete!</strong>";
?>
