<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';

$count = 0;

if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    
    if ($_SESSION['role'] === 'employee') {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ticket WHERE requester_user_id = ? AND updated_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status != 'OPEN'");
        $stmt->bind_param("i", $_SESSION['user_id']);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ticket WHERE requested_user_id = ? AND status = 'PENDING'");
        $stmt->bind_param("i", $_SESSION['user_id']);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    $stmt->close();
    $conn->close();
}

echo json_encode(['count' => $count]);
?>