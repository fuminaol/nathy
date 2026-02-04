<?php
// FIXED: Process form submission BEFORE including header.php to avoid "headers already sent" error
require_once __DIR__ . '/../../includes/functions.php';
requireRole('superadmin');

$conn = getDBConnection();

$error = '';

// Handle POST request before any output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $f_name = trim($_POST['f_name']);
    $m_name = trim($_POST['m_name']);
    $l_name = trim($_POST['l_name']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    
    // Validate admin creation - only one admin per department
    if ($role == 'admin' && $department_id) {
        $check = $conn->query("SELECT user_id FROM users WHERE department_id = $department_id AND role = 'admin' AND status = 'Active'");
        if ($check->num_rows > 0) {
            $error = 'This department already has an active admin. Please select a different department or demote the existing admin first.';
        }
    }
    
    if (empty($error)) {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, f_name, m_name, l_name, password, role, department_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->bind_param("sssssssi", $username, $email, $f_name, $m_name, $l_name, $hashed_password, $role, $department_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'User created successfully!';
                header('Location: ' . BASE_URL . 'pages/superadmin/manage_users.php');
                exit;
            } else {
                $error = 'Error creating user: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Get departments for the form
$departments = $conn->query("SELECT * FROM department ORDER BY department_name");

// NOW include header.php (after all POST processing is complete)
$page_title = 'Create User';
require_once __DIR__ . '/../../includes/header.php';

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus"></i> Create New User
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
                    <label for="username" class="required">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="f_name" class="required">First Name</label>
                    <input type="text" name="f_name" id="f_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="m_name">Middle Name</label>
                    <input type="text" name="m_name" id="m_name" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="l_name" class="required">Last Name</label>
                    <input type="text" name="l_name" id="l_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="required">Password</label>
                    <div class="password-toggle">
                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small style="color: var(--medium-gray);">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="role" class="required">Role</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <option value="employee">Employee</option>
                        <option value="admin">Admin</option>
                    </select>
                    <small style="color: var(--medium-gray);">Only one admin allowed per department</small>
                </div>
                
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">-- Select Department --</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['department_id']; ?>">
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
                    <i class="fas fa-save"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>