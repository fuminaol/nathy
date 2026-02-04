<?php
$page_title = 'My Tickets';
require_once __DIR__ . '/../../includes/header.php';

$conn = getDBConnection();

// Get all tickets for current user
$stmt = $conn->prepare("
    SELECT t.*, pa.activity_name, d.department_name,
           u.f_name as admin_fname, u.l_name as admin_lname
    FROM ticket t
    JOIN project_activities pa ON t.activity_id = pa.activity_id
    JOIN department d ON pa.department_id = d.department_id
    JOIN users u ON t.requested_user_id = u.user_id
    WHERE t.requester_user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tickets = $stmt->get_result();

$stmt->close();
$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <i class="fas fa-ticket-alt"></i> My Tickets
        </div>
        <a href="<?php echo BASE_URL; ?>pages/tickets/create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Create New Ticket
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($tickets->num_rows > 0): ?>
            <div class="table-container">
                <table id="ticketsTable">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Activity</th>
                            <th>Department</th>
                            <th>Admin</th>
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
                                <td><?php echo htmlspecialchars($ticket['activity_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['admin_fname'] . ' ' . $ticket['admin_lname']); ?></td>
                                <td><?php echo getStatusBadge($ticket['status']); ?></td>
                                <td><?php echo getPriorityBadge($ticket['priority']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>pages/tickets/view.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">
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
                You haven't created any tickets yet. Click "Create New Ticket" to get started.
            </p>
        <?php endif; ?>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>