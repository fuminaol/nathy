<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$current_user = getUserInfo($_SESSION['user_id']);
$notification_count = getNotificationCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>NEECO Ticketing System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <!-- UPDATED: Using NEECO banner image with white background container -->
            <div class="logo-container">
                <div class="logo-bg">
                    <img src="<?php echo BASE_URL; ?>assets/images/NEECO_new_banner_5.png" 
                         alt="NEECO II - Area 1" 
                         class="header-logo"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                </div>
                <div class="logo-text" style="display: none;">NEECO II - Area 1</div>
            </div>
        </div>
        <div class="header-right">
            <div class="user-info" onclick="window.location.href='<?php echo BASE_URL; ?>pages/profile.php'">
                <?php if ($current_user['profile_photo']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($current_user['profile_photo']); ?>" alt="Profile" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar" style="background: var(--primary-green); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        <?php echo strtoupper(substr($current_user['f_name'], 0, 1) . substr($current_user['l_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="user-name">Hello, <?php echo htmlspecialchars($current_user['f_name']); ?></div>
                </div>
            </div>
        </div>
    </header>