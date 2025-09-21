<?php
session_start();

// Set department session if not set
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_id'] = 2;
    $_SESSION['user_name'] = 'Library Head';
    $_SESSION['user_email'] = 'library@college.edu';
    $_SESSION['user_role'] = 'department';
    $_SESSION['department_id'] = 2;
}

require_once 'backend/db_connect.php';

$error = '';
$success = '';

// Handle ticket approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $comment = $_POST['comment'] ?? '';

    if ($ticketId && $action) {
        try {
            $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ? AND department_id = ?');
            $stmt->execute([$newStatus, $ticketId, $_SESSION['department_id']]);
            
            $success = 'Ticket ' . strtolower($action) . 'd successfully!';
        } catch (PDOException $e) {
            $error = 'Error updating ticket: ' . $e->getMessage();
        }
    }
}

// Get tickets assigned to this department - simplified query
try {
    $stmt = $pdo->prepare('SELECT t.*, u.username as student_name FROM tickets t 
                          LEFT JOIN users u ON t.student_id = u.id 
                          WHERE t.department_id = ? 
                          ORDER BY t.created_at DESC');
    $stmt->execute([$_SESSION['department_id']]);
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    $tickets = [];
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Dashboard | Service Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Service Tracker - Department</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a class="nav-link" href="index.html">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Assigned Tickets (<?php echo count($tickets); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tickets)): ?>
                            <div class="alert alert-info">
                                <h6>No tickets assigned to your department yet.</h6>
                                <p>Tickets need to be assigned by admin first.</p>
                                <a href="admin_simple.php" class="btn btn-primary">Go to Admin Dashboard</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Student</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr>
                                                <td>#<?php echo $ticket['id']; ?></td>
                                                <td><?php echo htmlspecialchars($ticket['student_name'] ?? 'Unknown'); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['category']); ?></td>
                                                <td title="<?php echo htmlspecialchars($ticket['description']); ?>">
                                                    <?php echo htmlspecialchars(substr($ticket['description'], 0, 50)) . '...'; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $ticket['status'] === 'Assigned' ? 'info' : 
                                                            ($ticket['status'] === 'Approved' ? 'success' : 'danger'); 
                                                    ?>">
                                                        <?php echo $ticket['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($ticket['status'] === 'Assigned'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="comment" value="Approved by department">
                                                            <button type="submit" name="update_ticket" class="btn btn-sm btn-success me-1">Approve</button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="comment" value="Rejected by department">
                                                            <button type="submit" name="update_ticket" class="btn btn-sm btn-danger">Reject</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Processed</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
