<?php
require_once '../config.php';

header('Content-Type: application/json');

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if (isset($data['code']) && $data['code'] === 'check_mode') {
    if (isset($data['deviceID'])) {
        $device_uid = mysqli_real_escape_string($conn, $data['deviceID']);
        $dev_res = mysqli_query($conn, "SELECT id FROM devices WHERE device_uid = '$device_uid'");
        if (mysqli_num_rows($dev_res) > 0) {
            mysqli_query($conn, "UPDATE devices SET last_seen = NOW(), status = 'online' WHERE device_uid = '$device_uid'");
        } else {
            mysqli_query($conn, "INSERT INTO devices (device_uid, name, status, last_seen) VALUES ('$device_uid', 'Auto Registered Device', 'online', NOW())");
        }
    }

    $query = "SELECT setting_value FROM settings WHERE setting_key = 'system_mode' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $reg_status = (mysqli_num_rows($dev_res) > 0) ? 'updated' : 'registered';
        echo json_encode([
            'status' => $row['setting_value'], 
            'device_id' => $device_uid,
            'device_msg' => "Device $reg_status"
        ]);
    } else {
        echo json_encode(['status' => 'auth_mod', 'msg' => 'No deviceID provided']); 
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}

mysqli_close($conn);
?>
