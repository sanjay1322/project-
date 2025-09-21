<?php
// Test database connection and tables
try {
    $pdo = new PDO("mysql:host=localhost;dbname=service_tracker;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful!<br><br>";
    
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tables found: " . count($tables) . "<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
    // Check users table structure
    if (in_array('users', $tables)) {
        echo "<br>👥 Users table structure:<br>";
        $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
        }
        
        // Check if there are any users
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "<br>👤 Total users: " . $userCount . "<br>";
    }
    
    // Check departments table
    if (in_array('departments', $tables)) {
        echo "<br>🏢 Departments:<br>";
        $depts = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($depts as $dept) {
            echo "- " . $dept['name'] . "<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "Make sure MySQL is running and the database exists.<br>";
}
?>
