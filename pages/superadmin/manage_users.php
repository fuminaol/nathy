<?php
// FIXED: Handle actions BEFORE including header.php to avoid "headers already sent" error
require_once __DIR__ . '/../../includes/functions.php';
requireRole('superadmin');

$conn = getDBConnection();

// Handle user status update BEFORE any output
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['user_id']);
    
    if ($action == 'suspend') {
        $conn->query("UPDATE users SET status = 'Suspended' WHERE user_id = $user_id");
        $_SESSION['success_message'] = 'User suspended successfully!';
    } elseif ($action == 'activate') {
        $conn->query("UPDATE users SET status = 'Active' WHERE user_id = $user_id");
        $_SESSION['success_message'] = 'User activated successfully!';
    }
    
    header('Location: ' . BASE_URL . 'pages/superadmin/manage_users.php');
    exit;
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_department = isset($_GET['department']) ? intval($_GET['department']) : '';

// Build query with filters
$query = "
    SELECT u.*, d.department_name 
    FROM users u 
    LEFT JOIN department d ON u.department_id = d.department_id 
    WHERE 1=1
";

$params = [];
$types = '';

if ($filter_status) {
    $query .= " AND u.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if ($filter_role) {
    $query .= " AND u.role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

if ($filter_department) {
    $query .= " AND u.department_id = ?";
    $params[] = $filter_department;
    $types .= 'i';
}

$query .= " ORDER BY u.created_at DESC";

// Execute query with filters
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query($query);
}

// Get all departments for filter dropdown
$departments = $conn->query("SELECT * FROM department ORDER BY department_name");

// NOW include header.php (after all action processing is complete)
$page_title = 'Manage Users';
require_once __DIR__ . '/../../includes/header.php';

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <i class="fas fa-users"></i> Manage Users
        </div>
        <a href="<?php echo BASE_URL; ?>pages/superadmin/create_user.php" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus"></i> Create New User
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter Section -->
        <div class="filter-section" style="background: var(--light-gray); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="color: var(--primary-green); margin-bottom: 15px;">
                <i class="fas fa-filter"></i> Filter Users
            </h4>
            <form method="GET" action="" id="filterForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                    <div class="form-group" style="margin: 0;">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- All Status --</option>
                            <option value="Active" <?php echo $filter_status == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Suspended" <?php echo $filter_status == 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label for="role">Role</label>
                        <select name="role" id="role" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- All Roles --</option>
                            <option value="employee" <?php echo $filter_role == 'employee' ? 'selected' : ''; ?>>Employee</option>
                            <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="superadmin" <?php echo $filter_role == 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label for="department">Department</label>
                        <select name="department" id="department" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- All Departments --</option>
                            <?php while ($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo $filter_department == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <?php if ($filter_status || $filter_role || $filter_department): ?>
                            <a href="<?php echo BASE_URL; ?>pages/superadmin/manage_users.php" class="btn btn-secondary" style="width: 100%;">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(trim($user['f_name'] . ' ' . $user['m_name'] . ' ' . $user['l_name'])); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] == 'superadmin' ? 'danger' : ($user['role'] == 'admin' ? 'warning' : 'secondary'); ?>">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user['status'] == 'Active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>pages/superadmin/edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <?php if ($user['status'] == 'Active'): ?>
                                            <a href="?action=suspend&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-sm btn-warning" 
                                               onclick="return confirm('Are you sure you want to suspend this user?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=activate&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-sm btn-success" 
                                               onclick="return confirm('Are you sure you want to activate this user?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--medium-gray);">
                                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                No users found matching the selected filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>