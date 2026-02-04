<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ticket_id = intval($data['ticket_id']);

$conn = getDBConnection();

$start_time = date('Y-m-d H:i:s');

$stmt = $conn->prepare("UPDATE ticket SET status = 'IN_PROGRESS', start_time = ? WHERE ticket_id = ? AND requested_user_id = ? AND status = 'PENDING'");
$stmt->bind_param("sii", $start_time, $ticket_id, $_SESSION['user_id']);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Ticket started successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to start ticket']);
}

$stmt->close();
$conn->close();
?>