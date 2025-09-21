<?php
require_once 'backend/db_connect.php';

$message = '';
$error = '';

// Get available departments
try {
    $stmt = $pdo->query('SELECT id, name FROM departments ORDER BY name');
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $department_id = (int)($_POST['department_id'] ?? 0);
    
    // Validation
    if (!$username || !$email || !$password || !$confirm_password || !$department_id) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists. Choose a different email.';
            } else {
                // Get department name
                $stmt = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
                $stmt->execute([$department_id]);
                $dept = $stmt->fetch();
                
                if (!$dept) {
                    $error = 'Invalid department selected.';
                } else {
                    // Create department user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$username, $email, $hashedPassword, 'department', $department_id]);
                    
                    $message = "✅ Department account created successfully!<br>
                               <strong>Name:</strong> $username<br>
                               <strong>Email:</strong> $email<br>
                               <strong>Password:</strong> $password<br>
                               <strong>Role:</strong> Department<br>
                               <strong>Department:</strong> {$dept['name']}<br><br>
                               <a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a>";
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Department Account | Service Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #28a745, #20c997);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #444;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #bee5eb;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Department Account</h2>
        
        <?php if (empty($departments)): ?>
            <div class="error">
                No departments found! Please create departments first.<br>
                <a href="quick_setup.php" style="color: #721c24; text-decoration: underline;">Create Departments</a>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!$message && !empty($departments)): ?>
        <div class="info">
            Create a department staff account that can approve/reject tickets assigned to their department.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Staff Name</label>
                <input type="text" id="username" name="username" placeholder="Enter staff member name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="staff@college.edu" required>
            </div>
            
            <div class="form-group">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            </div>
            
            <button type="submit">Create Department Account</button>
        </form>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">← Back to Login</a>
            <a href="create_new_admin.php">Create Admin</a>
            <a href="debug_database.php">Check Database</a>
        </div>
    </div>
</body>
</html>
