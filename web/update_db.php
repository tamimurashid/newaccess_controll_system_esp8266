<?php
require_once 'config.php';

$queries = [
    "ALTER TABLE cards ADD COLUMN failed_attempts INT DEFAULT 0",
    "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('max_access_per_day', '0')",
    "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('max_failed_attempts', '0')"
];

foreach ($queries as $q) {
    if (mysqli_query($conn, $q)) {
        echo "Success: $q <br>";
    } else {
        echo "Error or already exists: " . mysqli_error($conn) . " ($q)<br>";
    }
}
echo "Done.";
?>
