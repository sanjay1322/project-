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
            $oldStatus = 'Assigned'; // Default old status
            
            // Get current status
            $stmt = $pdo->prepare('SELECT status FROM tickets WHERE id = ? AND department_id = ?');
            $stmt->execute([$ticketId, $user['department_id']]);
            $ticket = $stmt->fetch();
            if ($ticket) {
                $oldStatus = $ticket['status'];
            }

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

// Get tickets with filters
$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';

$whereConditions = ['t.department_id = ?'];
$params = [$user['department_id']];

if ($statusFilter) {
    $whereConditions[] = 't.status = ?';
    $params[] = $statusFilter;
}

if ($priorityFilter) {
    $whereConditions[] = 't.priority = ?';
    $params[] = $priorityFilter;
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            u.username as student_name,
            u.email as student_email,
            COALESCE(t.priority, 'Medium') as priority
        FROM tickets t 
        LEFT JOIN users u ON t.student_id = u.id 
        $whereClause
        ORDER BY 
            CASE t.status 
                WHEN 'Assigned' THEN 1 
                WHEN 'Under Review' THEN 2 
                WHEN 'Approved' THEN 3 
                WHEN 'Rejected' THEN 4 
            END,
            CASE COALESCE(t.priority, 'Medium')
                WHEN 'High' THEN 1 
                WHEN 'Medium' THEN 2 
                WHEN 'Low' THEN 3 
            END,
            t.created_at DESC
    ");
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();

    // Get department name
    $stmt = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
    $stmt->execute([$user['department_id']]);
    $department = $stmt->fetch();

    // Get ticket statistics
    $stmt = $pdo->prepare('
        SELECT 
            status,
            COUNT(*) as count 
        FROM tickets 
        WHERE department_id = ? 
        GROUP BY status
    ');
    $stmt->execute([$user['department_id']]);
    $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    $tickets = [];
    $department = ['name' => 'Unknown'];
    $stats = [];
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #8e44ad;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin: 20px 0;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .ticket-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .status-submitted { background: linear-gradient(45deg, #ffc107, #ffeb3b); color: #000; }
        .status-assigned { background: linear-gradient(45deg, #17a2b8, #20c997); color: #fff; }
        .status-under-review { background: linear-gradient(45deg, #8e44ad, #9b59b6); color: #fff; }
        .status-approved { background: linear-gradient(45deg, #28a745, #20c997); color: #fff; }
        .status-rejected { background: linear-gradient(45deg, #dc3545, #e74c3c); color: #fff; }

        .priority-high { border-left: 5px solid #e74c3c; }
        .priority-medium { border-left: 5px solid #f39c12; }
        .priority-low { border-left: 5px solid #27ae60; }

        .action-btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            margin: 2px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-approve { background: linear-gradient(45deg, #27ae60, #2ecc71); }
        .btn-review { background: linear-gradient(45deg, #8e44ad, #9b59b6); }
        .btn-reject { background: linear-gradient(45deg, #e74c3c, #c0392b); }

        .filter-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .back-home {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .back-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(44, 62, 80, 0.9); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-building me-2"></i>
                Service Tracker - <?php echo htmlspecialchars($department['name']); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>
                    Welcome, <?php echo htmlspecialchars($user['name']); ?>
                </span>
                <a class="nav-link" href="backend/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Department Dashboard
                    </h2>
                    <p class="text-muted mb-0">Manage and track service requests efficiently</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-end align-items-center">
                        <span class="badge bg-primary fs-6 me-2">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('M j, Y'); ?>
                        </span>
                        <span class="badge bg-secondary fs-6">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('H:i'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-inbox fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['Assigned'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">New Assigned</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-eye fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['Under Review'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">Under Review</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['Approved'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">Approved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="text-danger mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['Rejected'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">Rejected</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-filter me-1"></i>Filter by Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Assigned" <?php echo $statusFilter === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                        <option value="Under Review" <?php echo $statusFilter === 'Under Review' ? 'selected' : ''; ?>>Under Review</option>
                        <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-exclamation me-1"></i>Filter by Priority
                    </label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="High" <?php echo $priorityFilter === 'High' ? 'selected' : ''; ?>>High</option>
                        <option value="Medium" <?php echo $priorityFilter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="Low" <?php echo $priorityFilter === 'Low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Apply Filters
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Tickets Table -->
        <div class="ticket-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Assigned Tickets (<?php echo count($tickets); ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($tickets)): ?>
                    <div class="text-center p-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tickets found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                    <th><i class="fas fa-user me-1"></i>Student</th>
                                    <th><i class="fas fa-tag me-1"></i>Title</th>
                                    <th><i class="fas fa-folder me-1"></i>Category</th>
                                    <th><i class="fas fa-exclamation me-1"></i>Priority</th>
                                    <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                    <th><i class="fas fa-calendar me-1"></i>Created</th>
                                    <th><i class="fas fa-file-pdf me-1"></i>PDF</th>
                                    <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr class="priority-<?php echo strtolower($ticket['priority']); ?>">
                                        <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($ticket['student_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($ticket['student_email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ticket['title']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($ticket['description'], 0, 50)) . '...'; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($ticket['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $ticket['priority'] === 'High' ? 'danger' : ($ticket['priority'] === 'Medium' ? 'warning' : 'success'); ?>">
                                                <?php echo $ticket['priority']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                                <?php echo $ticket['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('M j, Y', strtotime($ticket['created_at'])); ?>
                                                <br><?php echo date('H:i', strtotime($ticket['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($ticket['pdf_path']): ?>
                                                <a href="backend/download_pdf.php?id=<?php echo $ticket['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (in_array($ticket['status'], ['Assigned', 'Under Review'])): ?>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-success action-btn btn-approve" 
                                                            onclick="showActionModal(<?php echo $ticket['id']; ?>, 'approve')">
                                                        <i class="fas fa-check me-1"></i>Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-warning action-btn btn-review" 
                                                            onclick="showActionModal(<?php echo $ticket['id']; ?>, 'under_review')">
                                                        <i class="fas fa-eye me-1"></i>Review
                                                    </button>
                                                    <button class="btn btn-sm btn-danger action-btn btn-reject" 
                                                            onclick="showActionModal(<?php echo $ticket['id']; ?>, 'reject')">
                                                        <i class="fas fa-times me-1"></i>Reject
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-lock me-1"></i>Completed
                                                </span>
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

    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalTitle">
                        <i class="fas fa-cog me-2"></i>Action Required
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="ticket_id" id="modalTicketId">
                        <input type="hidden" name="action" id="modalAction">
                        <div class="mb-3">
                            <label for="comment" class="form-label">
                                <i class="fas fa-comment me-1"></i>Comment (Optional)
                            </label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" 
                                      placeholder="Add a comment about this action..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="actionDescription"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn" id="modalSubmitBtn">
                            <i class="fas fa-check me-1"></i>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="index.html" class="back-home">
        <i class="fas fa-home me-1"></i>Back to Home
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showActionModal(ticketId, action) {
            document.getElementById('modalTicketId').value = ticketId;
            document.getElementById('modalAction').value = action;
            
            const title = document.getElementById('actionModalTitle');
            const submitBtn = document.getElementById('modalSubmitBtn');
            const description = document.getElementById('actionDescription');
            
            switch(action) {
                case 'approve':
                    title.innerHTML = '<i class="fas fa-check me-2"></i>Approve Ticket';
                    submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Approve';
                    submitBtn.className = 'btn btn-success';
                    description.textContent = 'This will mark the ticket as approved and notify the student.';
                    break;
                case 'reject':
                    title.innerHTML = '<i class="fas fa-times me-2"></i>Reject Ticket';
                    submitBtn.innerHTML = '<i class="fas fa-times me-1"></i>Reject';
                    submitBtn.className = 'btn btn-danger';
                    description.textContent = 'This will mark the ticket as rejected and notify the student.';
                    break;
                case 'under_review':
                    title.innerHTML = '<i class="fas fa-eye me-2"></i>Mark Under Review';
                    submitBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Mark Under Review';
                    submitBtn.className = 'btn btn-warning';
                    description.textContent = 'This will mark the ticket as under review for further investigation.';
                    break;
            }
            
            new bootstrap.Modal(document.getElementById('actionModal')).show();
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>
