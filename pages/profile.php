<?php
$page_title = 'Profile';
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

$success = '';
$error = '';

// Handle profile photo upload
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_photo']['name'];
        $filetype = $_FILES['profile_photo']['type'];
        $filesize = $_FILES['profile_photo']['size'];
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        } elseif ($filesize > 2 * 1024 * 1024) {
            $error = 'File size must be less than 2MB.';
        } else {
            $photo_data = file_get_contents($_FILES['profile_photo']['tmp_name']);
            
            $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
            $stmt->bind_param("si", $photo_data, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Profile photo updated successfully!';
            } else {
                $error = 'Error updating profile photo.';
            }
            $stmt->close();
        }
    }
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $f_name = trim($_POST['f_name']);
    $m_name = trim($_POST['m_name']);
    $l_name = trim($_POST['l_name']);
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("UPDATE users SET f_name = ?, m_name = ?, l_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $f_name, $m_name, $l_name, $email, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $success = 'Profile updated successfully!';
        $_SESSION['full_name'] = trim($f_name . ' ' . $l_name);
    } else {
        $error = 'Error updating profile.';
    }
    $stmt->close();
}

// Handle password change
if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($old_password, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Error changing password.';
        }
        $stmt->close();
    }
}

// Get user info
$user = getUserInfo($_SESSION['user_id']);

$conn->close();
?>

<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="profile-header">
    <?php if ($user['profile_photo']): ?>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_photo']); ?>" alt="Profile" class="profile-avatar">
    <?php else: ?>
        <div class="profile-avatar" style="background: var(--primary-green); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: 600;">
            <?php echo strtoupper(substr($user['f_name'], 0, 1) . substr($user['l_name'], 0, 1)); ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-details">
        <h2><?php echo htmlspecialchars($user['f_name'] . ' ' . $user['m_name'] . ' ' . $user['l_name']); ?></h2>
        <p><i class="fas fa-user-tag"></i> <strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <?php if ($user['department_name']): ?>
            <p><i class="fas fa-building"></i> <strong>Department:</strong> <?php echo htmlspecialchars($user['department_name']); ?></p>
        <?php endif; ?>
        <p><i class="fas fa-calendar"></i> <strong>Member Since:</strong> <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
    <!-- Update Profile Photo -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-camera"></i> Update Profile Photo
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_photo">Choose Photo (Max 2MB)</label>
                    <input type="file" name="profile_photo" id="profile_photo" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" name="upload_photo" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Photo
                </button>
            </form>
        </div>
    </div>
    
    <!-- Update Profile Info -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-edit"></i> Update Profile Information
        </div>
        <div class="card-body">
            <form method="POST">
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
                    <label for="email" class="required">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-key"></i> Change Password
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label for="old_password" class="required">Current Password</label>
                    <div class="password-toggle">
                        <input type="password" name="old_password" id="old_password" class="form-control" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('old_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="required">New Password</label>
                    <div class="password-toggle">
                        <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('new_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small style="color: var(--medium-gray);">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="required">Confirm New Password</label>
                    <div class="password-toggle">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="change_password" class="btn btn-warning">
                    <i class="fas fa-lock"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>