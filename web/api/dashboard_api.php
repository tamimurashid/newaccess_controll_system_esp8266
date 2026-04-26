<?php
require_once '../config.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_users') {
        $query = "SELECT u.*, o.name as org_name, d.name as dept_name, s.name as sect_name 
                  FROM users u
                  LEFT JOIN organizations o ON u.organization_id = o.id
                  LEFT JOIN departments d ON u.department_id = d.id
                  LEFT JOIN sections s ON u.section_id = s.id
                  ORDER BY u.created_at DESC";
        $result = mysqli_query($conn, $query);
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        echo json_encode($users);
    } elseif ($action === 'get_logs') {
        $query = "SELECT l.*, u.full_name as user_name, d.name as device_name 
                  FROM logs l
                  LEFT JOIN users u ON l.user_id = u.id
                  LEFT JOIN devices d ON l.device_id = d.id
                  ORDER BY l.timestamp DESC LIMIT 100";
        $result = mysqli_query($conn, $query);
        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        echo json_encode($logs);
    } elseif ($action === 'get_recent_logs') {
        $query = "SELECT l.*, u.full_name as user_name, d.name as device_name 
                  FROM logs l
                  LEFT JOIN users u ON l.user_id = u.id
                  LEFT JOIN devices d ON l.device_id = d.id
                  ORDER BY l.timestamp DESC LIMIT 5";
        $result = mysqli_query($conn, $query);
        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        echo json_encode($logs);
    } elseif ($action === 'get_settings') {
        $query = "SELECT setting_key, setting_value FROM settings";
        $result = mysqli_query($conn, $query);
        $settings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        echo json_encode($settings);
    } elseif ($action === 'get_stats') {
        $stats = [
            'total_users' => 0,
            'active_users' => 0,
            'total_logs' => 0,
            'entries_today' => 0,
            'failed_today' => 0,
            'total_orgs' => 0,
            'total_devices' => 0,
            'most_scanned_user' => 'None'
        ];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
        if ($row = mysqli_fetch_assoc($res))
            $stats['total_users'] = $row['c'];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE status = 'active'");
        if ($row = mysqli_fetch_assoc($res))
            $stats['active_users'] = $row['c'];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM logs");
        if ($row = mysqli_fetch_assoc($res))
            $stats['total_logs'] = $row['c'];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM logs WHERE action = 'Access Granted' AND DATE(timestamp) = CURDATE()");
        if ($row = mysqli_fetch_assoc($res))
            $stats['entries_today'] = $row['c'];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM logs WHERE action LIKE 'Access Denied%' AND DATE(timestamp) = CURDATE()");
        if ($row = mysqli_fetch_assoc($res))
            $stats['failed_today'] = $row['c'];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM organizations");
        if ($row = mysqli_fetch_assoc($res))
            $stats['total_orgs'] = $row['c'];

        $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM devices");
        if ($row = mysqli_fetch_assoc($res))
            $stats['total_devices'] = $row['c'];

        $res = mysqli_query($conn, "SELECT u.full_name, COUNT(l.id) as scan_count 
                                    FROM logs l 
                                    JOIN users u ON l.user_id = u.id 
                                    GROUP BY l.user_id 
                                    ORDER BY scan_count DESC LIMIT 1");
        if ($row = mysqli_fetch_assoc($res))
            $stats['most_scanned_user'] = $row['full_name'];

        echo json_encode($stats);
    } elseif ($action === 'get_chart_data') {
        $data = ['labels' => [], 'granted' => [], 'denied' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data['labels'][] = date('M d', strtotime($date));

            $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM logs WHERE action = 'Access Granted' AND DATE(timestamp) = '$date'");
            $row = mysqli_fetch_assoc($res);
            $data['granted'][] = (int) $row['c'];

            $res = mysqli_query($conn, "SELECT COUNT(*) as c FROM logs WHERE action LIKE 'Access Denied%' AND DATE(timestamp) = '$date'");
            $row = mysqli_fetch_assoc($res);
            $data['denied'][] = (int) $row['c'];
        }
        echo json_encode($data);
    } elseif ($action === 'get_orgs') {
        $res = mysqli_query($conn, "SELECT * FROM organizations ORDER BY name ASC");
        $items = [];
        while ($row = mysqli_fetch_assoc($res))
            $items[] = $row;
        echo json_encode($items);
    } elseif ($action === 'get_depts') {
        $org_id = isset($_GET['org_id']) ? (int) $_GET['org_id'] : 0;
        $where = $org_id ? "WHERE organization_id = $org_id" : "";
        $res = mysqli_query($conn, "SELECT * FROM departments $where ORDER BY name ASC");
        $items = [];
        while ($row = mysqli_fetch_assoc($res))
            $items[] = $row;
        echo json_encode($items);
    } elseif ($action === 'get_sections') {
        $dept_id = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
        $where = $dept_id ? "WHERE department_id = $dept_id" : "";
        $res = mysqli_query($conn, "SELECT * FROM sections $where ORDER BY name ASC");
        $items = [];
        while ($row = mysqli_fetch_assoc($res))
            $items[] = $row;
        echo json_encode($items);
    } elseif ($action === 'get_devices') {
        // Mark devices as offline if not seen in 30 seconds
        mysqli_query($conn, "UPDATE devices SET status = 'offline' WHERE last_seen < NOW() - INTERVAL 30 SECOND AND status = 'online'");

        $res = mysqli_query($conn, "SELECT d.*, o.name as org_name FROM devices d LEFT JOIN organizations o ON d.organization_id = o.id ORDER BY d.name ASC");
        $items = [];
        while ($row = mysqli_fetch_assoc($res))
            $items[] = $row;
        echo json_encode($items);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's multipart form data or JSON
    if (!empty($_POST['action'])) {
        $data = $_POST;
        $post_action = $data['action'];
    } else {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);
        $post_action = isset($data['action']) ? $data['action'] : '';
    }

    if ($post_action === 'set_mode') {
        $mode = mysqli_real_escape_string($conn, $data['mode']);
        mysqli_query($conn, "UPDATE settings SET setting_value = '$mode' WHERE setting_key = 'system_mode'");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'save_advanced_settings') {
        $max_access = (int) $data['max_access_per_day'];
        $max_failed = (int) $data['max_failed_attempts'];
        mysqli_query($conn, "UPDATE settings SET setting_value = '$max_access' WHERE setting_key = 'max_access_per_day'");
        mysqli_query($conn, "UPDATE settings SET setting_value = '$max_failed' WHERE setting_key = 'max_failed_attempts'");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'delete_user') {
        $id = (int) $data['id'];
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'freeze_user') {
        $id = (int) $data['id'];
        mysqli_query($conn, "UPDATE users SET status = 'frozen' WHERE id = $id");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'unfreeze_user') {
        $id = (int) $data['id'];
        mysqli_query($conn, "UPDATE users SET status = 'active', failed_attempts = 0 WHERE id = $id");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'add_org') {
        $name = mysqli_real_escape_string($conn, $data['name']);
        mysqli_query($conn, "INSERT INTO organizations (name) VALUES ('$name')");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'add_dept') {
        $name = mysqli_real_escape_string($conn, $data['name']);
        $org_id = (int) $data['org_id'];
        mysqli_query($conn, "INSERT INTO departments (name, organization_id) VALUES ('$name', $org_id)");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'add_section') {
        $name = mysqli_real_escape_string($conn, $data['name']);
        $dept_id = (int) $data['dept_id'];
        mysqli_query($conn, "INSERT INTO sections (name, department_id) VALUES ('$name', $dept_id)");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'save_user_wizard') {
        $full_name = mysqli_real_escape_string($conn, $data['full_name']);
        $email = mysqli_real_escape_string($conn, $data['email']);
        $phone = mysqli_real_escape_string($conn, $data['phone']);
        $gender = mysqli_real_escape_string($conn, $data['gender']);
        $org_id = (int) $data['organization_id'];
        $dept_id = (int) $data['department_id'];
        $sect_id = (int) $data['section_id'];
        $role = mysqli_real_escape_string($conn, $data['role']);
        $member_id = mysqli_real_escape_string($conn, $data['member_id']);
        $card_uid = mysqli_real_escape_string($conn, $data['card_uid']);

        $photo_path = null;
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir))
                mkdir($target_dir, 0777, true);
            $file_ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
            $file_name = time() . "_" . $card_uid . "." . $file_ext;
            $photo_path = "uploads/" . $file_name;
            move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $file_name);
        }

        $query = "INSERT INTO users (full_name, email, phone, gender, photo_path, organization_id, department_id, section_id, role, member_id, card_uid, status) 
                  VALUES ('$full_name', '$email', '$phone', '$gender', '$photo_path', $org_id, $dept_id, $sect_id, '$role', '$member_id', '$card_uid', 'active')";

        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
    } elseif ($post_action === 'update_user_limits') {
        $id = (int) $data['id'];
        $max_access = (int) $data['max_access_per_day'];
        $max_failed = (int) $data['max_failed_attempts'];
        mysqli_query($conn, "UPDATE users SET max_access_per_day = $max_access, max_failed_attempts = $max_failed WHERE id = $id");
        echo json_encode(['success' => true]);
    } elseif ($post_action === 'update_device') {
        $id = (int) $data['id'];
        $name = mysqli_real_escape_string($conn, $data['name']);
        $org_id = $data['organization_id'] ? (int) $data['organization_id'] : "NULL";
        $loc = mysqli_real_escape_string($conn, $data['location']);
        mysqli_query($conn, "UPDATE devices SET name = '$name', organization_id = $org_id, location = '$loc' WHERE id = $id");
        echo json_encode(['success' => true]);
    }
}

mysqli_close($conn);
?>