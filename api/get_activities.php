<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['department_id'])) {
    echo json_encode([]);
    exit;
}

$department_id = intval($_GET['department_id']);

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT activity_id, activity_name FROM project_activities WHERE department_id = ? ORDER BY activity_name");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($activities);
?>
