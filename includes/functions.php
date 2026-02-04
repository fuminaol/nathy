<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    
    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    }
    return $_SESSION['role'] === $roles;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

// Redirect if doesn't have required role
function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        http_response_code(403);
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <style>
                body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
                .error-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
                h1 { color: #2d5016; margin: 0 0 20px 0; }
                p { color: #666; margin: 0 0 30px 0; }
                a { background: #2d5016; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; }
                a:hover { background: #3d6820; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h1>🚫 Access Denied</h1>
                <p>You do not have permission to access this page.</p>
                <a href="' . BASE_URL . 'pages/dashboard.php">Return to Dashboard</a>
            </div>
        </body>
        </html>';
        exit;
    }
}

// Get user info
function getUserInfo($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN department d ON u.department_id = d.department_id WHERE u.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Calculate time spent
function calculateTimeSpent($start_time, $end_time) {
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $interval = $start->diff($end);
    
    $days = $interval->days;
    $hours = $interval->h;
    $minutes = $interval->i;
    
    $result = '';
    if ($days > 0) {
        $result .= $days . ' day' . ($days > 1 ? 's' : '') . ' ';
    }
    if ($hours > 0 || $days > 0) {
        $result .= $hours . ' hr' . ($hours != 1 ? 's' : '') . ' ';
    }
    $result .= $minutes . ' min' . ($minutes != 1 ? 's' : '');
    
    return trim($result);
}

// Get notifications count
function getNotificationCount($user_id) {
    $conn = getDBConnection();
    
    // For requesters: count status changes
    // For admins: count new tickets
    if ($_SESSION['role'] === 'employee') {
        // Count tickets where status changed recently
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ticket WHERE requester_user_id = ? AND updated_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status != 'OPEN'");
        $stmt->bind_param("i", $user_id);
    } else {
        // Count pending tickets for admin
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ticket WHERE requested_user_id = ? AND status = 'PENDING'");
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
    $stmt->close();
    $conn->close();
    return $count;
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format date
function formatDate($date) {
    if (!$date) return 'N/A';
    return date('M d, Y h:i A', strtotime($date));
}

// Get status badge HTML
function getStatusBadge($status) {
    $badges = [
        'OPEN' => '<span class="badge badge-secondary">OPEN</span>',
        'PENDING' => '<span class="badge badge-warning">PENDING</span>',
        'IN_PROGRESS' => '<span class="badge badge-info">IN PROGRESS</span>',
        'ON_HOLD' => '<span class="badge badge-hold">ON HOLD</span>',
        'COMPLETED' => '<span class="badge badge-success">COMPLETED</span>',
        'REJECTED' => '<span class="badge badge-danger">REJECTED</span>'
    ];
    return $badges[$status] ?? $status;
}

// Get priority badge HTML
function getPriorityBadge($priority) {
    if (!$priority) return '<span class="badge badge-light">NONE</span>';
    
    $badges = [
        'LOW' => '<span class="badge badge-priority-low">LOW</span>',
        'MEDIUM' => '<span class="badge badge-priority-medium">MEDIUM</span>',
        'HIGH' => '<span class="badge badge-priority-high">HIGH</span>',
        'CRITICAL' => '<span class="badge badge-priority-critical">CRITICAL</span>'
    ];
    return $badges[$priority] ?? $priority;
}

// Get admin of department
function getAdminOfDepartment($department_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT user_id, f_name, l_name FROM users WHERE department_id = ? AND role = 'admin' AND status = 'Active' LIMIT 1");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $admin;
}
?>
