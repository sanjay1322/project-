<?php
require_once 'backend/db_connect.php';

echo "<h2>Quick Database Setup</h2>";

try {
    // Create departments
    $pdo->exec("INSERT INTO departments (name) VALUES 
        ('Academic Office'),
        ('Library'),
        ('Hostel'),
        ('IT Support'),
        ('Accounts')
        ON DUPLICATE KEY UPDATE name=VALUES(name)");
    
    echo "<p style='color: green;'>✅ Departments created!</p>";
    
    // Check existing tickets
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tickets");
    $ticketCount = $stmt->fetch()['count'];
    
    echo "<p>Found $ticketCount existing tickets</p>";
    
    // Check departments
    $stmt = $pdo->query("SELECT * FROM departments");
    $departments = $stmt->fetchAll();
    
    echo "<h3>Available Departments:</h3>";
    echo "<ul>";
    foreach ($departments as $dept) {
        echo "<li>ID: {$dept['id']} - {$dept['name']}</li>";
    }
    echo "</ul>";
    
    echo "<p style='color: green;'>✅ Setup complete!</p>";
    echo "<p><a href='admin_direct.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
