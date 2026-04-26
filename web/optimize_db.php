<?php
require_once 'config.php';

$queries = [
    "ALTER TABLE logs ADD INDEX idx_uid (uid)",
    "ALTER TABLE logs ADD INDEX idx_timestamp (timestamp)",
    "ALTER TABLE cards ADD INDEX idx_card_uid (uid)",
    "ALTER TABLE settings ADD INDEX idx_setting_key (setting_key)"
];

foreach ($queries as $q) {
    if (mysqli_query($conn, $q)) {
        echo "Success: $q <br>";
    } else {
        echo "Note: " . mysqli_error($conn) . " ($q)<br>";
    }
}
echo "Database optimization complete.";
?>
