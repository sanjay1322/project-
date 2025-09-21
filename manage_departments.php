<?php
require_once 'backend/db_connect.php';
require_once 'backend/auth.php';

// Require admin role
requireRole(['admin']);

$user = getCurrentUser();
$error = '';
$success = '';

// Handle department actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_department') {
        $name = sanitizeInput($_POST['name'] ?? '');
        
        if (!$name) {
            $error = 'Department name is required.';
        } else {
            try {
                // Check if department already exists
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM departments WHERE name = ?');
                $stmt->execute([$name]);
                
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Department already exists.';
                } else {
                    // Add new department
                    $stmt = $pdo->prepare('INSERT INTO departments (name) VALUES (?)');
                    $stmt->execute([$name]);
                    $success = 'Department added successfully!';
                }
            } catch (PDOException $e) {
                $error = 'Error adding department: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'delete_department') {
        $deptId = (int)($_POST['dept_id'] ?? 0);
        
        if (!$deptId) {
            $error = 'Invalid department ID.';
        } else {
            try {
                // Check if department has users or tickets
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE department_id = ?');
                $stmt->execute([$deptId]);
                $userCount = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets WHERE department_id = ?');
                $stmt->execute([$deptId]);
                $ticketCount = $stmt->fetchColumn();
                
                if ($userCount > 0 || $ticketCount > 0) {
                    $error = 'Cannot delete department. It has associated users or tickets.';
                } else {
                    $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
                    $stmt->execute([$deptId]);
                    $success = 'Department deleted successfully!';
                }
            } catch (PDOException $e) {
                $error = 'Error deleting department: ' . $e->getMessage();
            }
        }
    }
}

// Get all departments with stats
try {
    $stmt = $pdo->query('
        SELECT 
            d.*,
            COUNT(DISTINCT u.id) as user_count,
            COUNT(DISTINCT t.id) as ticket_count
        FROM departments d
        LEFT JOIN users u ON d.id = u.department_id
        LEFT JOIN tickets t ON d.id = t.department_id
        GROUP BY d.id
        ORDER BY d.name
    ');
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $departments = [];
    $error = 'Error loading departments: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments | Service Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Service Tracker - Manage Departments</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a class="nav-link" href="admin_dashboard.php">Back to Dashboard</a>
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
            <!-- Add New Department -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Department</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_department">
                            <div class="mb-3">
                                <label for="name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Enter department name">
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus"></i> Add Department
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Departments -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Existing Departments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($departments)): ?>
                            <p class="text-muted">No departments found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Department Name</th>
                                            <th>Users</th>
                                            <th>Tickets</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departments as $dept): ?>
                                            <tr>
                                                <td><?php echo $dept['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($dept['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $dept['user_count']; ?> users</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning text-dark"><?php echo $dept['ticket_count']; ?> tickets</span>
                                                </td>
                                                <td>
                                                    <?php if ($dept['user_count'] == 0 && $dept['ticket_count'] == 0): ?>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['name']); ?>')">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">Cannot delete</span>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the department <strong id="deptName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_department">
                        <input type="hidden" name="dept_id" id="deptId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Department</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <a href="index.html" class="back-home">← Back to Home</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteDepartment(id, name) {
            document.getElementById('deptId').value = id;
            document.getElementById('deptName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
