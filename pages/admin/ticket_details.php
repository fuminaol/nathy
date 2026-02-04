<?php
// FIXED: Handle all redirects BEFORE including header.php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['admin', 'superadmin']);

$conn = getDBConnection();

// Check if ID is provided BEFORE any output
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/admin/manage_tickets.php');
    exit;
}

$ticket_id = intval($_GET['id']);

// Get ticket details BEFORE any output
if ($_SESSION['role'] == 'admin') {
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name,
               u1.f_name as requester_fname, u1.l_name as requester_lname, u1.email as requester_email,
               u2.f_name as admin_fname, u2.l_name as admin_lname
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u1 ON t.requester_user_id = u1.user_id
        JOIN users u2 ON t.requested_user_id = u2.user_id
        WHERE t.ticket_id = ? AND t.requested_user_id = ?
    ");
    $stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name,
               u1.f_name as requester_fname, u1.l_name as requester_lname, u1.email as requester_email,
               u2.f_name as admin_fname, u2.l_name as admin_lname
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u1 ON t.requester_user_id = u1.user_id
        JOIN users u2 ON t.requested_user_id = u2.user_id
        WHERE t.ticket_id = ?
    ");
    $stmt->bind_param("i", $ticket_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: ' . BASE_URL . 'pages/admin/manage_tickets.php');
    exit;
}

$ticket = $result->fetch_assoc();
$stmt->close();

// NOW include header.php (after all processing is complete)
$page_title = 'Ticket Details';
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
                <h4 style="color: var(--primary-green); margin-bottom: 15px;">Requester Information</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket['requester_fname'] . ' ' . $ticket['requester_lname']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($ticket['requester_email']); ?></p>
            </div>
            
            <div>
                <h4 style="color: var(--primary-green); margin-bottom: 15px;">Ticket Information</h4>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($ticket['department_name']); ?></p>
                <p><strong>Activity:</strong> <?php echo htmlspecialchars($ticket['activity_name']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $ticket['quantity']; ?></p>
                <p><strong>Priority:</strong> <?php echo getPriorityBadge($ticket['priority']); ?></p>
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
            <h4 style="color: var(--primary-green); margin-bottom: 10px;">Purpose/Action Required</h4>
            <p style="background: var(--light-gray); padding: 15px; border-radius: 4px; border-left: 4px solid var(--primary-green);">
                <?php echo nl2br(htmlspecialchars($ticket['purpose_action'])); ?>
            </p>
        </div>
        
        <?php if ($ticket['priority_notes']): ?>
            <div style="margin-bottom: 20px;">
                <h4 style="color: var(--primary-green); margin-bottom: 10px;">Priority Notes</h4>
                <p style="background: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid var(--warning);">
                    <?php echo nl2br(htmlspecialchars($ticket['priority_notes'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($ticket['remarks']): ?>
            <div style="margin-bottom: 20px;">
                <h4 style="color: var(--primary-green); margin-bottom: 10px;">Admin Remarks</h4>
                <p style="background: #d1ecf1; padding: 15px; border-radius: 4px; border-left: 4px solid var(--info);">
                    <?php echo nl2br(htmlspecialchars($ticket['remarks'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="flex gap-10" style="justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 2px solid var(--light-gray); flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>pages/admin/manage_tickets.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            
            <?php if ($ticket['status'] == 'PENDING'): ?>
                <button onclick="startTicket(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-success">
                    <i class="fas fa-play"></i> Start Ticket
                </button>
                <button onclick="showHoldModal(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-warning">
                    <i class="fas fa-pause"></i> Put on Hold
                </button>
                <button onclick="showRejectModal(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-danger">
                    <i class="fas fa-times-circle"></i> Reject
                </button>
            <?php endif; ?>
            
            <?php if ($ticket['status'] == 'IN_PROGRESS'): ?>
                <button onclick="completeTicket(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-success">
                    <i class="fas fa-check-circle"></i> Complete Ticket
                </button>
                <button onclick="showHoldModal(<?php echo $ticket['ticket_id']; ?>)" class="btn btn-warning">
                    <i class="fas fa-pause"></i> Put on Hold
                </button>
            <?php endif; ?>
            
            <?php if ($ticket['status'] == 'ON_HOLD'): ?>
                <button onclick="updateTicketStatus(<?php echo $ticket['ticket_id']; ?>, 'PENDING')" class="btn btn-info">
                    <i class="fas fa-redo"></i> Resume (Set to Pending)
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Hold Modal -->
<div id="holdModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Put Ticket on Hold</h2>
            <span class="close" onclick="hideModal('holdModal')">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="hold-ticket-id">
            <div class="form-group">
                <label for="hold-remarks">Reason for Hold (Optional)</label>
                <textarea id="hold-remarks" class="form-control" placeholder="Enter reason for putting ticket on hold..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="hideModal('holdModal')" class="btn btn-secondary">Cancel</button>
            <button onclick="submitHold()" class="btn btn-warning">Confirm Hold</button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Reject Ticket</h2>
            <span class="close" onclick="hideModal('rejectModal')">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="reject-ticket-id">
            <div class="form-group">
                <label for="reject-remarks" class="required">Reason for Rejection</label>
                <textarea id="reject-remarks" class="form-control" placeholder="Enter reason for rejection..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="hideModal('rejectModal')" class="btn btn-secondary">Cancel</button>
            <button onclick="submitReject()" class="btn btn-danger">Confirm Rejection</button>
        </div>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>