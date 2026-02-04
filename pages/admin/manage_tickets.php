<?php
$page_title = 'Manage Tickets';
require_once __DIR__ . '/../../includes/header.php';
requireRole(['admin', 'superadmin']);

$conn = getDBConnection();

// Get tickets for this admin
if ($_SESSION['role'] == 'admin') {
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name,
               u.f_name as requester_fname, u.l_name as requester_lname
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u ON t.requester_user_id = u.user_id
        WHERE t.requested_user_id = ?
        ORDER BY 
            CASE t.status
                WHEN 'PENDING' THEN 1
                WHEN 'IN_PROGRESS' THEN 2
                WHEN 'ON_HOLD' THEN 3
                WHEN 'OPEN' THEN 4
                ELSE 5
            END,
            t.created_at DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    // Superadmin sees all tickets
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name,
               u1.f_name as requester_fname, u1.l_name as requester_lname,
               u2.f_name as admin_fname, u2.l_name as admin_lname
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u1 ON t.requester_user_id = u1.user_id
        JOIN users u2 ON t.requested_user_id = u2.user_id
        ORDER BY 
            CASE t.status
                WHEN 'PENDING' THEN 1
                WHEN 'IN_PROGRESS' THEN 2
                WHEN 'ON_HOLD' THEN 3
                WHEN 'OPEN' THEN 4
                ELSE 5
            END,
            t.created_at DESC
    ");
}

$stmt->execute();
$tickets = $stmt->get_result();

$stmt->close();
$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-tasks"></i> Manage Tickets
    </div>
    <div class="card-body">
        <?php if ($tickets->num_rows > 0): ?>
            <div class="table-container">
                <table id="ticketsTable">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Requester</th>
                            <th>Activity</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = $tickets->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo str_pad($ticket['ticket_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($ticket['requester_fname'] . ' ' . $ticket['requester_lname']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['activity_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                                <td><?php echo getStatusBadge($ticket['status']); ?></td>
                                <td><?php echo getPriorityBadge($ticket['priority']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>pages/admin/ticket_details.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--medium-gray); padding: 40px 0;">
                <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                No tickets found.
            </p>
        <?php endif; ?>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>