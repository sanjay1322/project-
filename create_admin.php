<?php
require_once 'backend/db_connect.php';

echo "<h2>Creating Admin User</h2>";

try {
    // Create departments table first
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'department', 'student') NOT NULL,
        department_id INT,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create tickets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        department_id INT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT,
        status ENUM('Submitted', 'Assigned', 'Approved', 'Rejected') DEFAULT 'Submitted',
        pdf_path VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create ticket_history table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        changed_by INT NOT NULL,
        old_status VARCHAR(50),
        new_status VARCHAR(50) NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert departments
    $pdo->exec("INSERT IGNORE INTO departments (id, name) VALUES 
        (1, 'Academic Office'),
        (2, 'Library'),
        (3, 'Hostel'),
        (4, 'IT Support'),
        (5, 'Accounts')");
    
    // Check if admin already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin@college.edu']);
    
    if (!$stmt->fetch()) {
        // Create admin user with hashed password
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Admin', 'admin@college.edu', $hashedPassword, 'admin']);
        echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists!</p>";
    }
    
    // Create department users
    $deptUsers = [
        ['Library Head', 'library@college.edu', 2],
        ['IT Support Head', 'it@college.edu', 4],
        ['Hostel Warden', 'hostel@college.edu', 3]
    ];
    
    foreach ($deptUsers as $user) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$user[1]]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user[0], $user[1], $hashedPassword, 'department', $user[2]]);
            echo "<p style='color: green;'>✅ {$user[0]} created!</p>";
        }
    }
    
    echo "<h3>Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@college.edu / admin123</li>";
    echo "<li><strong>Library Head:</strong> library@college.edu / admin123</li>";
    echo "<li><strong>IT Support Head:</strong> it@college.edu / admin123</li>";
    echo "<li><strong>Hostel Warden:</strong> hostel@college.edu / admin123</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Database connection details:</p>";
    echo "<ul>";
    echo "<li>Host: localhost</li>";
    echo "<li>Database: service_tracker</li>";
    echo "<li>Make sure MySQL is running and database exists</li>";
    echo "</ul>";
}
?>
