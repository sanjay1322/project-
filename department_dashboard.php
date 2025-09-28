<?php
require_once 'backend/db_connect.php';
require_once 'backend/auth.php';

// Require department role
requireRole(['department']);

$user = getCurrentUser();
$error = '';
$success = '';

// Handle ticket actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $comment = sanitizeInput($_POST['comment'] ?? '');

    if (!$ticketId) {
        $error = 'Invalid ticket ID.';
    } else {
        try {
            // Get current status before making changes
            $stmt = $pdo->prepare('SELECT status FROM tickets WHERE id = ? AND department_id = ?');
            $stmt->execute([$ticketId, $user['department_id']]);
            $ticket = $stmt->fetch();
            $oldStatus = $ticket ? $ticket['status'] : 'Assigned';

            switch ($action) {
                case 'approve':
                    $stmt = $pdo->prepare('UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND department_id = ?');
                    $stmt->execute(['Approved', $ticketId, $user['department_id']]);
                    $stmt = $pdo->prepare('INSERT INTO ticket_history (ticket_id, changed_by, old_status, new_status, comment) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$ticketId, $user['id'], $oldStatus, 'Approved', $comment]);
                    $success = 'Ticket approved successfully!';
                    break;

                case 'reject':
                    $stmt = $pdo->prepare('UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND department_id = ?');
                    $stmt->execute(['Rejected', $ticketId, $user['department_id']]);
                    $stmt = $pdo->prepare('INSERT INTO ticket_history (ticket_id, changed_by, old_status, new_status, comment) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$ticketId, $user['id'], $oldStatus, 'Rejected', $comment]);
                    $success = 'Ticket rejected!';
                    break;

                case 'under_review':
                    $stmt = $pdo->prepare('UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND department_id = ?');
                    $stmt->execute(['Under Review', $ticketId, $user['department_id']]);
                    $stmt = $pdo->prepare('INSERT INTO ticket_history (ticket_id, changed_by, old_status, new_status, comment) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$ticketId, $user['id'], $oldStatus, 'Under Review', $comment]);
                    $success = 'Ticket marked as under review!';
                    break;
            }
        } catch (PDOException $e) {
            $error = 'Error processing request. Please try again.';
        }
    }
}

// Get tickets assigned to this department
try {
    $stmt = $pdo->prepare('SELECT t.*, u.username as student_name, u.email as student_email FROM tickets t LEFT JOIN users u ON t.student_id = u.id WHERE t.department_id = ? ORDER BY t.created_at DESC');
    $stmt->execute([$user['department_id']]);
    $tickets = $stmt->fetchAll();

    // Get department name
    $stmt = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
    $stmt->execute([$user['department_id']]);
    $department = $stmt->fetch();

} catch (PDOException $e) {
    $tickets = [];
    $department = ['name' => 'Unknown'];
    $error = 'Error loading tickets.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Dashboard | Service Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-submitted { background-color: #ffc107; color: #000; }
        .status-assigned { background-color: #17a2b8; color: #fff; }
        .status-under-review { background-color: #6f42c1; color: #fff; } /* New Style */
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
        
        .back-home {
            position: fixed;
            bottom: 15px;
            left: 15px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            transition: 0.3s;
        }
        .back-home:hover {
            color: #ff5722;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Service Tracker - <?php echo htmlspecialchars($department['name']); ?></a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <a class="nav-link" href="backend/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Assigned Tickets</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tickets)): ?>
                            <p class="text-muted">No tickets assigned to your department.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Student</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>PDF</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr>
                                                <td>#<?php echo $ticket['id']; ?></td>
                                                <td><?php echo htmlspecialchars($ticket['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($ticket['description'], 0, 100)) . '...'; ?></td>
                                                <td>
                                                    <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                                        <?php echo htmlspecialchars($ticket['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($ticket['pdf_path']): ?>
                                                        <a href="backend/download_pdf.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">Download</a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (in_array($ticket['status'], ['Assigned', 'Under Review'])): ?>
                                                        <button class="btn btn-sm btn-success me-1" onclick="showActionModal(<?php echo $ticket['id']; ?>, 'approve')">Approve</button>
                                                        <button class="btn btn-sm btn-danger me-1" onclick="showActionModal(<?php echo $ticket['id']; ?>, 'reject')">Reject</button>
                                                        <button class="btn btn-sm btn-info" onclick="showActionModal(<?php echo $ticket['id']; ?>, 'under_review')">Review</button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalTitle">Action Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="ticket_id" id="modalTicketId">
                        <input type="hidden" name="action" id="modalAction">
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment (Optional)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Add a comment about this action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="modalSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="index.html" class="back-home">← Back to Home</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showActionModal(ticketId, action) {
            document.getElementById('modalTicketId').value = ticketId;
            document.getElementById('modalAction').value = action;
            
            const title = document.getElementById('actionModalTitle');
            const submitBtn = document.getElementById('modalSubmitBtn');
            
            if (action === 'approve') {
                title.textContent = 'Approve Ticket';
                submitBtn.textContent = 'Approve';
                submitBtn.className = 'btn btn-success';
            } else if (action === 'reject') {
                title.textContent = 'Reject Ticket';
                submitBtn.textContent = 'Reject';
                submitBtn.className = 'btn btn-danger';
            } else if (action === 'under_review') {
                title.textContent = 'Mark as Under Review';
                submitBtn.textContent = 'Mark as Under Review';
                submitBtn.className = 'btn btn-info';
            }
            
            new bootstrap.Modal(document.getElementById('actionModal')).show();
        }
    </script>
</body>
</html>
