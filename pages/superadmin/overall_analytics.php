<?php
$page_title = 'Overall Analytics';
require_once __DIR__ . '/../../includes/header.php';
requireRole('superadmin');

$conn = getDBConnection();

// Overall statistics
$total_tickets = $conn->query("SELECT COUNT(*) as count FROM ticket")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_departments = $conn->query("SELECT COUNT(*) as count FROM department")->fetch_assoc()['count'];

// Status breakdown
$status_data = [];
$status_result = $conn->query("SELECT status, COUNT(*) as count FROM ticket GROUP BY status");
while ($row = $status_result->fetch_assoc()) {
    $status_data[$row['status']] = $row['count'];
}

// Department breakdown
$dept_data = [];
$dept_result = $conn->query("
    SELECT d.department_name, COUNT(t.ticket_id) as count 
    FROM department d 
    LEFT JOIN project_activities pa ON d.department_id = pa.department_id
    LEFT JOIN ticket t ON pa.activity_id = t.activity_id
    GROUP BY d.department_name
    ORDER BY count DESC
");
while ($row = $dept_result->fetch_assoc()) {
    $dept_data[] = $row;
}

// Top requesters
$top_requesters = $conn->query("
    SELECT u.f_name, u.l_name, u.email, COUNT(t.ticket_id) as count
    FROM users u
    LEFT JOIN ticket t ON u.user_id = t.requester_user_id
    WHERE u.role = 'employee'
    GROUP BY u.user_id
    ORDER BY count DESC
    LIMIT 10
");

// Average completion time
$avg_time = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_hours
    FROM ticket
    WHERE status = 'COMPLETED' AND start_time IS NOT NULL AND end_time IS NOT NULL
")->fetch_assoc();

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-chart-line"></i> Overall System Analytics
    </div>
    <div class="card-body">
        <!-- Overall Stats -->
        <h3 style="color: var(--primary-green); margin-bottom: 20px;">System Overview</h3>
        <div class="stats-grid" style="margin-bottom: 40px;">
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
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_departments; ?></h3>
                    <p>Departments</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $avg_time['avg_hours'] ? round($avg_time['avg_hours'], 1) : '0'; ?>h</h3>
                    <p>Avg. Completion Time</p>
                </div>
            </div>
        </div>
        
        <!-- Status Breakdown -->
        <h3 style="color: var(--primary-green); margin: 40px 0 20px 0;">Tickets by Status</h3>
        <div class="stats-grid" style="margin-bottom: 40px;">
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $status_data['OPEN'] ?? 0; ?></h3>
                    <p>Open</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $status_data['PENDING'] ?? 0; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $status_data['IN_PROGRESS'] ?? 0; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $status_data['ON_HOLD'] ?? 0; ?></h3>
                    <p>On Hold</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $status_data['COMPLETED'] ?? 0; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $status_data['REJECTED'] ?? 0; ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>
        
        <!-- Department Performance -->
        <h3 style="color: var(--primary-green); margin: 40px 0 20px 0;">Tickets by Department</h3>
        <div class="table-container" style="margin-bottom: 40px;">
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Total Tickets</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dept_data as $dept): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept['department_name']); ?></td>
                            <td><?php echo $dept['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Top Requesters -->
        <h3 style="color: var(--primary-green); margin: 40px 0 20px 0;">Top 10 Ticket Requesters</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Total Tickets</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $top_requesters->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['f_name'] . ' ' . $user['l_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['count']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>