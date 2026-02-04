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

// Verify ticket belongs to user and is in OPEN status
$stmt = $conn->prepare("UPDATE ticket SET status = 'PENDING' WHERE ticket_id = ? AND requester_user_id = ? AND status = 'OPEN'");
$stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Ticket sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to send ticket']);
}

$stmt->close();
$conn->close();
?>