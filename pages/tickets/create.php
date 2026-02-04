<?php
$page_title = 'Create Ticket';
require_once __DIR__ . '/../../includes/header.php';

$conn = getDBConnection();

$success = '';
$error = '';

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
        $error = 'No admin found for the selected department. Please contact the administrator.';
    } else {
        $requested_user_id = $admin['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO ticket (requester_user_id, requested_user_id, activity_id, purpose_action, quantity, priority, priority_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'OPEN')");
        // FIXED: Changed type string from "iiisis" to "iiisiss"
        // Types: i=integer, s=string
        // Variables: user_id(i), requested_user_id(i), activity_id(i), purpose_action(s), quantity(i), priority(s), priority_notes(s)
        $stmt->bind_param("iiisiss", $_SESSION['user_id'], $requested_user_id, $activity_id, $purpose_action, $quantity, $priority, $priority_notes);
        
        if ($stmt->execute()) {
            $success = 'Ticket created successfully! You can now send, edit, or delete it.';
            $ticket_id = $conn->insert_id;
            header('Location: ' . BASE_URL . 'pages/tickets/view.php?id=' . $ticket_id);
            exit;
        } else {
            $error = 'Error creating ticket: ' . $conn->error;
        }
        
        $stmt->close();
    }
}

// Get all departments
$departments = $conn->query("SELECT * FROM department ORDER BY department_name");

$conn->close();
?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> Create New Ticket
    </div>
    <div class="card-body">
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
        
        <form method="POST" action="" id="createTicketForm">
            <div class="form-group">
                <label for="department_id" class="required">Department</label>
                <select name="department_id" id="department_id" class="form-control" required onchange="loadActivities(this.value)">
                    <option value="">-- Select Department --</option>
                    <?php while ($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['department_id']; ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="activity_id" class="required">Project/Activity</label>
                <select name="activity_id" id="activity_id" class="form-control" required>
                    <option value="">-- Select Department First --</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="purpose_action" class="required">Purpose/Action to be Taken</label>
                <textarea name="purpose_action" id="purpose_action" class="form-control" required placeholder="Describe the purpose or action needed..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="quantity" class="required">Quantity (Number of items/people needed)</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" required value="1">
                <small style="color: var(--medium-gray);">Enter the number of items, files, or people required for this task</small>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority Level (Optional)</label>
                <select name="priority" id="priority" class="form-control" onchange="togglePriorityNotes()">
                    <option value="">-- No Priority --</option>
                    <option value="LOW">Low</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="HIGH">High</option>
                    <option value="CRITICAL">Critical</option>
                </select>
            </div>
            
            <div class="form-group" id="priority-notes-group" style="display: none;">
                <label for="priority_notes">Priority Notes</label>
                <textarea name="priority_notes" id="priority_notes" class="form-control" placeholder="Explain why this priority level is needed..."></textarea>
            </div>
            
            <div class="flex gap-10" style="justify-content: flex-end;">
                <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>