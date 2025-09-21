<?php
// Complete database setup script
echo "<h2>Service Tracker Database Setup</h2>";

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'service_tracker';

try {
    // First, connect without database to create it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "<p>✅ Database '$dbname' created/verified</p>";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create departments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE
    )");
    echo "<p>✅ Departments table created</p>";
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'department', 'student') NOT NULL,
        department_id INT,
        email VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )");
    echo "<p>✅ Users table created</p>";
    
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id),
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )");
    echo "<p>✅ Tickets table created</p>";
    
    // Create ticket_history table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        changed_by INT NOT NULL,
        old_status VARCHAR(50),
        new_status VARCHAR(50) NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id),
        FOREIGN KEY (changed_by) REFERENCES users(id)
    )");
    echo "<p>✅ Ticket history table created</p>";
    
    // Insert departments
    $departments = ['Academic Office', 'Library', 'Hostel', 'IT Support', 'Accounts'];
    foreach ($departments as $dept) {
        $pdo->prepare("INSERT IGNORE INTO departments (name) VALUES (?)")->execute([$dept]);
    }
    echo "<p>✅ Departments inserted</p>";
    
    // Create default users with proper password hashing
    $defaultUsers = [
        ['username' => 'Admin', 'email' => 'admin@college.edu', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'role' => 'admin', 'department_id' => null],
        ['username' => 'Library Head', 'email' => 'library@college.edu', 'password' => password_hash('library123', PASSWORD_DEFAULT), 'role' => 'department', 'department_id' => 2],
        ['username' => 'Hostel Warden', 'email' => 'hostel@college.edu', 'password' => password_hash('hostel123', PASSWORD_DEFAULT), 'role' => 'department', 'department_id' => 3],
        ['username' => 'IT Support Head', 'email' => 'it@college.edu', 'password' => password_hash('it123', PASSWORD_DEFAULT), 'role' => 'department', 'department_id' => 4]
    ];
    
    foreach ($defaultUsers as $user) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user['username'], $user['email'], $user['password'], $user['role'], $user['department_id']]);
            echo "<p>✅ Created user: {$user['email']} ({$user['role']})</p>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<p>⚠️ User {$user['email']} already exists</p>";
            } else {
                throw $e;
            }
        }
    }
    
    // Create uploads directory
    if (!is_dir('uploads/tickets/')) {
        mkdir('uploads/tickets/', 0755, true);
        echo "<p>✅ Created uploads directory</p>";
    }
    
    echo "<h3>🎉 Database Setup Complete!</h3>";
    echo "<h4>Default Login Credentials:</h4>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@college.edu / admin123</li>";
    echo "<li><strong>Library:</strong> library@college.edu / library123</li>";
    echo "<li><strong>Hostel:</strong> hostel@college.edu / hostel123</li>";
    echo "<li><strong>IT Support:</strong> it@college.edu / it123</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running and you have the correct credentials.</p>";
}
?>