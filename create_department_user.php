<?php
require_once 'backend/db_connect.php';
require_once 'backend/auth.php';

// Require admin role
requireRole(['admin']);

$user = getCurrentUser();
$error = '';
$success = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $department_id = (int)($_POST['department_id'] ?? 0);
    
    // Validation
    if (!$username || !$email || !$password || !$department_id) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username or email already exists.';
            } else {
                // Create new department user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$username, $email, $hashedPassword, 'department', $department_id]);
                
                $success = 'Department user created successfully!';
                
                // Clear form data
                $username = $email = '';
                $department_id = 0;
            }
        } catch (PDOException $e) {
            $error = 'Error creating user: ' . $e->getMessage();
        }
    }
}

// Get all departments for dropdown
try {
    $stmt = $pdo->query('SELECT * FROM departments ORDER BY name');
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $departments = [];
    $error = 'Error loading departments: ' . $e->getMessage();
}

// Get existing department users
try {
    $stmt = $pdo->query('
        SELECT u.*, d.name as department_name 
        FROM users u 
        LEFT JOIN departments d ON u.department_id = d.id 
        WHERE u.role = "department" 
        ORDER BY u.created_at DESC
    ');
    $departmentUsers = $stmt->fetchAll();
} catch (PDOException $e) {
    $departmentUsers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Department User | Service Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .password-strength {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #198754; }
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
            <a class="navbar-brand" href="#">Service Tracker - Create Department User</a>
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
                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Create Department User Form -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Create Department User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="userForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                       required placeholder="Enter full name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       required placeholder="Enter email address">
                                <div class="form-text">This will be used for login.</div>
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" 
                                                <?php echo ($department_id ?? 0) == $dept['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required placeholder="Enter password" minlength="6">
                                <div class="password-strength mt-1" id="passwordStrength"></div>
                                <div class="form-text">Minimum 6 characters required.</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required placeholder="Confirm password">
                                <div class="form-text" id="passwordMatch"></div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-person-plus"></i> Create Department User
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Department Users -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-people"></i> Existing Department Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($departmentUsers)): ?>
                            <p class="text-muted">No department users found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departmentUsers as $deptUser): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($deptUser['username']); ?></td>
                                                <td><?php echo htmlspecialchars($deptUser['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($deptUser['department_name'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($deptUser['created_at'])); ?></td>
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
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength mt-1';
            if (strength === 0) {
                strengthBar.style.width = '0%';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthBar.style.width = '33%';
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
                strengthBar.style.width = '66%';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthBar.style.width = '100%';
            }
        });

        // Password confirmation matching
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchDiv.innerHTML = '<span class="text-success"><i class="bi bi-check"></i> Passwords match</span>';
                } else {
                    matchDiv.innerHTML = '<span class="text-danger"><i class="bi bi-x"></i> Passwords do not match</span>';
                }
            } else {
                matchDiv.innerHTML = '';
            }
        }

        document.getElementById('password').addEventListener('input', checkPasswordMatch);
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>
