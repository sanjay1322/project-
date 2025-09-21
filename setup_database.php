<?php
// Database setup script
echo "<h2>Service Tracker Database Setup</h2>";

try {
    // Connect to MySQL without database first
    $pdo = new PDO("mysql:host=localhost;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL server<br>";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS service_tracker");
    echo "✅ Database 'service_tracker' created/verified<br>";

    // Connect to the specific database
    $pdo = new PDO("mysql:host=localhost;dbname=service_tracker;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to service_tracker database<br>";

    // Read and execute the SQL file
    $sql = file_get_contents('backend/setup_db.sql');
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✅ Database schema created successfully<br>";

    // Verify tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>📋 Tables created:<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }

    // Check if admin user exists
    $adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($adminCount > 0) {
        echo "<br>👤 Admin user exists<br>";
    } else {
        echo "<br>⚠️ No admin user found<br>";
    }

    echo "<br><strong>✅ Database setup completed successfully!</strong><br>";
    echo "<br><a href='test_db.php'>Test Database Connection</a> | ";
    echo "<a href='test_register.php'>Test Registration</a> | ";
    echo "<a href='index.html'>Go to Application</a>";

} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "<br>";
    echo "<br>Make sure MySQL is running and accessible with username 'root' and no password.";
}
?>
