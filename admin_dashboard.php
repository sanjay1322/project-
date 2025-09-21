<?php
require_once 'backend/db_connect.php';
require_once 'backend/auth.php';

// Require admin role
requireRole(['admin']);

$user = getCurrentUser();
$error = '';
$success = '';

// Handle ticket assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_ticket'])) {
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $departmentId = (int)($_POST['department_id'] ?? 0);

    if (!$ticketId || !$departmentId) {
        $error = 'Invalid ticket or department selection.';
    } else {
        try {
            // Update ticket status and department
            $stmt = $pdo->prepare('UPDATE tickets SET department_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$departmentId, 'Assigned', $ticketId]);

            // Log status change
            $stmt = $pdo->prepare('INSERT INTO ticket_history (ticket_id, changed_by, old_status, new_status, comment) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$ticketId, $user['id'], 'Submitted', 'Assigned', 'Ticket assigned to department by admin']);

            $success = 'Ticket assigned successfully!';
        } catch (PDOException $e) {
            $error = 'Error assigning ticket. Please try again.';
        }
    }
}

// Get all tickets with filters
$statusFilter = $_GET['status'] ?? '';
$departmentFilter = $_GET['department'] ?? '';

$whereConditions = [];
$params = [];

if ($statusFilter) {
    $whereConditions[] = 't.status = ?';
    $params[] = $statusFilter;
}

if ($departmentFilter) {
    $whereConditions[] = 't.department_id = ?';
    $params[] = $departmentFilter;
}

$whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.username as student_name, d.name as department_name 
        FROM tickets t 
        LEFT JOIN users u ON t.student_id = u.id 
        LEFT JOIN departments d ON t.department_id = d.id 
        $whereClause
        ORDER BY t.created_at DESC
    ");
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();

    // Get departments for filter and assignment dropdown - ensure no duplicates
    $stmt = $pdo->query('SELECT DISTINCT id, name FROM departments WHERE id IS NOT NULL ORDER BY name');
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $tickets = [];
    $departments = [];
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Service Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        .status-badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
        }
        .status-submitted { background-color: #ffc107; color: #000; }
        .status-assigned { background-color: #17a2b8; color: #fff; }
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Service Tracker - Admin</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a class="nav-link" href="backend/logout.php">Logout</a>
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
            <h2 class="text-primary">Admin Dashboard</h2>
            <div>
                <a href="create_department_user.php" class="btn btn-info me-2">
                    <i class="bi bi-person-plus"></i> Create Department User
                </a>
                <a href="manage_departments.php" class="btn btn-success me-2">
                    <i class="bi bi-building"></i> Manage Departments
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Submitted" <?php echo $statusFilter === 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                                    <option value="Assigned" <?php echo $statusFilter === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                                    <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo $departmentFilter == $dept['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Tickets</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tickets)): ?>
                            <p class="text-muted">No tickets found.</p>
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
                                            <th>Department</th>
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
                                                <td><?php echo htmlspecialchars($ticket['category']); ?></td>
                                                <td>
                                                    <span class="badge status-<?php echo strtolower($ticket['status']); ?>">
                                                        <?php echo $ticket['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $ticket['department_name'] ?: 'Not assigned'; ?></td>
                                                <td><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($ticket['pdf_path']): ?>
                                                        <a href="backend/download_pdf.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="bi bi-download"></i> Download
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($ticket['status'] === 'Submitted'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                            <select name="department_id" class="form-select form-select-sm d-inline-block" style="width: auto;">
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

    <a href="index.html" class="back-home">← Back to Home</a>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
