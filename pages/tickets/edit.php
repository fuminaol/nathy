<?php
// FIXED: Handle redirects and POST processing BEFORE including header.php
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$conn = getDBConnection();

// Check if ID is provided BEFORE any output
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/tickets/my_tickets.php');
    exit;
}

$ticket_id = intval($_GET['id']);

// Get ticket details BEFORE any output to check if it exists
$stmt = $conn->prepare("
    SELECT t.*, pa.activity_name, pa.department_id, d.department_name
    FROM ticket t
    JOIN project_activities pa ON t.activity_id = pa.activity_id
    JOIN department d ON pa.department_id = d.department_id
    WHERE t.ticket_id = ? AND t.requester_user_id = ? AND t.status = 'OPEN'
");
$stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: ' . BASE_URL . 'pages/tickets/my_tickets.php');
    exit;
}

$ticket = $result->fetch_assoc();
$stmt->close();

$error = '';

// Handle POST request before any output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_id = $_POST['department_id'];
    $activity_id = $_POST['activity_id'];
    $purpose_action = trim($_POST['purpose_action']);
    $quantity = intval($_POST['quantity']);
    $priority = !empty($_POST['priority']) ? $_POST['priority'] : null;
    $priority_notes = !empty($_POST['priority_notes']) ? trim($_POST['priority_notes']) : null;
    
    // Get admin of selected department
    $admin = getAdminOfDepartment($department_id);
    
    if (!$admin) {
        $error = 'No admin found for the selected department.';
    } else {
        $requested_user_id = $admin['user_id'];
        
        $stmt = $conn->prepare("UPDATE ticket SET requested_user_id = ?, activity_id = ?, purpose_action = ?, quantity = ?, priority = ?, priority_notes = ? WHERE ticket_id = ? AND requester_user_id = ? AND status = 'OPEN'");
        $stmt->bind_param("iisisiii", $requested_user_id, $activity_id, $purpose_action, $quantity, $priority, $priority_notes, $ticket_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Ticket updated successfully!';
            header('Location: ' . BASE_URL . 'pages/tickets/view.php?id=' . $ticket_id);
            exit;
        } else {
            $error = 'Error updating ticket: ' . $conn->error;
        }
        
        $stmt->close();
    }
}

// Get all departments
$departments = $conn->query("SELECT * FROM department ORDER BY department_name");

// Get activities for current department
$activities = $conn->query("SELECT * FROM project_activities WHERE department_id = " . $ticket['department_id'] . " ORDER BY activity_name");

// NOW include header.php (after all processing is complete)
$page_title = 'Edit Ticket';
require_once __DIR__ . '/../../includes/header.php';

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> Edit Ticket #<?php echo str_pad($ticket['ticket_id'], 5, '0', STR_PAD_LEFT); ?>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="department_id" class="required">Department</label>
                <select name="department_id" id="department_id" class="form-control" required onchange="loadActivities(this.value)">
                    <option value="">-- Select Department --</option>
                    <?php while ($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['department_id']; ?>" <?php echo $dept['department_id'] == $ticket['department_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="activity_id" class="required">Project/Activity</label>
                <select name="activity_id" id="activity_id" class="form-control" required>
                    <?php while ($act = $activities->fetch_assoc()): ?>
                        <option value="<?php echo $act['activity_id']; ?>" <?php echo $act['activity_id'] == $ticket['activity_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($act['activity_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="purpose_action" class="required">Purpose/Action to be Taken</label>
                <textarea name="purpose_action" id="purpose_action" class="form-control" required><?php echo htmlspecialchars($ticket['purpose_action']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="quantity" class="required">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" required value="<?php echo $ticket['quantity']; ?>">
            </div>
            
            <div class="form-group">
                <label for="priority">Priority Level</label>
                <select name="priority" id="priority" class="form-control" onchange="togglePriorityNotes()">
                    <option value="">-- No Priority --</option>
                    <option value="LOW" <?php echo $ticket['priority'] == 'LOW' ? 'selected' : ''; ?>>Low</option>
                    <option value="MEDIUM" <?php echo $ticket['priority'] == 'MEDIUM' ? 'selected' : ''; ?>>Medium</option>
                    <option value="HIGH" <?php echo $ticket['priority'] == 'HIGH' ? 'selected' : ''; ?>>High</option>
                    <option value="CRITICAL" <?php echo $ticket['priority'] == 'CRITICAL' ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>
            
            <div class="form-group" id="priority-notes-group" style="display: <?php echo $ticket['priority'] ? 'block' : 'none'; ?>;">
                <label for="priority_notes">Priority Notes</label>
                <textarea name="priority_notes" id="priority_notes" class="form-control"><?php echo htmlspecialchars($ticket['priority_notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="flex gap-10" style="justify-content: flex-end;">
                <a href="<?php echo BASE_URL; ?>pages/tickets/view.php?id=<?php echo $ticket_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Ticket
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>