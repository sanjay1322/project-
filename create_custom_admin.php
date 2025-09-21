<?php
require_once 'backend/db_connect.php';

echo "<h2>Creating Custom Admin Account</h2>";

try {
    // Create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=localhost;charset=utf8", "root", "");
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS service_tracker");
    echo "✅ Database 'service_tracker' created/verified<br>";

    // Connect to the specific database
    $pdo = new PDO("mysql:host=localhost;dbname=service_tracker;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to service_tracker database<br>";

    // Create tables if they don't exist
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id)
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id),
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )");

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

    echo "✅ Database tables created/verified<br>";

    // Insert initial departments if they don't exist
    $deptCount = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    if ($deptCount == 0) {
        $pdo->exec("INSERT INTO departments (name) VALUES 
            ('Academic Office'),
            ('Library'),
            ('Hostel'),
            ('IT Support'),
            ('Accounts'),
            ('Examination'),
            ('Administration')");
        echo "✅ Default departments created<br>";
    }

    // Check if admin already exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute(['admin@gmail.com']);
    $adminExists = $stmt->fetchColumn();

    if ($adminExists > 0) {
        echo "⚠️ Admin account with email 'admin@gmail.com' already exists<br>";
        
        // Update existing admin password
        $hashedPassword = password_hash('admin@123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$hashedPassword, 'admin@gmail.com']);
        echo "✅ Admin password updated to 'admin@123'<br>";
    } else {
        // Create new admin user
        $hashedPassword = password_hash('admin@123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Admin', 'admin@gmail.com', $hashedPassword, 'admin']);
        echo "✅ New admin account created<br>";
    }

    // Create default department users if they don't exist
    $defaultUsers = [
        ['Library Head', 'library@college.edu', 'library123', 2],
        ['Hostel Warden', 'hostel@college.edu', 'hostel123', 3],
        ['IT Support Head', 'it@college.edu', 'it123', 4]
    ];

    foreach ($defaultUsers as $userData) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$userData[1]]);
        $userExists = $stmt->fetchColumn();

        if ($userExists == 0) {
            $hashedPassword = password_hash($userData[2], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$userData[0], $userData[1], $hashedPassword, 'department', $userData[3]]);
            echo "✅ Created department user: " . $userData[1] . "<br>";
        }
    }

    echo "<br><h3>✅ Setup Complete!</h3>";
    echo "<strong>Admin Login Credentials:</strong><br>";
    echo "📧 <strong>Email:</strong> admin@gmail.com<br>";
    echo "🔑 <strong>Password:</strong> admin@123<br>";
    echo "👤 <strong>Role:</strong> Administrator<br>";

    echo "<br><h3>Test Your Admin Account:</h3>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Now</a><br><br>";
    echo "<a href='check_users.php'>Check All Users</a> | ";
    echo "<a href='index.html'>Go to Home</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br>Make sure MySQL is running and accessible.";
}
?>
