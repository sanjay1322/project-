<?php
// Database initialization script
require_once 'backend/db_connect.php';

echo "<h2>Service Tracker Database Initialization</h2>";

try {
    // Read and execute SQL file
    $sql = file_get_contents('backend/setup_db.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p style='color: green;'>✅ Database initialized successfully!</p>";
    echo "<h3>Default Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@college.edu / admin123</li>";
    echo "<li><strong>Library Head:</strong> library@college.edu / admin123</li>";
    echo "<li><strong>Hostel Warden:</strong> hostel@college.edu / admin123</li>";
    echo "<li><strong>IT Support Head:</strong> it@college.edu / admin123</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Go to Login</a> | <a href='index.html'>Go to Home</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error initializing database: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running and the database connection settings are correct.</p>";
}
?>
