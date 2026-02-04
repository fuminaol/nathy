<?php
// FIXED: Handle all redirects and actions BEFORE including header.php
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$conn = getDBConnection();

// Check if ID is provided BEFORE any output
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/tickets/my_tickets.php');
    exit;
}

$ticket_id = intval($_GET['id']);

// Handle delete action BEFORE any output
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $stmt = $conn->prepare("DELETE FROM ticket WHERE ticket_id = ? AND requester_user_id = ? AND status = 'OPEN'");
    $stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Ticket deleted successfully.';
        header('Location: ' . BASE_URL . 'pages/tickets/my_tickets.php');
        exit;
    }
    $stmt->close();
}

// Get ticket details
$stmt = $conn->prepare("
    SELECT t.*, pa.activity_name, d.department_name, d.department_id,
           u1.f_name as requester_fname, u1.l_name as requester_lname,
           u2.f_name as admin_fname, u2.l_name as admin_lname
    FROM ticket t
    JOIN project_activities pa ON t.activity_id = pa.activity_id
    JOIN department d ON pa.department_id = d.department_id
    JOIN users u1 ON t.requester_user_id = u1.user_id
    JOIN users u2 ON t.requested_user_id = u2.user_id
    WHERE t.ticket_id = ? AND t.requester_user_id = ?
");
$stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: ' . BASE_URL . 'pages/tickets/my_tickets.php');
    exit;
}

$ticket = $result->fetch_assoc();
$stmt->close();

// NOW include header.php (after all processing is complete)
$page_title = 'View Ticket';
require_once __DIR__ . '/../../includes/header.php';

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <i class="fas fa-ticket-alt"></i> Ticket #<?php echo str_pad($ticket['ticket_id'], 5, '0', STR_PAD_LEFT); ?>
        </div>
        <div>
            <?php echo getStatusBadge($ticket['status']); ?>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div>
                <h4 style="color: var(--primary-green); margin-bottom: 15px;">Ticket Information</h4>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($ticket['department_name']); ?></p>
                <p><strong>Activity:</strong> <?php echo htmlspecialchars($ticket['activity_name']); ?></p>
                <p><strong>Assigned Admin:</strong> <?php echo htmlspecialchars($ticket['admin_fname'] . ' ' . $ticket['admin_lname']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $ticket['quantity']; ?></p>
                <p><strong>Priority:</strong> <?php echo getPriorityBadge($ticket['priority']); ?></p>
                <?php if ($ticket['priority_notes']): ?>
                    <p><strong>Priority Notes:</strong><br><?php echo nl2br(htmlspecialchars($ticket['priority_notes'])); ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <h4 style="color: var(--primary-green); margin-bottom: 15px;">Timeline</h4>
                <p><strong>Created:</strong> <?php echo formatDate($ticket['created_at']); ?></p>
                <p><strong>Last Updated:</strong> <?php echo formatDate($ticket['updated_at']); ?></p>
                <?php if ($ticket['start_time']): ?>
                    <p><strong>Started:</strong> <?php echo formatDate($ticket['start_time']); ?></p>
                <?php endif; ?>
                <?php if ($ticket['end_time']): ?>
                    <p><strong>Completed:</strong> <?php echo formatDate($ticket['end_time']); ?></p>
                    <p><strong>Time Spent:</strong> <?php echo $ticket['time_spent']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--primary-green); margin-bottom: 10px;">Purpose/Action</h4>
            <p style="background: var(--light-gray); padding: 15px; border-radius: 4px; border-left: 4px solid var(--primary-green);">
                <?php echo nl2br(htmlspecialchars($ticket['purpose_action'])); ?>
            </p>
        </div>
        
        <?php if ($ticket['remarks']): ?>
            <div style="margin-bottom: 20px;">
                <h4 style="color: var(--primary-green); margin-bottom: 10px;">Admin Remarks</h4>
                <p style="background: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid var(--warning);">
                    <?php echo nl2br(htmlspecialchars($ticket['remarks'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <div class="flex gap-10" style="justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 2px solid var(--light-gray);">
            <a href="<?php echo BASE_URL; ?>pages/tickets/my_tickets.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to My Tickets
            </a>
            
            <?php if ($ticket['status'] == 'OPEN'): ?>
                <button onclick="sendTicket(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Send Ticket
                </button>
                <a href="<?php echo BASE_URL; ?>pages/tickets/edit.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button onclick="deleteTicket(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>