<?php
// FIXED: Handle redirects and POST processing BEFORE including header.php
require_once __DIR__ . '/../../includes/functions.php';
requireRole('superadmin');

$conn = getDBConnection();

// Check if ID is provided BEFORE any output
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/superadmin/manage_users.php');
    exit;
}

$user_id = intval($_GET['id']);

$error = '';

// Handle POST request before any output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $f_name = trim($_POST['f_name']);
    $m_name = trim($_POST['m_name']);
    $l_name = trim($_POST['l_name']);
    $role = $_POST['role'];
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    
    // Validate admin change
    if ($role == 'admin' && $department_id) {
        $check = $conn->query("SELECT user_id FROM users WHERE department_id = $department_id AND role = 'admin' AND status = 'Active' AND user_id != $user_id");
        if ($check->num_rows > 0) {
            $error = 'This department already has an active admin.';
        }
    }
    
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET email = ?, f_name = ?, m_name = ?, l_name = ?, role = ?, department_id = ? WHERE user_id = ?");
        $stmt->bind_param("sssssii", $email, $f_name, $m_name, $l_name, $role, $department_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'User updated successfully!';
            header('Location: ' . BASE_URL . 'pages/superadmin/manage_users.php');
            exit;
        } else {
            $error = 'Error updating user: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get user info
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

if (!$user) {
    header('Location: ' . BASE_URL . 'pages/superadmin/manage_users.php');
    exit;
}

// Get departments
$departments = $conn->query("SELECT * FROM department ORDER BY department_name");

// NOW include header.php (after all processing is complete)
$page_title = 'Edit User';
require_once __DIR__ . '/../../includes/header.php';

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-edit"></i> Edit User
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small style="color: var(--medium-gray);">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="f_name" class="required">First Name</label>
                    <input type="text" name="f_name" id="f_name" class="form-control" value="<?php echo htmlspecialchars($user['f_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="m_name">Middle Name</label>
                    <input type="text" name="m_name" id="m_name" class="form-control" value="<?php echo htmlspecialchars($user['m_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="l_name" class="required">Last Name</label>
                    <input type="text" name="l_name" id="l_name" class="form-control" value="<?php echo htmlspecialchars($user['l_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="role" class="required">Role</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <option value="employee" <?php echo $user['role'] == 'employee' ? 'selected' : ''; ?>>Employee</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <?php if ($user['role'] == 'superadmin'): ?>
                            <option value="superadmin" selected>Super Admin</option>
                        <?php endif; ?>
                    </select>
                    <small style="color: var(--medium-gray);">Only one admin allowed per department</small>
                </div>
                
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">-- Select Department --</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['department_id']; ?>" <?php echo $user['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['department_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-10" style="justify-content: flex-end; margin-top: 30px;">
                <a href="<?php echo BASE_URL; ?>pages/superadmin/manage_users.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>