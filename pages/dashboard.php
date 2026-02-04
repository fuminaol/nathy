<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Get statistics
if ($_SESSION['role'] == 'employee') {
    // Employee dashboard
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requester_user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $total_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requester_user_id = ? AND status = 'PENDING'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $pending_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requester_user_id = ? AND status = 'IN_PROGRESS'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $in_progress_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requester_user_id = ? AND status = 'COMPLETED'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $completed_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
} else if ($_SESSION['role'] == 'admin') {
    // Admin dashboard
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requested_user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $total_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requested_user_id = ? AND status = 'PENDING'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $pending_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requested_user_id = ? AND status = 'IN_PROGRESS'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $in_progress_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ticket WHERE requested_user_id = ? AND status = 'COMPLETED'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $completed_tickets = $stmt->get_result()->fetch_assoc()['total'];
    
} else {
    // Superadmin dashboard
    $total_tickets = $conn->query("SELECT COUNT(*) as total FROM ticket")->fetch_assoc()['total'];
    $pending_tickets = $conn->query("SELECT COUNT(*) as total FROM ticket WHERE status = 'PENDING'")->fetch_assoc()['total'];
    $in_progress_tickets = $conn->query("SELECT COUNT(*) as total FROM ticket WHERE status = 'IN_PROGRESS'")->fetch_assoc()['total'];
    $completed_tickets = $conn->query("SELECT COUNT(*) as total FROM ticket WHERE status = 'COMPLETED'")->fetch_assoc()['total'];
}

// Get recent tickets
if ($_SESSION['role'] == 'employee') {
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name, u.f_name, u.l_name
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u ON t.requested_user_id = u.user_id
        WHERE t.requester_user_id = ?
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
} else if ($_SESSION['role'] == 'admin') {
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name, u.f_name, u.l_name
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u ON t.requester_user_id = u.user_id
        WHERE t.requested_user_id = ?
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare("
        SELECT t.*, pa.activity_name, d.department_name, u1.f_name as requester_fname, u1.l_name as requester_lname,
               u2.f_name as admin_fname, u2.l_name as admin_lname
        FROM ticket t
        JOIN project_activities pa ON t.activity_id = pa.activity_id
        JOIN department d ON pa.department_id = d.department_id
        JOIN users u1 ON t.requester_user_id = u1.user_id
        JOIN users u2 ON t.requested_user_id = u2.user_id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
}

$stmt->execute();
$recent_tickets = $stmt->get_result();

$conn->close();
?>

<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-chart-pie"></i> Dashboard Overview
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_tickets; ?></h3>
                    <p>Total Tickets</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_tickets; ?></h3>
                    <p>Pending Tickets</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $in_progress_tickets; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $completed_tickets; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-history"></i> Recent Tickets
    </div>
    <div class="card-body">
        <?php if ($recent_tickets->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Activity</th>
                            <th>Department</th>
                            <?php if ($_SESSION['role'] == 'employee'): ?>
                                <th>Admin</th>
                            <?php else: ?>
                                <th>Requester</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = $recent_tickets->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo str_pad($ticket['ticket_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($ticket['activity_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                                <td>
                                    <?php 
                                    if ($_SESSION['role'] == 'employee') {
                                        echo htmlspecialchars($ticket['f_name'] . ' ' . $ticket['l_name']);
                                    } else if ($_SESSION['role'] == 'superadmin') {
                                        echo htmlspecialchars($ticket['requester_fname'] . ' ' . $ticket['requester_lname']);
                                    } else {
                                        echo htmlspecialchars($ticket['f_name'] . ' ' . $ticket['l_name']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo getStatusBadge($ticket['status']); ?></td>
                                <td><?php echo getPriorityBadge($ticket['priority']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <?php if ($_SESSION['role'] == 'employee'): ?>
                                        <a href="<?php echo BASE_URL; ?>pages/tickets/view.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>pages/admin/ticket_details.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--medium-gray); padding: 40px 0;">
                <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                No tickets found. <?php if ($_SESSION['role'] == 'employee'): ?>Click "Create Ticket" to get started.<?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
