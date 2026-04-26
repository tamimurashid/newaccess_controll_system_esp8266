<?php
require_once '../config.php';

header('Content-Type: application/json');

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if (isset($data['cardID']) && isset($data['mode'])) {
    $uid = mysqli_real_escape_string($conn, $data['cardID']);
    $mode = mysqli_real_escape_string($conn, $data['mode']);
    $device_uid = isset($data['deviceID']) ? mysqli_real_escape_string($conn, $data['deviceID']) : 'UNKNOWN_DEVICE';
    
    // 1. Fetch current system mode from settings (Prioritize server-side state)
    $mode_res = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'system_mode' LIMIT 1");
    $mode_row = mysqli_fetch_assoc($mode_res);
    $system_mode = $mode_row ? $mode_row['setting_value'] : 'auth_mod';

    $response_code = "000";

    // 2. Device Tracking & Management
    $device_id = null;
    $dev_res = mysqli_query($conn, "SELECT id FROM devices WHERE device_uid = '$device_uid'");
    if (mysqli_num_rows($dev_res) > 0) {
        $dev_row = mysqli_fetch_assoc($dev_res);
        $device_id = $dev_row['id'];
        mysqli_query($conn, "UPDATE devices SET last_seen = NOW(), status = 'online' WHERE id = $device_id");
    } else {
        // Auto-register unknown device
        mysqli_query($conn, "INSERT INTO devices (device_uid, name, status, last_seen) VALUES ('$device_uid', 'Auto Registered Device', 'online', NOW())");
        $device_id = mysqli_insert_id($conn);
    }

    // 3. Registration Mode Handling (Capture for Wizard)
    if ($system_mode === 'reg_mod') {
        // Clear previous scans from this device to avoid confusion
        mysqli_query($conn, "DELETE FROM scanned_cards_temp WHERE device_uid = '$device_uid'");
        
        // Insert new scan
        mysqli_query($conn, "INSERT INTO scanned_cards_temp (device_uid, card_uid) VALUES ('$device_uid', '$uid')");
        
        $response_code = "001";
        $log_action = "Registration Scan Captured";
        mysqli_query($conn, "INSERT INTO logs (card_uid, device_id, action) VALUES ('$uid', $device_id, '$log_action')");
    } 
    else {
        // 4. Authentication Mode
        
        // Fetch settings for limits
        $max_access = 0;
        $max_failed = 0;
        $res = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('max_access_per_day', 'max_failed_attempts')");
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row['setting_key'] == 'max_access_per_day') $max_access = (int)$row['setting_value'];
            if ($row['setting_key'] == 'max_failed_attempts') $max_failed = (int)$row['setting_value'];
        }

        $query = "SELECT id, status, failed_attempts, max_access_per_day, max_failed_attempts FROM users WHERE card_uid = '$uid'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $user_id = $user['id'];
            
            // Effective limits (Individual > System)
            $eff_max_access = ($user['max_access_per_day'] > 0) ? (int)$user['max_access_per_day'] : $max_access;
            $eff_max_failed = ($user['max_failed_attempts'] > 0) ? (int)$user['max_failed_attempts'] : $max_failed;

            if ($user['status'] === 'active') {
                // Check daily limit
                $limit_ok = true;
                if ($eff_max_access > 0) {
                    $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE card_uid = '$uid' AND action = 'Access Granted' AND DATE(timestamp) = CURDATE()");
                    $count_row = mysqli_fetch_assoc($count_res);
                    if ($count_row['total'] >= $eff_max_access) {
                        $limit_ok = false;
                    }
                }

                if ($limit_ok) {
                    $response_code = "001";
                    $log_action = "Access Granted";
                    mysqli_query($conn, "UPDATE users SET failed_attempts = 0 WHERE id = $user_id");
                } else {
                    $log_action = "Access Denied (Daily Limit Reached)";
                    $new_failed = $user['failed_attempts'] + 1;
                    mysqli_query($conn, "UPDATE users SET failed_attempts = $new_failed WHERE id = $user_id");
                    
                    if ($eff_max_failed > 0 && $new_failed >= $eff_max_failed) {
                        mysqli_query($conn, "UPDATE users SET status = 'frozen' WHERE id = $user_id");
                        $log_action = "Access Denied (Limit - Auto Frozen)";
                    }
                }
            } else {
                $log_action = "Access Denied (Frozen)";
            }
        } else {
            $user_id = null;
            $log_action = "Access Denied (Unknown Card)";
        }

        // Insert log
        $user_val = $user_id ? $user_id : "NULL";
        $log_query = "INSERT INTO logs (user_id, card_uid, device_id, action) VALUES ($user_val, '$uid', $device_id, '$log_action')";
        mysqli_query($conn, $log_query);
    }

    echo json_encode(['code' => $response_code, 'mode' => $system_mode]);
} else {
    echo json_encode(['error' => 'Invalid request']);
}

mysqli_close($conn);
?>
