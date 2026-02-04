// Toggle sidebar
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    sidebar.classList.toggle('show');
    mainContent.classList.toggle('expanded');
    
    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Restore sidebar state
document.addEventListener('DOMContentLoaded', function() {
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        document.querySelector('.sidebar')?.classList.add('collapsed');
        document.querySelector('.main-content')?.classList.add('expanded');
    }
});

// Toggle password visibility
function togglePassword(inputId, buttonElement) {
    const input = document.getElementById(inputId);
    const icon = buttonElement.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Show/Hide priority notes based on priority selection
function togglePriorityNotes() {
    const prioritySelect = document.getElementById('priority');
    const priorityNotesGroup = document.getElementById('priority-notes-group');
    
    if (prioritySelect && priorityNotesGroup) {
        if (prioritySelect.value && prioritySelect.value !== '') {
            priorityNotesGroup.style.display = 'block';
        } else {
            priorityNotesGroup.style.display = 'none';
            document.getElementById('priority_notes').value = '';
        }
    }
}

// Load activities based on department
function loadActivities(departmentId) {
    const activitySelect = document.getElementById('activity_id');
    
    if (!activitySelect) return;
    
    activitySelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`${window.location.origin}/ticketing_system/api/get_activities.php?department_id=${departmentId}`)
        .then(response => response.json())
        .then(data => {
            activitySelect.innerHTML = '<option value="">-- Select Activity --</option>';
            data.forEach(activity => {
                const option = document.createElement('option');
                option.value = activity.activity_id;
                option.textContent = activity.activity_name;
                activitySelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading activities:', error);
            activitySelect.innerHTML = '<option value="">Error loading activities</option>';
        });
}

// Modal functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Confirm action
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Send ticket
function sendTicket(ticketId) {
    confirmAction('Are you sure you want to send this ticket?', function() {
        fetch(`${window.location.origin}/ticketing_system/api/send_ticket.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ticket_id: ticketId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ticket sent successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending the ticket.');
        });
    });
}

// Delete ticket
function deleteTicket(ticketId) {
    confirmAction('Are you sure you want to delete this ticket? This action cannot be undone.', function() {
        window.location.href = `${window.location.origin}/ticketing_system/pages/tickets/view.php?id=${ticketId}&action=delete`;
    });
}

// Update ticket status
function updateTicketStatus(ticketId, status, remarks = '') {
    let message = `Are you sure you want to change the status to ${status}?`;
    
    confirmAction(message, function() {
        fetch(`${window.location.origin}/ticketing_system/api/update_ticket_status.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                ticket_id: ticketId, 
                status: status,
                remarks: remarks 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    });
}

// Start ticket
function startTicket(ticketId) {
    confirmAction('Are you sure you want to start this ticket?', function() {
        fetch(`${window.location.origin}/ticketing_system/api/start_ticket.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ticket_id: ticketId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ticket started successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while starting the ticket.');
        });
    });
}

// Complete ticket
function completeTicket(ticketId) {
    confirmAction('Are you sure you want to complete this ticket?', function() {
        fetch(`${window.location.origin}/ticketing_system/api/complete_ticket.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ticket_id: ticketId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ticket completed successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the ticket.');
        });
    });
}

// Show hold modal
function showHoldModal(ticketId) {
    document.getElementById('hold-ticket-id').value = ticketId;
    showModal('holdModal');
}

// Submit hold status
function submitHold() {
    const ticketId = document.getElementById('hold-ticket-id').value;
    const remarks = document.getElementById('hold-remarks').value;
    
    hideModal('holdModal');
    updateTicketStatus(ticketId, 'ON_HOLD', remarks);
}

// Show reject modal
function showRejectModal(ticketId) {
    document.getElementById('reject-ticket-id').value = ticketId;
    showModal('rejectModal');
}

// Submit reject status
function submitReject() {
    const ticketId = document.getElementById('reject-ticket-id').value;
    const remarks = document.getElementById('reject-remarks').value;
    
    if (!remarks.trim()) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    hideModal('rejectModal');
    updateTicketStatus(ticketId, 'REJECTED', remarks);
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = 'var(--danger)';
        } else {
            input.style.borderColor = 'var(--border-gray)';
        }
    });
    
    return isValid;
}

// Filter table
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        let txtValue = tr[i].textContent || tr[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Check for notifications (for admin/employee)
function checkNotifications() {
    fetch(`${window.location.origin}/ticketing_system/api/get_notifications.php`)
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                // Update notification badge if exists
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                }
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Check notifications every 30 seconds
setInterval(checkNotifications, 30000);

// Logout confirmation
function confirmLogout() {
    confirmAction('Are you sure you want to logout?', function() {
        window.location.href = `${window.location.origin}/ticketing_system/auth/logout.php`;
    });
}
