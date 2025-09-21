<?php
session_start();

// Set admin session if not set
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Admin';
    $_SESSION['user_email'] = 'admin@college.edu';
    $_SESSION['user_role'] = 'admin';
    $_SESSION['department_id'] = null;
}

require_once 'backend/db_connect.php';

$error = '';
$success = '';

// Handle ticket assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_ticket'])) {
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $departmentId = (int)($_POST['department_id'] ?? 0);

    if ($ticketId && $departmentId) {
        try {
            $stmt = $pdo->prepare('UPDATE tickets SET department_id = ?, status = ? WHERE id = ?');
            $stmt->execute([$departmentId, 'Assigned', $ticketId]);
            $success = 'Ticket assigned successfully!';
        } catch (PDOException $e) {
            $error = 'Error assigning ticket: ' . $e->getMessage();
        }
    }
}

// Get tickets - simplified query
try {
    $stmt = $pdo->prepare('SELECT t.*, u.username as student_name FROM tickets t LEFT JOIN users u ON t.student_id = u.id ORDER BY t.created_at DESC');
    $stmt->execute();
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    $tickets = [];
    $error = 'Database error: ' . $e->getMessage();
}

// Get departments - use existing departments only
try {
    $stmt = $pdo->query('SELECT DISTINCT id, name FROM departments WHERE id IS NOT NULL ORDER BY name');
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Service Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Service Tracker - Admin</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, Admin</span>
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Dashboard - Simple View</h2>
            <div>
                <a href="create_new_department.php" class="btn btn-warning me-2">
                    <i class="bi bi-person-plus"></i> Add Department User
                </a>
                <a href="admin_dashboard_enhanced.php" class="btn btn-success">Enhanced View</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Tickets (<?php echo count($tickets); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tickets)): ?>
                            <p class="text-muted">No tickets found. Students need to submit tickets first.</p>
                            <p><a href="student_dashboard.php" class="btn btn-primary">Go to Student Dashboard</a></p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Student</th>
                                            <th>Title</th>
                                            <th>Category</th>
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
                                                <td>
                                                    <span class="badge bg-<?php echo $ticket['status'] === 'Submitted' ? 'warning' : 'info'; ?>">
                                                        <?php echo $ticket['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($ticket['status'] === 'Submitted'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                            <select name="department_id" class="form-select form-select-sm d-inline-block" style="width: auto;" required>
                                                                <option value="">Select Department</option>
                                                                <?php foreach ($departments as $dept): ?>
                                                                    <option value="<?php echo $dept['id']; ?>">
                                                                        <?php echo htmlspecialchars($dept['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <button type="submit" name="assign_ticket" class="btn btn-sm btn-primary ms-1">Assign</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Assigned</span>
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
