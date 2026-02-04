<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav>
        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php if (hasRole(['employee', 'admin', 'superadmin'])): ?>
            <li>
                <a href="<?php echo BASE_URL; ?>pages/tickets/create.php" class="<?php echo $current_page == 'create.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Ticket</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo BASE_URL; ?>pages/tickets/my_tickets.php" class="<?php echo $current_page == 'my_tickets.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i>
                    <span>My Tickets</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasRole(['admin', 'superadmin'])): ?>
            <li>
                <a href="<?php echo BASE_URL; ?>pages/admin/manage_tickets.php" class="<?php echo $current_page == 'manage_tickets.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Manage Tickets</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="<?php echo BASE_URL; ?>pages/analytics/my_analytics.php" class="<?php echo $current_page == 'my_analytics.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>My Tickets Analytics</span>
                </a>
            </li>
            
            <?php if (hasRole('superadmin')): ?>
            <li>
                <a href="<?php echo BASE_URL; ?>pages/superadmin/overall_analytics.php" class="<?php echo $current_page == 'overall_analytics.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Overall Analytics</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo BASE_URL; ?>pages/superadmin/manage_users.php" class="<?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="<?php echo BASE_URL; ?>pages/profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            
            <li>
                <a href="javascript:void(0)" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<div class="main-content">
