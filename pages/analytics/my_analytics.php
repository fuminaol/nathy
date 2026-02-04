<?php
$page_title = 'My Tickets Analytics';
require_once __DIR__ . '/../../includes/header.php';

$conn = getDBConnection();

// Get ticket statistics for current user
if ($_SESSION['role'] == 'employee') {
    $user_id = $_SESSION['user_id'];
    $is_requester = true;
} else {
    $user_id = $_SESSION['user_id'];
    $is_requester = false;
}

// Status breakdown
$status_query = $is_requester 
    ? "SELECT status, COUNT(*) as count FROM ticket WHERE requester_user_id = ? GROUP BY status"
    : "SELECT status, COUNT(*) as count FROM ticket WHERE requested_user_id = ? GROUP BY status";

$stmt = $conn->prepare($status_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$status_result = $stmt->get_result();

$status_data = [];
while ($row = $status_result->fetch_assoc()) {
    $status_data[$row['status']] = $row['count'];
}
$stmt->close();

// Department breakdown
$dept_query = $is_requester
    ? "SELECT d.department_name, COUNT(*) as count 
       FROM ticket t 
       JOIN project_activities pa ON t.activity_id = pa.activity_id 
       JOIN department d ON pa.department_id = d.department_id 
       WHERE t.requester_user_id = ? 
       GROUP BY d.department_name"
    : "SELECT d.department_name, COUNT(*) as count 
       FROM ticket t 
       JOIN project_activities pa ON t.activity_id = pa.activity_id 
       JOIN department d ON pa.department_id = d.department_id 
       WHERE t.requested_user_id = ? 
       GROUP BY d.department_name";

$stmt = $conn->prepare($dept_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$dept_result = $stmt->get_result();

$dept_data = [];
while ($row = $dept_result->fetch_assoc()) {
    $dept_data[] = $row;
}
$stmt->close();

// Monthly tickets (last 12 months)
$monthly_query = $is_requester
    ? "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
       FROM ticket 
       WHERE requester_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
       ORDER BY month"
    : "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
       FROM ticket 
       WHERE requested_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
       ORDER BY month";

$stmt = $conn->prepare($monthly_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$monthly_result = $stmt->get_result();

$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = $row;
}
$stmt->close();

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-chart-bar"></i> My Tickets Analytics
    </div>
    <div class="card-body">
        <!-- Status Statistics -->
        <h3 style="color: var(--primary-green); margin-bottom: 20px;">Tickets by Status</h3>
        <div class="stats-grid" style="margin-bottom: 40px;">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--medium-gray);">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $status_data['OPEN'] ?? 0; ?></h3>
                    <p>Open</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $status_data['PENDING'] ?? 0; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $status_data['IN_PROGRESS'] ?? 0; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ff9800;">
                    <i class="fas fa-pause"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $status_data['ON_HOLD'] ?? 0; ?></h3>
                    <p>On Hold</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $status_data['COMPLETED'] ?? 0; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $status_data['REJECTED'] ?? 0; ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>
        
        <!-- Department Breakdown -->
        <?php if (!empty($dept_data)): ?>
        <h3 style="color: var(--primary-green); margin: 40px 0 20px 0;">Tickets by Department</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Number of Tickets</th>
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
        <?php endif; ?>
        
        <!-- Monthly Trend -->
        <?php if (!empty($monthly_data)): ?>
        <h3 style="color: var(--primary-green); margin: 40px 0 20px 0;">Monthly Ticket Trend (Last 12 Months)</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Number of Tickets</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_data as $month): ?>
                        <tr>
                            <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                            <td><?php echo $month['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>