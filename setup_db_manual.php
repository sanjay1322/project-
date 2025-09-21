<?php
// Manual database setup - creates database and tables directly
$host = 'localhost';
$user = 'root';
$password = '';

try {
    // Connect to MySQL server (not specific database)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS service_tracker");
    $pdo->exec("USE service_tracker");
    
    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'department', 'student') NOT NULL,
        department_id INT,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
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
    $pdo->exec("INSERT INTO departments (name) VALUES 
        ('Academic Office'),
        ('Library'),
        ('Hostel'),
        ('IT Support'),
        ('Accounts') 
        ON DUPLICATE KEY UPDATE name=VALUES(name)");
    
    // Insert admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, email, password, role) VALUES 
        ('Admin', 'admin@college.edu', '$hashedPassword', 'admin')
        ON DUPLICATE KEY UPDATE password='$hashedPassword'");
    
    echo "<h2>Database Setup Complete!</h2>";
    echo "<p style='color: green;'>✅ Database and tables created successfully!</p>";
    echo "<p style='color: green;'>✅ Admin user created!</p>";
    echo "<h3>Now try admin access:</h3>";
    echo "<p><a href='admin_direct.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Direct Admin Access</a></p>";
    echo "<p>Or login normally with: admin@college.edu / admin123</p>";
    
} catch (PDOException $e) {
    echo "<h2>Database Setup</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check if MySQL is running.</p>";
    echo "<h3>Alternative - Direct Admin Access:</h3>";
    echo "<p><a href='admin_direct.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Skip Database - Direct Admin Access</a></p>";
}
?>
