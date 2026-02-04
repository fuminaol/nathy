<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ticket_id = intval($data['ticket_id']);

$conn = getDBConnection();

// Get ticket start time
$stmt = $conn->prepare("SELECT start_time FROM ticket WHERE ticket_id = ? AND requested_user_id = ? AND status = 'IN_PROGRESS'");
$stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found or not in progress']);
    exit;
}

$ticket = $result->fetch_assoc();
$start_time = $ticket['start_time'];
$end_time = date('Y-m-d H:i:s');

// Calculate time spent
$time_spent = calculateTimeSpent($start_time, $end_time);

$stmt->close();

// Update ticket
$stmt = $conn->prepare("UPDATE ticket SET status = 'COMPLETED', end_time = ?, time_spent = ? WHERE ticket_id = ?");
$stmt->bind_param("ssi", $end_time, $time_spent, $ticket_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Ticket completed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to complete ticket']);
}

$stmt->close();
$conn->close();
?>