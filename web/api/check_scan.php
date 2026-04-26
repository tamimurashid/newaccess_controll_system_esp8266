<?php
require_once '../config.php';

header('Content-Type: application/json');

$device_uid = isset($_GET['deviceID']) ? mysqli_real_escape_string($conn, $_GET['deviceID']) : '';

if ($device_uid) {
    // Check for the most recent scan from this device
    $query = "SELECT card_uid FROM scanned_cards_temp 
              WHERE device_uid = '$device_uid' 
              ORDER BY scanned_at DESC LIMIT 1";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'uid' => $row['card_uid']]);
        
        // Clear the scan after it's retrieved to prevent reuse
        mysqli_query($conn, "DELETE FROM scanned_cards_temp WHERE device_uid = '$device_uid'");
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Device ID required']);
}

mysqli_close($conn);
?>
